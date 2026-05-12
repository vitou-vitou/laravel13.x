<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id', 'status', 'subtotal', 'tax_amount', 'shipping_cost',
        'total', 'currency', 'payment_method', 'payment_terms', 'due_date',
        'shipping_method', 'shipping_address', 'estimated_delivery', 'priority',
        'internal_notes', 'customer_notes', 'tax_rate', 'confirmed_at',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'due_date'         => 'date',
        'estimated_delivery' => 'date',
        'confirmed_at'     => 'datetime',
        'subtotal'         => 'decimal:2',
        'tax_amount'       => 'decimal:2',
        'shipping_cost'    => 'decimal:2',
        'total'            => 'decimal:2',
        'tax_rate'         => 'decimal:2',
    ];

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
