<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'customer_name',
        'customer_email',
        'task_type',
        'priority',
        'status',
        'notes',
        'handler_id',
        'due_date',
    ];

    protected $casts = [
        'priority' => TaskPriority::class,
        'status' => TaskStatus::class,
        'due_date' => 'date',
    ];

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handler_id');
    }
}
