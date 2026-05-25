<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'sku', 'category', 'quantity', 'low_stock_threshold',
        'price', 'cost', 'supplier', 'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost'  => 'decimal:2',
    ];

    public function getStockStatusAttribute(): string
    {
        if ($this->quantity === 0) return 'out_of_stock';
        if ($this->quantity <= $this->low_stock_threshold) return 'low_stock';
        return 'in_stock';
    }
}
