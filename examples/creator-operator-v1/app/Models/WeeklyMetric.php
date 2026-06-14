<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'creator_id',
    'week_start',
    'videos_published',
    'best_video_url',
    'best_video_views',
    'experiment',
    'experiment_result',
    'operator_notes',
])]
class WeeklyMetric extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'videos_published' => 'integer',
            'best_video_views' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }
}
