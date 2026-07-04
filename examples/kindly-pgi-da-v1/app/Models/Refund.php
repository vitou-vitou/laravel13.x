<?php

namespace App\Models;

use App\Enums\RefundStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    /** @use HasFactory<\Database\Factories\RefundFactory> */
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'order_id',
        'initiated_by_user_id',
        'amount_cents',
        'status',
        'reason',
        'stripe_refund_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => RefundStatus::class,
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by_user_id');
    }
}
