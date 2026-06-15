<?php

namespace Database\Factories;

use App\Models\Creator;
use App\Models\WeeklyMetric;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WeeklyMetric>
 */
class WeeklyMetricFactory extends Factory
{
    protected $model = WeeklyMetric::class;

    public function definition(): array
    {
        return [
            'creator_id' => Creator::factory(),
            'week_start' => now()->startOfWeek()->toDateString(),
            'videos_published' => fake()->numberBetween(1, 5),
            'best_video_url' => 'https://youtube.com/shorts/'.fake()->regexify('[a-z0-9]{11}'),
            'best_video_views' => fake()->numberBetween(500, 5000),
            'experiment' => 'Posted 7pm vs 9am batch',
            'experiment_result' => '+18% views on 7pm batch',
            'operator_notes' => null,
        ];
    }
}
