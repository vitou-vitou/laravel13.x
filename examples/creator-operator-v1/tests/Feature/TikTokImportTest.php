<?php

namespace Tests\Feature;

use App\Enums\PublishStatus;
use App\Models\Creator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TikTokImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_import_new_urls_from_jsonl(): void
    {
        $operator = User::factory()->operator()->create();
        $creator = Creator::factory()->create([
            'last_run_date' => '2026-06-01',
        ]);

        $jsonl = implode("\n", [
            json_encode([
                'video_id' => '7123456789012345678',
                'video_url' => 'https://www.tiktok.com/@pilot/video/7123456789012345678',
                'caption' => 'Imported caption',
                'posted_date' => '2026-06-10',
                'status' => 'ok',
            ]),
            json_encode([
                'video_id' => '7123456789012345679',
                'video_url' => 'https://www.tiktok.com/@pilot/video/7123456789012345679',
                'caption' => 'Duplicate skip',
                'posted_date' => '2026-06-11',
                'status' => 'ok',
            ]),
        ]);

        $creator->publishLogEntries()->create([
            'logged_on' => '2026-06-10',
            'tiktok_url' => 'https://www.tiktok.com/@pilot/video/7123456789012345679',
            'status' => PublishStatus::PendingApproval,
        ]);

        $response = $this->actingAs($operator)->post(route('operator.creators.import.store', $creator), [
            'selected_urls' => [
                'https://www.tiktok.com/@pilot/video/7123456789012345678',
            ],
            'titles' => [
                'https://www.tiktok.com/@pilot/video/7123456789012345678' => 'Imported caption',
            ],
        ]);

        $response->assertRedirect(route('operator.creators.show', $creator));

        $this->assertDatabaseHas('publish_log_entries', [
            'creator_id' => $creator->id,
            'tiktok_url' => 'https://www.tiktok.com/@pilot/video/7123456789012345678',
            'title_variant' => 'Imported caption',
            'status' => PublishStatus::PendingApproval->value,
        ]);

        $this->assertSame(2, $creator->publishLogEntries()->count());
    }
}
