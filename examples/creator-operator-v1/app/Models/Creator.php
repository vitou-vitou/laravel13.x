<?php

namespace App\Models;

use App\Enums\MusicPolicy;
use App\Enums\PublishStatus;
use App\Enums\ServiceTier;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'handle',
    'tiktok_url',
    'tier',
    'music_policy',
    'youtube_manager_email',
    'meta_manager_email',
    'last_run_date',
    'onboarding_notes',
])]
class Creator extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'tier' => ServiceTier::class,
            'music_policy' => MusicPolicy::class,
            'last_run_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function publishLogEntries(): HasMany
    {
        return $this->hasMany(PublishLogEntry::class);
    }

    public function weeklyMetrics(): HasMany
    {
        return $this->hasMany(WeeklyMetric::class);
    }

    public function monthlySettlements(): HasMany
    {
        return $this->hasMany(MonthlySettlement::class);
    }

    public function pendingApprovalsCount(): int
    {
        return $this->publishLogEntries()
            ->where('status', PublishStatus::PendingApproval)
            ->count();
    }
}
