<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'promo_code_id',
        'status',
        'subtotal_cents',
        'discount_cents',
        'total_cents',
        'shipping_address_snapshot',
        'stripe_checkout_session_id',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'paid_at' => 'datetime',
            'shipping_address_snapshot' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(OrderGroup::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function formattedTotal(): string
    {
        return '$'.number_format($this->total_cents / 100, 2);
    }

    public function isPending(): bool
    {
        return $this->status === OrderStatus::PendingPayment;
    }

    public function isPaid(): bool
    {
        return $this->status === OrderStatus::Paid;
    }

    public function restoreStock(): void
    {
        $this->loadMissing('groups.lines.variant');

        foreach ($this->groups as $group) {
            foreach ($group->lines as $line) {
                $line->variant?->increment('stock_qty', $line->quantity);
            }
        }
    }
}
