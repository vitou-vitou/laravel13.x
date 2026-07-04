<?php

namespace App\Models;

use App\Enums\OrderGroupStatus;
use App\Models\Scopes\VendorTenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ScopedBy([VendorTenantScope::class])]
class OrderGroup extends Model
{
    /** @use HasFactory<\Database\Factories\OrderGroupFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'vendor_id',
        'status',
        'commission_bps',
        'subtotal_cents',
        'tracking_number',
        'shipped_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderGroupStatus::class,
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    public function payout(): HasOne
    {
        return $this->hasOne(Payout::class);
    }

    public function dispute(): HasOne
    {
        return $this->hasOne(Dispute::class);
    }

    public function vendorNetCents(): int
    {
        $commission = (int) round($this->subtotal_cents * ($this->commission_bps / 10000));

        return $this->subtotal_cents - $commission;
    }
}
