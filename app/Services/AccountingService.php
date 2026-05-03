<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\ChartOfAccount;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    public function accountByCode(string $code): ?ChartOfAccount
    {
        return ChartOfAccount::where('code', $code)->first();
    }

    public function createEntryForPurchase(Purchase $purchase): ?JournalEntry
    {
        if ($purchase->status === 'cancelled') {
            return null;
        }

        $expenseAccount = $this->accountByCode('5100');
        $cashAccount = $this->accountByCode('1100');
        $apAccount = $this->accountByCode('2100');

        if (! $expenseAccount || ! $cashAccount || ! $apAccount) {
            return null;
        }

        $creditAccount = $purchase->status === 'paid' ? $cashAccount : $apAccount;

        return DB::transaction(function () use ($purchase, $expenseAccount, $creditAccount) {
            $purchase->journalEntry()?->delete();

            $entry = JournalEntry::create([
                'number' => JournalEntry::generateNumber(),
                'entry_date' => $purchase->purchase_date,
                'reference' => $purchase->number,
                'description' => 'مشتريات — ' . $purchase->supplier_name,
                'status' => 'posted',
                'source_type' => Purchase::class,
                'source_id' => $purchase->id,
                'project_id' => $purchase->project_id,
            ]);

            $entry->lines()->create([
                'account_id' => $expenseAccount->id,
                'debit' => $purchase->amount,
                'credit' => 0,
                'notes' => 'مشتريات ' . $purchase->supplier_name,
            ]);

            $entry->lines()->create([
                'account_id' => $creditAccount->id,
                'debit' => 0,
                'credit' => $purchase->amount,
                'notes' => 'سداد / استحقاق مشتريات',
            ]);

            $entry->recalculateTotals();

            if ($purchase->bankAccount) {
                $purchase->bankAccount->recalculateBalance();
            }

            return $entry;
        });
    }

    public function createEntryForInvoice(Invoice $invoice): ?JournalEntry
    {
        if (in_array($invoice->status, ['draft', 'cancelled'])) {
            $invoice->journalEntry()?->delete();
            return null;
        }

        $arAccount = $this->accountByCode('1200');
        $apAccount = $this->accountByCode('2100');
        $revenueAccount = $this->accountByCode('4100');
        $cogsAccount = $this->accountByCode('5200');
        $cashAccount = $this->accountByCode('1100');

        if (! $arAccount || ! $apAccount || ! $revenueAccount || ! $cogsAccount || ! $cashAccount) {
            return null;
        }

        return DB::transaction(function () use ($invoice, $arAccount, $apAccount, $revenueAccount, $cogsAccount, $cashAccount) {
            $invoice->journalEntry()?->delete();

            $entry = JournalEntry::create([
                'number' => JournalEntry::generateNumber(),
                'entry_date' => $invoice->issue_date,
                'reference' => $invoice->number,
                'description' => ($invoice->type === 'sales' ? 'فاتورة مبيعات — ' : 'فاتورة مشتريات — ') . $invoice->party_name,
                'status' => 'posted',
                'source_type' => Invoice::class,
                'source_id' => $invoice->id,
                'project_id' => $invoice->project_id,
            ]);

            if ($invoice->type === 'sales') {
                $debitAccount = $invoice->status === 'paid' ? $cashAccount : $arAccount;
                $entry->lines()->create([
                    'account_id' => $debitAccount->id,
                    'debit' => $invoice->amount,
                    'credit' => 0,
                    'notes' => 'فاتورة مبيعات ' . $invoice->party_name,
                ]);
                $entry->lines()->create([
                    'account_id' => $revenueAccount->id,
                    'debit' => 0,
                    'credit' => $invoice->amount,
                    'notes' => 'إيراد مبيعات',
                ]);
            } else {
                $creditAccount = $invoice->status === 'paid' ? $cashAccount : $apAccount;
                $entry->lines()->create([
                    'account_id' => $cogsAccount->id,
                    'debit' => $invoice->amount,
                    'credit' => 0,
                    'notes' => 'تكلفة مشتريات ' . $invoice->party_name,
                ]);
                $entry->lines()->create([
                    'account_id' => $creditAccount->id,
                    'debit' => 0,
                    'credit' => $invoice->amount,
                    'notes' => 'استحقاق / سداد',
                ]);
            }

            $entry->recalculateTotals();

            if ($invoice->bankAccount) {
                $invoice->bankAccount->recalculateBalance();
            }

            return $entry;
        });
    }

    public function createEntryForBankTransaction(BankTransaction $tx): ?JournalEntry
    {
        if ($tx->transaction_type === 'unknown' || $tx->amount <= 0) {
            return null;
        }

        $cashAccount = $this->accountByCode('1100');
        $expenseAccount = $tx->chartOfAccount ?? $this->accountByCode('5100');
        $revenueAccount = $tx->chartOfAccount ?? $this->accountByCode('4100');

        if (! $cashAccount || ! $expenseAccount || ! $revenueAccount) {
            return null;
        }

        return DB::transaction(function () use ($tx, $cashAccount, $expenseAccount, $revenueAccount) {
            $tx->journalEntry()?->delete();

            $entryDate = $tx->transaction_datetime?->toDateString() ?? now()->toDateString();
            $reference = 'BT-' . $tx->id;
            $description = ($tx->bank_name ? $tx->bank_name . ' — ' : '') . ($tx->description ?? '');

            $entry = JournalEntry::create([
                'number' => JournalEntry::generateNumber(),
                'entry_date' => $entryDate,
                'reference' => $reference,
                'description' => mb_substr($description, 0, 1000),
                'status' => 'posted',
                'source_type' => BankTransaction::class,
                'source_id' => $tx->id,
                'project_id' => $tx->project_id,
            ]);

            if ($tx->transaction_type === 'debit') {
                $entry->lines()->create([
                    'account_id' => $expenseAccount->id,
                    'debit' => $tx->amount,
                    'credit' => 0,
                    'notes' => $tx->description,
                ]);
                $entry->lines()->create([
                    'account_id' => $cashAccount->id,
                    'debit' => 0,
                    'credit' => $tx->amount,
                    'notes' => 'خصم بنكي — ' . ($tx->masked_account_number ?? ''),
                ]);
            } else {
                $entry->lines()->create([
                    'account_id' => $cashAccount->id,
                    'debit' => $tx->amount,
                    'credit' => 0,
                    'notes' => 'إيداع بنكي — ' . ($tx->masked_account_number ?? ''),
                ]);
                $entry->lines()->create([
                    'account_id' => $revenueAccount->id,
                    'debit' => 0,
                    'credit' => $tx->amount,
                    'notes' => $tx->description,
                ]);
            }

            $entry->recalculateTotals();

            return $entry;
        });
    }
}
