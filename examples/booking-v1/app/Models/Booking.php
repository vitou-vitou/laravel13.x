<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'bookable_resource_id',
        'service_id',
        'customer_id',
        'starts_at',
        'ends_at',
        'status',
        'hold_expires_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'hold_expires_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'status' => BookingStatus::class,
        ];
    }

    public function bookableResource(): BelongsTo
    {
        return $this->belongsTo(BookableResource::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
