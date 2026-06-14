<?php

namespace Database\Seeders;

use App\Enums\MusicPolicy;
use App\Enums\OperatorPlan;
use App\Enums\PublishStatus;
use App\Enums\ServiceTier;
use App\Enums\UserRole;
use App\Models\Creator;
use App\Models\PublishLogEntry;
use App\Models\User;
use App\Models\WeeklyMetric;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $operator = User::query()->create([
            'name' => 'Ops Operator',
            'email' => 'operator@creator-operator.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Operator,
            'operator_plan' => OperatorPlan::Starter,
            'email_verified_at' => now(),
        ]);

        $creatorUser = User::query()->create([
            'name' => 'Pilot Creator',
            'email' => 'creator@creator-operator.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Creator,
            'email_verified_at' => now(),
        ]);

        $creator = Creator::query()->create([
            'user_id' => $creatorUser->id,
            'handle' => 'pilotcreator',
            'tiktok_url' => 'https://www.tiktok.com/@pilotcreator',
            'tier' => ServiceTier::Lite,
            'music_policy' => MusicPolicy::Skip,
            'youtube_manager_email' => 'yt-manager@example.com',
            'meta_manager_email' => 'meta-manager@example.com',
            'onboarding_notes' => 'Pilot row from docs/creator-commission/templates/publish-log.csv',
        ]);

        PublishLogEntry::query()->create([
            'creator_id' => $creator->id,
            'logged_on' => now()->toDateString(),
            'tiktok_url' => 'https://www.tiktok.com/@pilotcreator/video/7123456789012345678',
            'title_variant' => 'Example SEO title | hook here',
            'status' => PublishStatus::PendingApproval,
            'notes' => 'Seeded pending row — approve as creator to test flow.',
        ]);

        PublishLogEntry::query()->create([
            'creator_id' => $creator->id,
            'logged_on' => now()->subDay()->toDateString(),
            'tiktok_url' => 'https://www.tiktok.com/@pilotcreator/video/7123456789012345679',
            'title_variant' => 'Already live example',
            'yt_url' => 'https://youtube.com/shorts/abc123',
            'ig_url' => 'https://instagram.com/reel/xyz789',
            'yt_video_id' => 'abc123',
            'posted_time' => now()->subDay(),
            'status' => PublishStatus::Published,
            'views_yt_7d' => 1200,
            'views_ig_7d' => 800,
        ]);

        WeeklyMetric::query()->create([
            'creator_id' => $creator->id,
            'week_start' => now()->startOfWeek()->toDateString(),
            'videos_published' => 2,
            'best_video_url' => 'https://youtube.com/shorts/abc123',
            'best_video_views' => 1200,
            'experiment' => 'Hook A vs Hook B',
            'experiment_result' => 'Hook B +12% CTR',
            'operator_notes' => 'Seeded demo row for reports tab.',
        ]);

        unset($operator, $creatorUser, $creator);
    }
}
