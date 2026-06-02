<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function discountCentsFor(int $subtotalCents): int
    {
        if ($subtotalCents <= 0) {
            return 0;
        }

        return match ($this->type) {
            'percent' => (int) min($subtotalCents, round($subtotalCents * $this->value / 100)),
            'fixed' => min($this->value, $subtotalCents),
            default => 0,
        };
    }
}
