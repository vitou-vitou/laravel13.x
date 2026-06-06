<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'amount_cents',
        'status',
        'ordered_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ordered_at' => 'datetime',
        ];
    }

    public function formattedAmount(): string
    {
        return '$'.number_format($this->amount_cents / 100, 2);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'paid' => 'Paid',
            'pending' => 'Pending',
            'refunded' => 'Refunded',
            default => ucfirst($this->status),
        };
    }
}
