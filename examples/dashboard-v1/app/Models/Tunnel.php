<?php

namespace App\Models;

use Database\Factories\TunnelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Tunnel extends Model
{
    /** @use HasFactory<TunnelFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'herd_host',
        'is_active',
        'last_verified_at',
        'last_verified_status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_verified_at' => 'datetime',
        ];
    }

    public static function normalizeDomain(string $raw): string
    {
        $host = strtolower(trim($raw));
        $host = preg_replace('#^https?://#', '', $host) ?? $host;
        $host = explode('/', $host)[0];
        $host = rtrim($host, '/');

        return $host;
    }

    public static function validateDomain(string $host): void
    {
        if ($host === '' || str_contains($host, '://')) {
            throw ValidationException::withMessages([
                'domain' => 'Enter a hostname only (no scheme or path).',
            ]);
        }

        if (str_ends_with($host, '.test') || $host === 'localhost' || str_starts_with($host, '127.')) {
            throw ValidationException::withMessages([
                'domain' => 'Use a public ngrok domain, not a local Herd URL.',
            ]);
        }

        if (! preg_match('/\.ngrok(-free)?\.(app|dev|pizza)$/', $host)) {
            throw ValidationException::withMessages([
                'domain' => 'Domain must look like an ngrok host (e.g. abc123.ngrok-free.dev).',
            ]);
        }
    }

    public static function validateHerdHost(string $host): void
    {
        $host = strtolower(trim($host));

        if ($host === '' || str_contains($host, '://') || str_contains($host, '/')) {
            throw ValidationException::withMessages([
                'herd_host' => 'Enter a Herd hostname only (no scheme or path).',
            ]);
        }

        if (preg_match('/\.ngrok(-free)?\.(app|dev|pizza)$/', $host)) {
            throw ValidationException::withMessages([
                'herd_host' => 'Use your local Herd host (e.g. dashboard-v1.test), not an ngrok domain.',
            ]);
        }

        if (! str_ends_with($host, '.test')) {
            throw ValidationException::withMessages([
                'herd_host' => 'Herd host must end with .test (e.g. dashboard-v1.test).',
            ]);
        }
    }

    public function publicBaseUrl(): string
    {
        return 'https://'.static::normalizeDomain($this->domain);
    }

    /**
     * @return array<string, string>
     */
    public static function defaultTemplate(): ?self
    {
        return static::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->first()
            ?? static::query()->orderBy('id')->first();
    }

    /**
     * @return array{domain: string, herd_host: string}
     */
    public function templateAttributes(): array
    {
        return [
            'domain' => $this->domain,
            'herd_host' => $this->herd_host,
        ];
    }

    public function oauthUrls(): array
    {
        $base = $this->publicBaseUrl();
        $urls = [
            'login' => $base.'/login',
        ];

        foreach (config('tunnel.oauth_callbacks', []) as $key => $path) {
            $urls[$key] = $base.$path;
        }

        return $urls;
    }

    public function setDomainAttribute(?string $value): void
    {
        if ($value === null) {
            $this->attributes['domain'] = $value;

            return;
        }

        $this->attributes['domain'] = static::normalizeDomain($value);
    }

    protected static function booted(): void
    {
        static::saving(function (Tunnel $tunnel): void {
            if ($tunnel->domain !== null && $tunnel->domain !== '') {
                static::validateDomain($tunnel->domain);
            }

            if ($tunnel->herd_host === null || $tunnel->herd_host === '') {
                $tunnel->herd_host = config('tunnel.default_herd_host', 'dashboard-v1.test');
            }

            static::validateHerdHost($tunnel->herd_host);
        });
    }
}
