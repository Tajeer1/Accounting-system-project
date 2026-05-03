<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\ChartOfAccount;
use App\Models\Project;
use App\Services\BankEmailService;
use Illuminate\Http\Request;

class BankTransactionController extends Controller
{
    public function __construct(protected BankEmailService $service) {}

    public function index(Request $request)
    {
        $query = BankTransaction::query()
            ->with(['bankAccount', 'sourceMessage.integration']);

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($type = $request->string('type')->toString()) {
            $query->where('transaction_type', $type);
        }
        if ($q = $request->string('q')->toString()) {
            $query->where(function ($w) use ($q) {
                $w->where('description', 'like', "%{$q}%")
                  ->orWhere('masked_account_number', 'like', "%{$q}%")
                  ->orWhere('masked_card_number', 'like', "%{$q}%");
            });
        }

        $transactions = $query->latest('transaction_datetime')->paginate(20)->withQueryString();

        $counts = [
            'pending_review' => BankTransaction::where('status', 'pending_review')->count(),
            'confirmed' => BankTransaction::where('status', 'confirmed')->count(),
            'ignored' => BankTransaction::where('status', 'ignored')->count(),
            'unknown' => BankTransaction::where('transaction_type', 'unknown')->count(),
        ];

        return view('bank-emails.transactions.index', compact('transactions', 'counts'));
    }

    public function review()
    {
        $transactions = BankTransaction::query()
            ->with(['bankAccount', 'sourceMessage.integration'])
            ->where(function ($q) {
                $q->where('status', 'pending_review')
                  ->orWhere('transaction_type', 'unknown');
            })
            ->latest('transaction_datetime')
            ->paginate(20);

        return view('bank-emails.transactions.review', compact('transactions'));
    }

    public function show(BankTransaction $transaction)
    {
        $transaction->load(['bankAccount', 'sourceMessage.integration', 'matchedPurchase', 'matchedInvoice', 'project', 'chartOfAccount', 'journalEntry']);
        $candidates = $this->service->findMatchCandidates($transaction);

        return view('bank-emails.transactions.show', [
            'tx' => $transaction,
            'candidates' => $candidates,
            'bankAccounts' => BankAccount::orderBy('name')->pluck('name', 'id'),
            'projects' => Project::orderBy('name')->pluck('name', 'id'),
            'chartAccounts' => ChartOfAccount::orderBy('code')
                ->get(['id', 'code', 'name'])
                ->mapWithKeys(fn ($a) => [$a->id => "{$a->code} — {$a->name}"]),
        ]);
    }

    public function update(Request $request, BankTransaction $transaction)
    {
        $data = $request->validate([
            'transaction_type' => ['required', 'in:debit,credit,unknown'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'description' => ['nullable', 'string', 'max:500'],
            'transaction_datetime' => ['required', 'date'],
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'chart_of_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'matched_purchase_id' => ['nullable', 'exists:purchases,id'],
            'matched_invoice_id' => ['nullable', 'exists:invoices,id'],
        ]);

        $transaction->update($data);

        return back()->with('success', 'تم تحديث المعاملة');
    }

    public function confirm(BankTransaction $transaction, Request $request)
    {
        if ($transaction->transaction_type === 'unknown') {
            return back()->with('error', 'لا يمكن تأكيد معاملة بنوع غير محدّد. حدّد النوع أولاً.');
        }

        $createJournal = $request->boolean('create_journal', true);
        $this->service->confirmTransaction($transaction, $createJournal);

        return back()->with('success', 'تم تأكيد المعاملة');
    }

    public function ignore(BankTransaction $transaction)
    {
        $this->service->ignoreTransaction($transaction);
        return back()->with('success', 'تم تجاهل المعاملة');
    }

    public function destroy(BankTransaction $transaction)
    {
        $transaction->journalEntry()?->delete();
        $transaction->delete();
        return redirect()->route('bank-emails.transactions.index')
            ->with('success', 'تم حذف المعاملة');
    }
}
