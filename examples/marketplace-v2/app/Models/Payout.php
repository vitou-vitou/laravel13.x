<?php

namespace App\Models;

use App\Enums\PayoutStatus;
use App\Models\Scopes\VendorTenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([VendorTenantScope::class])]
class Payout extends Model
{
    /** @use HasFactory<\Database\Factories\PayoutFactory> */
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'order_group_id',
        'status',
        'amount_cents',
    ];

    protected function casts(): array
    {
        return [
            'status' => PayoutStatus::class,
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function orderGroup(): BelongsTo
    {
        return $this->belongsTo(OrderGroup::class);
    }
}
