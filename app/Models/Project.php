<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'code', 'name', 'client_name', 'start_date', 'end_date',
        'contract_value', 'status', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_value' => 'decimal:3',
    ];

    public const STATUSES = [
        'planned' => 'مخطط',
        'in_progress' => 'قيد التنفيذ',
        'completed' => 'منتهي',
        'cancelled' => 'ملغي',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function totalCost(): float
    {
        return (float) $this->purchases()->sum('amount')
             + (float) $this->invoices()->where('type', 'purchase')->sum('amount');
    }

    public function totalRevenue(): float
    {
        return (float) $this->invoices()->where('type', 'sales')->sum('amount');
    }

    public function profit(): float
    {
        return $this->totalRevenue() - $this->totalCost();
    }

    public function profitMargin(): float
    {
        $revenue = $this->totalRevenue();
        return $revenue > 0 ? round(($this->profit() / $revenue) * 100, 2) : 0;
    }
}
