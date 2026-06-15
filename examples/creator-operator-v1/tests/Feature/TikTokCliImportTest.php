<?php

namespace Tests\Feature;

use App\Enums\PublishStatus;
use App\Models\Creator;
use App\Models\User;
use App\Services\TikTokMetadataCliRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TikTokCliImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_preview_import_from_mocked_cli(): void
    {
        $operator = User::factory()->operator()->create();
        $creator = Creator::factory()->create([
            'handle' => 'pilot',
            'last_run_date' => '2026-06-01',
        ]);

        $jsonl = json_encode([
            'video_id' => '7123456789012345678',
            'video_url' => 'https://www.tiktok.com/@pilot/video/7123456789012345678',
            'caption' => 'CLI caption',
            'posted_date' => '2026-06-10',
            'status' => 'ok',
        ]);

        $this->mock(TikTokMetadataCliRunner::class, function ($mock) use ($jsonl): void {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('fetchJsonl')->once()->andReturn($jsonl."\n");
        });

        $response = $this->actingAs($operator)->post(route('operator.creators.import.cli', $creator), [
            'limit' => 10,
        ]);

        $response->assertOk();
        $response->assertSee('CLI fetch completed');
        $response->assertSee('7123456789012345678');
    }

    public function test_cli_fetch_surfaces_errors(): void
    {
        $operator = User::factory()->operator()->create();
        $creator = Creator::factory()->create();

        $this->mock(TikTokMetadataCliRunner::class, function ($mock): void {
            $mock->shouldReceive('fetchJsonl')->once()->andThrow(new \RuntimeException('yt-dlp not installed'));
        });

        $response = $this->actingAs($operator)->post(route('operator.creators.import.cli', $creator));

        $response->assertRedirect(route('operator.creators.import.index', $creator));
        $response->assertSessionHasErrors('cli');
    }
}
