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
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class AgencyDemoSeeder extends Seeder
{
    private const CREATOR_COUNT = 100;

    private const YT_MANAGER = 'yt-ops@agency-demo.test';

    private const META_MANAGER = 'meta-ops@agency-demo.test';

    private const THUMB_URL = '/images/demo-video-thumb.svg';

    /** @var list<string> */
    private const TRAVEL_TITLES = [
        'Hidden gems in Kyoto | Japan travel guide',
        'Best street food in Bangkok | budget eats',
        '48 hours in Lisbon | weekend itinerary',
        'Santorini sunset spots | Greece travel tips',
        'Bali rice terraces hike | Ubud day trip',
        'Iceland ring road stops | road trip planner',
        'Mexico City food crawl | CDMX travel vlog',
        'Amalfi Coast boat day | Italy coastal guide',
        'Dubai desert safari tips | UAE travel hack',
        'Seoul cafe hopping | Korea travel diary',
    ];

    public function run(): void
    {
        if (! $this->agencyDemoEnabled()) {
            $this->command?->warn('AgencyDemoSeeder skipped — set SEED_AGENCY_DEMO=true to run.');

            return;
        }

        $operator = $this->resolveOperator();
        $operator->forceFill(['operator_plan' => OperatorPlan::Demo])->save();

        $tiers = [ServiceTier::Lite, ServiceTier::Standard];
        $policies = [MusicPolicy::Skip, MusicPolicy::Replace, MusicPolicy::CreatorExport];
        $thumbUrl = url(self::THUMB_URL);

        for ($i = 1; $i <= self::CREATOR_COUNT; $i++) {
            $handle = sprintf('agency_creator_%03d', $i);

            if (Creator::query()->where('handle', $handle)->exists()) {
                continue;
            }

            $creator = Creator::query()->create([
                'user_id' => null,
                'handle' => $handle,
                'tiktok_url' => 'https://www.tiktok.com/@'.$handle,
                'tier' => $tiers[($i - 1) % count($tiers)],
                'music_policy' => $policies[($i - 1) % count($policies)],
                'youtube_manager_email' => self::YT_MANAGER,
                'meta_manager_email' => self::META_MANAGER,
                'last_run_date' => Carbon::create(2026, 6, 1)->addDays(($i - 1) % 30),
                'onboarding_notes' => 'Agency demo roster',
            ]);

            $entryCount = 4 + ($i % 2);

            for ($e = 0; $e < $entryCount; $e++) {
                $status = $this->entryStatus($i, $e);
                $videoId = $this->videoId($i, $e);
                $loggedOn = Carbon::create(2026, 6, 1)->addDays(($i + $e) % 14);

                $payload = [
                    'creator_id' => $creator->id,
                    'logged_on' => $loggedOn->toDateString(),
                    'tiktok_url' => 'https://www.tiktok.com/@'.$handle.'/video/'.$videoId,
                    'tiktok_thumbnail_url' => $thumbUrl,
                    'title_variant' => self::TRAVEL_TITLES[($i + $e) % count(self::TRAVEL_TITLES)],
                    'status' => $status,
                    'notes' => $status === PublishStatus::SkippedMusic
                        ? 'Skipped — licensed audio conflict'
                        : null,
                ];

                if ($status === PublishStatus::Published) {
                    $ytId = sprintf('yt%d%02d', $i, $e);
                    $postedAt = $loggedOn->copy()->addDay()->setTime(19, 0);
                    $payload['yt_url'] = 'https://youtube.com/shorts/'.$ytId;
                    $payload['ig_url'] = 'https://instagram.com/reel/'.sprintf('ig%d%02d', $i, $e);
                    $payload['yt_video_id'] = $ytId;
                    $payload['posted_time'] = $postedAt;
                    $payload['views_yt_7d'] = 800 + (($i * 17 + $e * 43) % 4200);
                    $payload['views_ig_7d'] = 400 + (($i * 11 + $e * 29) % 2800);
                }

                if ($status === PublishStatus::Approved) {
                    $payload['approved_at'] = $loggedOn->copy()->addHours(6);
                }

                PublishLogEntry::query()->create($payload);
            }

            WeeklyMetric::query()->create([
                'creator_id' => $creator->id,
                'week_start' => Carbon::create(2026, 6, 9)->toDateString(),
                'videos_published' => min($entryCount, 3),
                'best_video_url' => 'https://youtube.com/shorts/yt'.$i.'00',
                'best_video_views' => 900 + ($i * 37 % 3000),
                'experiment' => null,
                'experiment_result' => null,
                'operator_notes' => 'Agency demo week snapshot',
            ]);
        }

        $this->command?->info(sprintf(
            'Agency demo seeded: %d creators, operator plan set to demo (limit %d).',
            Creator::query()->where('handle', 'like', 'agency_creator_%')->count(),
            OperatorPlan::Demo->creatorLimit(),
        ));
    }

    private function agencyDemoEnabled(): bool
    {
        $value = getenv('SEED_AGENCY_DEMO');

        if ($value === false) {
            $value = $_ENV['SEED_AGENCY_DEMO'] ?? $_SERVER['SEED_AGENCY_DEMO'] ?? false;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function resolveOperator(): User
    {
        $operator = User::query()
            ->where('role', UserRole::Operator)
            ->orderBy('id')
            ->first();

        if ($operator !== null) {
            return $operator;
        }

        return User::query()->create([
            'name' => 'Agency Demo Operator',
            'email' => 'operator@agency-demo.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Operator,
            'operator_plan' => OperatorPlan::Demo,
            'email_verified_at' => now(),
        ]);
    }

    private function entryStatus(int $creatorIndex, int $entryIndex): PublishStatus
    {
        $pattern = [
            PublishStatus::PendingApproval,
            PublishStatus::Approved,
            PublishStatus::Published,
            PublishStatus::SkippedMusic,
            PublishStatus::Published,
        ];

        return $pattern[($creatorIndex + $entryIndex) % count($pattern)];
    }

    private function videoId(int $creatorIndex, int $entryIndex): string
    {
        return '7123'.sprintf('%015d', ($creatorIndex * 10) + $entryIndex + 1);
    }
}
