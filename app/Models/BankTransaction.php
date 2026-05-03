<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    protected $fillable = [
        'bank_account_id', 'source', 'source_message_id', 'bank_name',
        'transaction_type', 'masked_card_number', 'masked_account_number',
        'description', 'amount', 'currency', 'balance_after',
        'transaction_country', 'transaction_datetime', 'status',
        'matched_purchase_id', 'matched_invoice_id', 'project_id',
        'chart_of_account_id', 'journal_entry_id', 'dedupe_hash', 'raw_data',
    ];

    protected $casts = [
        'amount' => 'decimal:3',
        'balance_after' => 'decimal:3',
        'transaction_datetime' => 'datetime',
        'raw_data' => 'array',
    ];

    public const TYPES = [
        'debit' => 'مدين (خصم)',
        'credit' => 'دائن (إيداع)',
        'unknown' => 'غير محدّد',
    ];

    public const STATUSES = [
        'pending_review' => 'قيد المراجعة',
        'confirmed' => 'مؤكدة',
        'ignored' => 'متجاهلة',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function sourceMessage(): BelongsTo
    {
        return $this->belongsTo(BankEmailMessage::class, 'source_message_id');
    }

    public function matchedPurchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'matched_purchase_id');
    }

    public function matchedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'matched_invoice_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->transaction_type] ?? $this->transaction_type;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'confirmed' => 'emerald',
            'ignored' => 'slate',
            default => 'amber',
        };
    }

    public function typeColor(): string
    {
        return match ($this->transaction_type) {
            'debit' => 'rose',
            'credit' => 'emerald',
            default => 'amber',
        };
    }
}
