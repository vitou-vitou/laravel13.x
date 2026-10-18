<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'phone_last_four',
        'department_id',
        'test_category',
        'status',
        'pickup_counter',
        'ready_at',
        'collected_at',
    ];

    protected function casts(): array
    {
        return [
            'ready_at' => 'datetime',
            'collected_at' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
