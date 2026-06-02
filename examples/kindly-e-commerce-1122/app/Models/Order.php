<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'subtotal_cents',
        'discount_cents',
        'coupon_code',
        'total_cents',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'paid_at',
        'shipped_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
    ];

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isShipped(): bool
    {
        return $this->status === 'shipped';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function formattedTotal(): string
    {
        return '$'.number_format($this->total_cents / 100, 2);
    }

    public function restoreStock(): void
    {
        $this->loadMissing('items.product');

        foreach ($this->items as $item) {
            $item->product->increment('stock_quantity', $item->quantity);
        }
    }
}
