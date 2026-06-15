<?php

namespace App\Models;

use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'creator_id',
    'logged_on',
    'tiktok_url',
    'tiktok_thumbnail_url',
    'yt_url',
    'ig_url',
    'yt_video_id',
    'title_variant',
    'posted_time',
    'status',
    'views_yt_7d',
    'views_ig_7d',
    'notes',
    'approved_at',
    'approved_by_user_id',
])]
class PublishLogEntry extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'logged_on' => 'date',
            'posted_time' => 'datetime',
            'status' => PublishStatus::class,
            'approved_at' => 'datetime',
            'views_yt_7d' => 'integer',
            'views_ig_7d' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
