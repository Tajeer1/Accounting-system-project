<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Purchase extends Model
{
    protected $fillable = [
        'number', 'purchase_date', 'supplier_name', 'amount',
        'category_id', 'bank_account_id', 'project_id', 'invoice_id',
        'description', 'status',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'amount' => 'decimal:3',
    ];

    public const STATUSES = [
        'pending' => 'معلق',
        'paid' => 'مدفوع',
        'cancelled' => 'ملغي',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function journalEntry(): MorphOne
    {
        return $this->morphOne(JournalEntry::class, 'source');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public static function generateNumber(): string
    {
        $last = static::orderByDesc('id')->first();
        $n = $last ? ((int) preg_replace('/\D/', '', $last->number)) + 1 : 1;
        return 'PO-' . str_pad((string) $n, 6, '0', STR_PAD_LEFT);
    }
}
