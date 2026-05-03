<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class BankEmailIntegration extends Model
{
    protected $fillable = [
        'bank_name', 'parser_key', 'email_address', 'imap_host', 'imap_port',
        'encryption', 'validate_cert', 'username', 'encrypted_password',
        'mailbox_folder', 'sender_filter', 'keyword_filter',
        'linked_bank_account_id', 'is_active', 'auto_confirm',
        'mark_seen_after_import', 'last_synced_at', 'last_sync_error',
    ];

    protected $hidden = ['encrypted_password'];

    protected $casts = [
        'imap_port' => 'integer',
        'validate_cert' => 'boolean',
        'is_active' => 'boolean',
        'auto_confirm' => 'boolean',
        'mark_seen_after_import' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public const ENCRYPTIONS = [
        'ssl' => 'SSL',
        'tls' => 'TLS',
        'none' => 'بدون تشفير',
    ];

    public const PARSERS = [
        'bank_muscat' => 'Bank Muscat',
        'generic' => 'محلّل عام',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'linked_bank_account_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(BankEmailMessage::class, 'integration_id');
    }

    public function password(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->encrypted_password ? Crypt::decryptString($this->encrypted_password) : null,
            set: fn ($value) => $value ? ['encrypted_password' => Crypt::encryptString($value)] : [],
        );
    }

    public function maskedPassword(): string
    {
        return $this->encrypted_password ? '•••••••••' : '';
    }

    public function encryptionLabel(): string
    {
        return self::ENCRYPTIONS[$this->encryption] ?? $this->encryption;
    }

    public function parserLabel(): string
    {
        return self::PARSERS[$this->parser_key] ?? $this->parser_key;
    }
}
