<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilityRule extends Model
{
    protected $fillable = ['bookable_resource_id', 'day_of_week', 'start_time', 'end_time'];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
        ];
    }

    public function bookableResource(): BelongsTo
    {
        return $this->belongsTo(BookableResource::class);
    }
}
