<?php

namespace Database\Seeders\Support;

use App\Enums\MusicPolicy;
use App\Enums\PublishStatus;
use App\Enums\ServiceTier;
use Carbon\Carbon;

class KhmerTravelRoster
{
    public const TARGET_COUNT = 500;

    /** @var list<string> */
    private const KHMER_GIVEN = [
        'Sokha', 'Vibol', 'Visal', 'Dara', 'Kosal', 'Rithy', 'Sopheap', 'Bopha', 'Channary', 'Neary',
        'Srey', 'Piseth', 'Chenda', 'Vannak', 'Sothea', 'Nary', 'Hout', 'Maly', 'Kimheng', 'Sreymom',
        'Panha', 'Sambath', 'Chhay', 'Sokun', 'Veasna', 'Ratha', 'Sreypov', 'Kunthea', 'Sokunthea', 'Chanthea',
    ];

    /** @var list<string> */
    private const KHMER_PLACES = [
        'angkor', 'siemreap', 'kampot', 'kep', 'battambang', 'kohkong', 'mondulkiri', 'ratanakiri', 'kratie',
        'takeo', 'pursat', 'preahvihear', 'kampongcham', 'phnompenh', 'sihanoukville', 'bokor', 'otres',
        'kbal speh', 'tonlesap', 'preahkhan', 'bayon', 'bakong', 'phnomkulen', 'chisor', 'kohrong',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public static function build(): array
    {
        $rows = [];
        $handles = [];

        foreach (require database_path('data/khmer-travel-public-creators.php') as $index => $public) {
            $handle = self::normalizeHandle($public['handle']);
            if (isset($handles[$handle])) {
                continue;
            }
            $handles[$handle] = true;
            $rows[] = self::creatorRow(
                index: count($rows) + 1,
                name: $public['name'],
                handle: $handle,
                source: 'public_list',
                profileUrl: $public['profile_url'],
                onboardingNotes: $public['onboarding_notes'],
            );
        }

        $syntheticIndex = 0;
        while (count($rows) < self::TARGET_COUNT) {
            $syntheticIndex++;
            $given = self::KHMER_GIVEN[$syntheticIndex % count(self::KHMER_GIVEN)];
            $place = self::KHMER_PLACES[intdiv($syntheticIndex, count(self::KHMER_GIVEN)) % count(self::KHMER_PLACES)];
            $placeSlug = preg_replace('/\s+/', '', strtolower($place)) ?? 'cambodia';
            $handle = self::normalizeHandle(sprintf('khmer_travel_%03d_%s', $syntheticIndex, $placeSlug));

            if (isset($handles[$handle])) {
                $handle = self::normalizeHandle(sprintf('khmer_travel_%03d', $syntheticIndex));
            }

            if (isset($handles[$handle])) {
                continue;
            }

            $handles[$handle] = true;
            $rows[] = self::creatorRow(
                index: count($rows) + 1,
                name: "{$given} {$placeSlug}",
                handle: $handle,
                source: 'synthetic_demo',
                profileUrl: '',
                onboardingNotes: 'Khmer travel demo roster',
            );
        }

        return $rows;
    }

    /**
     * @return array{public: int, synthetic: int}
     */
    public static function sourceCounts(array $rows): array
    {
        $public = 0;
        $synthetic = 0;

        foreach ($rows as $row) {
            if ($row['source'] === 'public_list') {
                $public++;
            } else {
                $synthetic++;
            }
        }

        return ['public' => $public, 'synthetic' => $synthetic];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    public static function exportCsv(array $rows, string $path): void
    {
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $handle = fopen($path, 'w');
        if ($handle === false) {
            throw new \RuntimeException("Unable to write CSV: {$path}");
        }

        fputcsv($handle, [
            'handle',
            'name',
            'tiktok_url',
            'tier',
            'music_policy',
            'youtube_manager_email',
            'meta_manager_email',
            'creator_email',
            'last_run_date',
            'onboarding_notes',
            'source',
            'profile_url',
        ]);

        foreach ($rows as $row) {
            fputcsv($handle, [
                $row['handle'],
                $row['name'],
                $row['tiktok_url'],
                $row['tier'],
                $row['music_policy'],
                $row['youtube_manager_email'],
                $row['meta_manager_email'],
                $row['creator_email'],
                $row['last_run_date'],
                $row['onboarding_notes'],
                $row['source'],
                $row['profile_url'],
            ]);
        }

        fclose($handle);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function publishLogRows(int $creatorId, int $creatorIndex, string $creatorHandle): array
    {
        $thumb = url('/images/demo-video-thumb.svg');
        $statuses = [
            PublishStatus::PendingApproval,
            PublishStatus::Approved,
            PublishStatus::Published,
            PublishStatus::SkippedMusic,
            PublishStatus::Published,
        ];
        $rowCount = 3 + ($creatorIndex % 3);
        $entries = [];
        $now = Carbon::create(2026, 6, 15, 12, 0, 0);

        for ($row = 1; $row <= $rowCount; $row++) {
            $status = $statuses[($creatorIndex + $row) % count($statuses)];
            $videoId = self::videoId($creatorIndex, $row);
            $loggedOn = $now->copy()->subDays($row + ($creatorIndex % 5))->toDateString();
            $postedTime = in_array($status, [PublishStatus::Published, PublishStatus::SkippedMusic], true)
                ? $now->copy()->subDays($row)->setTime(10 + ($row % 8), 0)
                : null;

            $entries[] = [
                'creator_id' => $creatorId,
                'logged_on' => $loggedOn,
                'tiktok_url' => "https://www.tiktok.com/@{$creatorHandle}/video/{$videoId}",
                'tiktok_thumbnail_url' => $thumb,
                'yt_url' => $status === PublishStatus::Published ? "https://youtube.com/shorts/{$videoId}" : null,
                'ig_url' => $status === PublishStatus::Published ? "https://instagram.com/reel/{$videoId}" : null,
                'yt_video_id' => $status === PublishStatus::Published ? substr($videoId, -11) : null,
                'title_variant' => "Khmer travel | {$creatorHandle} clip {$row}",
                'posted_time' => $postedTime,
                'status' => $status->value,
                'views_yt_7d' => $status === PublishStatus::Published ? 500 + ($creatorIndex * 7) + ($row * 13) : null,
                'views_ig_7d' => $status === PublishStatus::Published ? 200 + ($creatorIndex * 3) + ($row * 11) : null,
                'notes' => $status === PublishStatus::SkippedMusic ? 'Skipped — licensed Khmer pop track' : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $entries;
    }

    public static function weeklyMetricRow(int $creatorId, int $creatorIndex, string $bestVideoId): array
    {
        $now = Carbon::create(2026, 6, 15, 12, 0, 0);

        return [
            'creator_id' => $creatorId,
            'week_start' => $now->copy()->startOfWeek()->toDateString(),
            'videos_published' => 1 + ($creatorIndex % 4),
            'best_video_url' => "https://youtube.com/shorts/{$bestVideoId}",
            'best_video_views' => 800 + ($creatorIndex * 17),
            'experiment' => 'Khmer hook A vs B',
            'experiment_result' => ($creatorIndex % 2 === 0) ? 'Hook B +9% saves' : 'Hook A +6% watch time',
            'operator_notes' => 'Khmer travel demo roster — auto weekly row',
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    public static function videoId(int $creatorIndex, int $row): string
    {
        return '7123456789'.str_pad((string) $creatorIndex, 3, '0', STR_PAD_LEFT).(string) $row;
    }

    /**
     * @return array<string, mixed>
     */
    private static function creatorRow(
        int $index,
        string $name,
        string $handle,
        string $source,
        string $profileUrl,
        string $onboardingNotes,
    ): array {
        $tiers = [ServiceTier::Lite, ServiceTier::Standard];
        $policies = [MusicPolicy::Skip, MusicPolicy::Replace, MusicPolicy::CreatorExport];
        $day = 1 + (($index - 1) % 28);
        $lastRun = sprintf('2026-06-%02d', $day);

        return [
            'name' => $name,
            'handle' => $handle,
            'tiktok_url' => 'https://www.tiktok.com/@'.$handle,
            'tier' => $tiers[$index % count($tiers)]->value,
            'music_policy' => $policies[$index % count($policies)]->value,
            'youtube_manager_email' => sprintf('yt-agency-%03d@demo.creator-operator.test', ($index % 50) + 1),
            'meta_manager_email' => sprintf('meta-agency-%03d@demo.creator-operator.test', ($index % 50) + 1),
            'creator_email' => sprintf('khmer_travel_%03d@demo.creator-operator.test', $index),
            'last_run_date' => $lastRun,
            'onboarding_notes' => $onboardingNotes,
            'source' => $source,
            'profile_url' => $profileUrl,
            'creator_index' => $index,
        ];
    }

    private static function normalizeHandle(string $handle): string
    {
        $handle = strtolower(trim($handle));
        $handle = preg_replace('/[^a-z0-9._-]+/', '_', $handle) ?? $handle;
        $handle = trim($handle, '._-');

        return $handle !== '' ? $handle : 'khmer_travel';
    }
}
