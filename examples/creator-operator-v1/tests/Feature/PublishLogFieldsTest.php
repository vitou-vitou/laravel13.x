<?php

namespace Tests\Feature;

use App\Enums\PublishStatus;
use App\Models\Creator;
use App\Models\PublishLogEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishLogFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_save_extended_publish_fields_on_edit(): void
    {
        $operator = User::factory()->operator()->create();
        $creator = Creator::factory()->create();
        $entry = PublishLogEntry::factory()->create([
            'creator_id' => $creator->id,
            'status' => PublishStatus::Approved,
        ]);

        $this->actingAs($operator)->put(route('operator.creators.publish-log.update', [$creator, $entry]), [
            'logged_on' => '2026-06-14',
            'tiktok_url' => $entry->tiktok_url,
            'title_variant' => 'Updated title',
            'yt_url' => 'https://youtube.com/shorts/xyz',
            'ig_url' => 'https://instagram.com/reel/xyz',
            'yt_video_id' => 'xyz',
            'posted_time' => '2026-06-14T10:30',
            'status' => PublishStatus::Approved->value,
            'views_yt_7d' => 1500,
            'views_ig_7d' => 900,
            'notes' => 'IG: caption block here',
        ])->assertRedirect(route('operator.creators.show', $creator));

        $entry->refresh();

        $this->assertSame('xyz', $entry->yt_video_id);
        $this->assertSame(1500, $entry->views_yt_7d);
        $this->assertSame(900, $entry->views_ig_7d);
        $this->assertNotNull($entry->posted_time);
    }
}
