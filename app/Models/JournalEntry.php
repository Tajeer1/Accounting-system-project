<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JournalEntry extends Model
{
    protected $fillable = [
        'number', 'entry_date', 'reference', 'description', 'status',
        'total_debit', 'total_credit', 'source_type', 'source_id', 'project_id',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'total_debit' => 'decimal:3',
        'total_credit' => 'decimal:3',
    ];

    public const STATUSES = [
        'draft' => 'مسودة',
        'posted' => 'منشور',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function isBalanced(): bool
    {
        return round((float) $this->total_debit, 2) === round((float) $this->total_credit, 2);
    }

    public function recalculateTotals(): void
    {
        $this->total_debit = (float) $this->lines()->sum('debit');
        $this->total_credit = (float) $this->lines()->sum('credit');
        $this->save();
    }

    public static function generateNumber(): string
    {
        $last = static::orderByDesc('id')->first();
        $n = $last ? ((int) preg_replace('/\D/', '', $last->number)) + 1 : 1;
        return 'JE-' . str_pad((string) $n, 6, '0', STR_PAD_LEFT);
    }
}
