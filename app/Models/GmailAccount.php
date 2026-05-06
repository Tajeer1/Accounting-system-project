<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class GmailAccount extends Model
{
    protected $fillable = [
        'email', 'label', 'access_token', 'refresh_token',
        'token_expires_at', 'history_id', 'last_synced_at', 'is_active',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    public function emailMessages(): HasMany
    {
        return $this->hasMany(BankEmailMessage::class);
    }

    protected function accessToken(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Crypt::decryptString($value) : null,
            set: fn ($value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    protected function refreshToken(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Crypt::decryptString($value) : null,
            set: fn ($value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    public function isExpired(): bool
    {
        return $this->token_expires_at !== null
            && $this->token_expires_at->subMinute()->isPast();
    }
}
