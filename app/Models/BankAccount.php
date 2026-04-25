<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    protected $fillable = [
        'name', 'type', 'account_number', 'opening_balance',
        'current_balance', 'currency', 'notes', 'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:3',
        'current_balance' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    public const TYPES = [
        'bank' => 'حساب بنكي',
        'cash' => 'كاش',
        'other' => 'أخرى',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function transfersOut(): HasMany
    {
        return $this->hasMany(AccountTransfer::class, 'from_account_id');
    }

    public function transfersIn(): HasMany
    {
        return $this->hasMany(AccountTransfer::class, 'to_account_id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function recalculateBalance(): void
    {
        $purchasesTotal = (float) $this->purchases()
            ->where('status', 'paid')
            ->sum('amount');

        $paidInvoicesIn = (float) $this->invoices()
            ->where('type', 'sales')
            ->where('status', 'paid')
            ->sum('amount');

        $paidInvoicesOut = (float) $this->invoices()
            ->where('type', 'purchase')
            ->where('status', 'paid')
            ->sum('amount');

        $transfersIn = (float) $this->transfersIn()->sum('amount');
        $transfersOut = (float) $this->transfersOut()->sum('amount');

        $this->current_balance = (float) $this->opening_balance
            + $paidInvoicesIn + $transfersIn
            - $purchasesTotal - $paidInvoicesOut - $transfersOut;

        $this->save();
    }
}
