<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountTransfer extends Model
{
    protected $fillable = [
        'number', 'transfer_date', 'from_account_id', 'to_account_id', 'amount', 'notes',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'amount' => 'decimal:3',
    ];

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'to_account_id');
    }

    public static function generateNumber(): string
    {
        $last = static::orderByDesc('id')->first();
        $n = $last ? ((int) preg_replace('/\D/', '', $last->number)) + 1 : 1;
        return 'TRF-' . str_pad((string) $n, 6, '0', STR_PAD_LEFT);
    }
}
