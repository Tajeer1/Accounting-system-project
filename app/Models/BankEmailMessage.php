<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankEmailMessage extends Model
{
    protected $fillable = [
        'gmail_account_id', 'bank_account_id', 'gmail_message_id', 'thread_id',
        'from_email', 'from_name', 'subject', 'snippet',
        'body_plain', 'body_html', 'received_at',
        'bank_key', 'status', 'parse_error',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public const STATUSES = [
        'pending' => 'في الانتظار',
        'parsed' => 'تم التحليل',
        'ignored' => 'متجاهل',
        'failed' => 'فشل',
    ];

    public function gmailAccount(): BelongsTo
    {
        return $this->belongsTo(GmailAccount::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
