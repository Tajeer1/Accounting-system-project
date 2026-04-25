<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Invoice extends Model
{
    protected $fillable = [
        'number', 'type', 'party_name', 'amount', 'issue_date', 'due_date',
        'status', 'project_id', 'bank_account_id', 'category_id', 'description',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:3',
    ];

    public const TYPES = [
        'sales' => 'مبيعات',
        'purchase' => 'مشتريات',
    ];

    public const STATUSES = [
        'draft' => 'مسودة',
        'sent' => 'مرسلة',
        'paid' => 'مدفوعة',
        'overdue' => 'متأخرة',
        'cancelled' => 'ملغاة',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function journalEntry(): MorphOne
    {
        return $this->morphOne(JournalEntry::class, 'source');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public static function generateNumber(string $type): string
    {
        $prefix = $type === 'sales' ? 'INV-S-' : 'INV-P-';
        $last = static::where('type', $type)->orderByDesc('id')->first();
        $n = $last ? ((int) preg_replace('/\D/', '', substr($last->number, strlen($prefix)))) + 1 : 1;
        return $prefix . str_pad((string) $n, 6, '0', STR_PAD_LEFT);
    }
}
