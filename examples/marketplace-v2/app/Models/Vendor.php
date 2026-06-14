<?php

namespace App\Models;

use App\Enums\VendorStatus;
use App\Models\Scopes\VendorTenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([VendorTenantScope::class])]
class Vendor extends Model
{
    /** @use HasFactory<\Database\Factories\VendorFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_name',
        'slug',
        'status',
        'stripe_account_id',
        'rating_avg',
        'rating_count',
    ];

    protected function casts(): array
    {
        return [
            'status' => VendorStatus::class,
            'stripe_account_id' => 'encrypted',
            'rating_avg' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orderGroups(): HasMany
    {
        return $this->hasMany(OrderGroup::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }

    public function isActive(): bool
    {
        return $this->status === VendorStatus::Active;
    }
}
