<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BankEmailMessage extends Model
{
    protected $fillable = [
        'integration_id', 'message_uid', 'message_id', 'sender', 'subject',
        'received_at', 'raw_body', 'processing_status', 'error_message',
        'content_hash',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public const STATUSES = [
        'pending' => 'قيد المعالجة',
        'processed' => 'تمت المعالجة',
        'failed' => 'فشل',
        'duplicate' => 'مكرر',
    ];

    public function integration(): BelongsTo
    {
        return $this->belongsTo(BankEmailIntegration::class, 'integration_id');
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(BankTransaction::class, 'source_message_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class, 'source_message_id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->processing_status] ?? $this->processing_status;
    }

    public function statusColor(): string
    {
        return match ($this->processing_status) {
            'processed' => 'emerald',
            'failed' => 'rose',
            'duplicate' => 'slate',
            default => 'amber',
        };
    }
}
