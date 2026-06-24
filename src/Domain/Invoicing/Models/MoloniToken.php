<?php

namespace Domain\Invoicing\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property \Illuminate\Support\Carbon|null $access_token_expires_at
 * @property \Illuminate\Support\Carbon|null $refresh_token_expires_at
 */
class MoloniToken extends Model
{
    protected $table = 'moloni_tokens';

    protected $fillable = [
        'access_token',
        'refresh_token',
        'access_token_expires_at',
        'refresh_token_expires_at',
        'company_id',
    ];

    protected function casts(): array
    {
        return [
            'access_token_expires_at' => 'datetime',
            'refresh_token_expires_at' => 'datetime',
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
        ];
    }

    public function isAccessTokenValid(): bool
    {
        return $this->access_token_expires_at && $this->access_token_expires_at->isFuture();
    }

    public function isRefreshTokenValid(): bool
    {
        return $this->refresh_token_expires_at && $this->refresh_token_expires_at->isFuture();
    }

    public function accessTokenExpiresInMinutes(): int
    {
        if (! $this->access_token_expires_at) {
            return 0;
        }

        return max(0, now()->diffInMinutes($this->access_token_expires_at, false));
    }

    public function needsRefresh(): bool
    {
        if (! $this->access_token_expires_at) {
            return true;
        }

        return $this->access_token_expires_at->subMinutes(5)->isPast();
    }
}
