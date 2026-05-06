<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    protected $fillable = [
        'bank_email_message_id', 'bank_account_id', 'direction',
        'amount', 'currency', 'transaction_date', 'merchant',
        'reference', 'card_last4', 'balance_after', 'raw_match',
        'status', 'purchase_id', 'category_id', 'project_id', 'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:3',
        'balance_after' => 'decimal:3',
        'transaction_date' => 'date',
    ];

    public const DIRECTIONS = [
        'debit' => 'خصم',
        'credit' => 'إيداع',
    ];

    public const STATUSES = [
        'pending_review' => 'بانتظار المراجعة',
        'approved' => 'معتمدة',
        'linked' => 'مرتبطة بمشترى',
        'rejected' => 'مرفوضة',
    ];

    public function emailMessage(): BelongsTo
    {
        return $this->belongsTo(BankEmailMessage::class, 'bank_email_message_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function directionLabel(): string
    {
        return self::DIRECTIONS[$this->direction] ?? $this->direction;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
