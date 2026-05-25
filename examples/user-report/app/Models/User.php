<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory;
    use Notifiable;

    // ---------------------------------------------------------------------------
    // Constants
    // ---------------------------------------------------------------------------

    /** Number of days considered "active" based on last_login_at. */
    public const int ACTIVE_DAYS = 30;

    /** Allowed values for the device_type column. */
    public const array DEVICE_TYPES = ['mobile', 'desktop', 'tablet'];

    /** Allowed values for the signup_source column. */
    public const array SIGNUP_SOURCES = ['google', 'facebook', 'twitter', 'direct', 'referral'];

    // ---------------------------------------------------------------------------
    // Fillable
    // ---------------------------------------------------------------------------

    /** @var list<string> */
    protected $fillable = [
        'name',
        'username',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'country',
        'city',
        'device_type',
        'signup_source',
        'avatar',
        'geo_lat',
        'geo_long',
        'last_login_at',
    ];

    // ---------------------------------------------------------------------------
    // Hidden
    // ---------------------------------------------------------------------------

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ---------------------------------------------------------------------------
    // Casts
    // ---------------------------------------------------------------------------

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'geo_lat'           => 'float',
            'geo_long'          => 'float',
            'password'          => 'hashed',
        ];
    }

    // ---------------------------------------------------------------------------
    // Filament
    // ---------------------------------------------------------------------------

    /**
     * Allow all authenticated users access to the Filament admin panel.
     * Restrict this in production (e.g. check a role/permission).
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    // ---------------------------------------------------------------------------
    // Accessors
    // ---------------------------------------------------------------------------

    /**
     * Returns 'Active' when the user logged in within ACTIVE_DAYS, else 'Inactive'.
     */
    public function getUserStatusAttribute(): string
    {
        if ($this->last_login_at === null) {
            return 'Inactive';
        }

        return $this->last_login_at->gte(now()->subDays(self::ACTIVE_DAYS))
            ? 'Active'
            : 'Inactive';
    }

    // ---------------------------------------------------------------------------
    // Query Scopes
    // ---------------------------------------------------------------------------

    /**
     * Filter by country (null = no filter applied).
     */
    public function scopeByCountry(Builder $query, ?string $country): Builder
    {
        if ($country === null || $country === '') {
            return $query;
        }

        return $query->where('country', $country);
    }

    /**
     * Filter by city (null = no filter applied).
     */
    public function scopeByCity(Builder $query, ?string $city): Builder
    {
        if ($city === null || $city === '') {
            return $query;
        }

        return $query->where('city', $city);
    }

    /**
     * Filter by device type (null = no filter applied).
     */
    public function scopeByDeviceType(Builder $query, ?string $deviceType): Builder
    {
        if ($deviceType === null || $deviceType === '') {
            return $query;
        }

        return $query->where('device_type', $deviceType);
    }

    /**
     * Filter by signup source (null = no filter applied).
     */
    public function scopeBySignupSource(Builder $query, ?string $source): Builder
    {
        if ($source === null || $source === '') {
            return $query;
        }

        return $query->where('signup_source', $source);
    }

    /**
     * Filter by activity status based on last_login_at.
     *
     * - $active = true  → last_login_at >= now() - $days  (recently active)
     * - $active = false → last_login_at <  now() - $days OR IS NULL  (inactive)
     * - $active = null  → no filter
     */
    public function scopeActive(Builder $query, ?bool $active, int $days = self::ACTIVE_DAYS): Builder
    {
        if ($active === null) {
            return $query;
        }

        $cutoff = now()->subDays($days);

        if ($active) {
            return $query->where('last_login_at', '>=', $cutoff);
        }

        return $query->where(function (Builder $q) use ($cutoff): void {
            $q->where('last_login_at', '<', $cutoff)
              ->orWhereNull('last_login_at');
        });
    }

    /**
     * Filter by whether the user has an avatar set.
     *
     * - $has = true  → avatar IS NOT NULL
     * - $has = false → avatar IS NULL
     * - $has = null  → no filter
     */
    public function scopeHasAvatar(Builder $query, ?bool $has): Builder
    {
        if ($has === null) {
            return $query;
        }

        return $has
            ? $query->whereNotNull('avatar')
            : $query->whereNull('avatar');
    }

    /**
     * Keyword search across username, email, city, and country.
     * Null or empty string disables the filter.
     */
    public function scopeKeyword(Builder $query, ?string $keyword): Builder
    {
        if ($keyword === null || trim($keyword) === '') {
            return $query;
        }

        $term = '%' . trim($keyword) . '%';

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('username', 'LIKE', $term)
              ->orWhere('email', 'LIKE', $term)
              ->orWhere('city', 'LIKE', $term)
              ->orWhere('country', 'LIKE', $term);
        });
    }

    /**
     * Filter records by a named period or a custom date range.
     *
     * Supported $period values: 'day', 'week', 'month', 'year', 'custom'
     * When 'custom', $start and $end (Y-m-d or Y-m-d H:i:s) are used.
     * Null $period disables the filter.
     */
    public function scopePeriod(
        Builder $query,
        ?string $period,
        ?string $start = null,
        ?string $end = null,
    ): Builder {
        if ($period === null || $period === '') {
            return $query;
        }

        $from = match ($period) {
            'day'   => now()->startOfDay(),
            'week'  => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year'  => now()->startOfYear(),
            default => null,
        };

        if ($period === 'custom') {
            if ($start !== null) {
                $query->where('created_at', '>=', $start);
            }

            if ($end !== null) {
                $query->where('created_at', '<=', $end);
            }

            return $query;
        }

        if ($from !== null) {
            $query->where('created_at', '>=', $from)
                  ->where('created_at', '<=', now()->endOfDay());
        }

        return $query;
    }

    /**
     * Geo-spatial scope: filter users within a radius (km) of a given coordinate.
     *
     * Uses a bounding-box pre-filter for performance, then applies the Haversine
     * formula via a raw SQL expression to eliminate false positives.
     *
     * Any null argument disables the scope entirely.
     *
     * @param  float|null  $lat       Centre latitude  (decimal degrees)
     * @param  float|null  $lng       Centre longitude (decimal degrees)
     * @param  float|null  $radiusKm  Search radius in kilometres
     */
    public function scopeWithinRadius(
        Builder $query,
        ?float $lat,
        ?float $lng,
        ?float $radiusKm,
    ): Builder {
        if ($lat === null || $lng === null || $radiusKm === null || $radiusKm <= 0) {
            return $query;
        }

        // Approximate degrees per km (1° lat ≈ 111.045 km)
        $degPerKm  = 1.0 / 111.045;
        $latDelta  = $radiusKm * $degPerKm;
        $lngDelta  = $radiusKm * $degPerKm / max(cos(deg2rad($lat)), 1e-10);

        // Bounding-box pre-filter (uses the index on geo_lat / geo_long if present)
        $query
            ->whereNotNull('geo_lat')
            ->whereNotNull('geo_long')
            ->whereBetween('geo_lat',  [$lat - $latDelta, $lat + $latDelta])
            ->whereBetween('geo_long', [$lng - $lngDelta, $lng + $lngDelta]);

        // Haversine formula — refine to exact circle
        $haversine = '(6371 * ACOS(
            COS(RADIANS(?)) * COS(RADIANS(geo_lat)) *
            COS(RADIANS(geo_long) - RADIANS(?)) +
            SIN(RADIANS(?)) * SIN(RADIANS(geo_lat))
        ))';

        $query->whereRaw("{$haversine} <= ?", [$lat, $lng, $lat, $radiusKm]);

        return $query;
    }
}
