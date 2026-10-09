<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'question',
        'answer',
        'action_type',
        'target_department_id',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function targetDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'target_department_id');
    }
}
