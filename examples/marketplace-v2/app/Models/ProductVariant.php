<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    /** @use HasFactory<\Database\Factories\ProductVariantFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'price_cents',
        'stock_qty',
        'status',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function cartLines(): HasMany
    {
        return $this->hasMany(CartLine::class);
    }

    public function orderLines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    public function hasStock(int $quantity): bool
    {
        return $this->stock_qty >= $quantity;
    }

    public function formattedPrice(): string
    {
        return '$'.number_format($this->price_cents / 100, 2);
    }
}
