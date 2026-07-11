<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Application extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'client_id',
        'client_secret',
        'redirect_uris',
        'allowed_origins',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'redirect_uris' => 'array',
            'allowed_origins' => 'array',
            'is_active' => 'boolean',
            'client_secret' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Application $application): void {
            if (empty($application->client_id)) {
                $application->client_id = Str::random(32);
            }

            if (empty($application->client_secret)) {
                $application->client_secret = Str::random(64);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function telegramBot(): HasOne
    {
        return $this->hasOne(TelegramBot::class);
    }

    public function tenantEndUsers(): HasMany
    {
        return $this->hasMany(TenantEndUser::class);
    }

    public function authSessions(): HasMany
    {
        return $this->hasMany(AuthSession::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuthAuditLog::class);
    }

    public function allowsRedirectUri(string $redirectUri): bool
    {
        return in_array($redirectUri, $this->redirect_uris ?? [], true);
    }
}
