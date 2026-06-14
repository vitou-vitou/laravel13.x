<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderLine extends Model
{
    /** @use HasFactory<\Database\Factories\OrderLineFactory> */
    use HasFactory;
    protected $fillable = [
        'order_group_id',
        'product_variant_id',
        'quantity',
        'unit_price_cents',
        'product_name_snapshot',
        'variant_name_snapshot',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(OrderGroup::class, 'order_group_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function lineTotalCents(): int
    {
        return $this->unit_price_cents * $this->quantity;
    }
}
