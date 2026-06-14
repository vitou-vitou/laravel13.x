<?php

namespace Tests\Feature;

use App\Models\Creator;
use App\Models\User;
use App\Models\WeeklyMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeeklyMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_record_weekly_metrics(): void
    {
        $operator = User::factory()->operator()->create();
        $creator = Creator::factory()->create();

        $response = $this->actingAs($operator)->post(route('operator.creators.metrics.store', $creator), [
            'week_start' => '2026-06-09',
            'videos_published' => 3,
            'best_video_url' => 'https://youtube.com/shorts/demo',
            'best_video_views' => 5000,
            'experiment' => 'Thumbnail test',
            'experiment_result' => 'Variant B won',
            'operator_notes' => 'Logged after batch step 7',
        ]);

        $response->assertRedirect(route('operator.creators.metrics.index', $creator));

        $this->assertDatabaseHas('weekly_metrics', [
            'creator_id' => $creator->id,
            'videos_published' => 3,
            'experiment' => 'Thumbnail test',
        ]);
    }

    public function test_creator_can_view_reports(): void
    {
        $creatorUser = User::factory()->creator()->create();
        $creator = Creator::factory()->create(['user_id' => $creatorUser->id]);

        WeeklyMetric::factory()->create([
            'creator_id' => $creator->id,
            'videos_published' => 2,
        ]);

        $this->actingAs($creatorUser)
            ->get(route('creator.reports.index'))
            ->assertOk()
            ->assertSee('2');
    }
}
