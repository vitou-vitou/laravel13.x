<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\Creator;
use App\Models\PublishLogEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PublishLogEntry>
 */
class PublishLogEntryFactory extends Factory
{
    protected $model = PublishLogEntry::class;

    public function definition(): array
    {
        return [
            'creator_id' => Creator::factory(),
            'logged_on' => now()->toDateString(),
            'tiktok_url' => 'https://www.tiktok.com/@example/video/7123456789012345678',
            'yt_url' => null,
            'ig_url' => null,
            'yt_video_id' => null,
            'title_variant' => fake()->sentence(6),
            'posted_time' => null,
            'status' => PublishStatus::PendingApproval,
            'views_yt_7d' => null,
            'views_ig_7d' => null,
            'notes' => null,
            'approved_at' => null,
            'approved_by_user_id' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => PublishStatus::Approved]);
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => PublishStatus::Published,
            'yt_url' => 'https://youtube.com/shorts/abc123',
            'posted_time' => now(),
        ]);
    }
}
