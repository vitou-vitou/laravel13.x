<?php

namespace App\Models;

use App\Enums\PromoCodeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    /** @use HasFactory<\Database\Factories\PromoCodeFactory> */
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'code',
        'type',
        'value',
        'min_subtotal_cents',
        'max_uses',
        'uses_count',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => PromoCodeType::class,
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isPlatformWide(): bool
    {
        return $this->vendor_id === null;
    }

    public function isUsable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            return false;
        }

        return true;
    }
}
