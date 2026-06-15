<?php

namespace Tests\Feature;

use App\Enums\PublishStatus;
use App\Mail\ApprovalBatchReadyMail;
use App\Models\Creator;
use App\Models\PublishLogEntry;
use App\Models\User;
use App\Services\TikTokThumbnailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CompetitiveUxAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_adding_pending_row_sends_approval_batch_email(): void
    {
        Mail::fake();

        $operator = User::factory()->operator()->create();
        $creatorUser = User::factory()->creator()->create(['email' => 'creator@example.test']);
        $creator = Creator::factory()->create(['user_id' => $creatorUser->id]);

        $this->mock(TikTokThumbnailService::class, function ($mock): void {
            $mock->shouldReceive('hydrateEntry')->once();
        });

        $this->actingAs($operator)->post(route('operator.creators.publish-log.store', $creator), [
            'logged_on' => '2026-06-15',
            'tiktok_url' => 'https://www.tiktok.com/@pilot/video/7123456789012345678',
            'title_variant' => 'Batch title',
        ])->assertRedirect();

        Mail::assertSent(ApprovalBatchReadyMail::class, function (ApprovalBatchReadyMail $mail) use ($creatorUser): bool {
            return $mail->hasTo($creatorUser->email) && $mail->pendingCount === 1;
        });
    }

    public function test_tiktok_thumbnail_service_stores_oembed_thumbnail(): void
    {
        Http::fake([
            'www.tiktok.com/oembed*' => Http::response([
                'thumbnail_url' => 'https://cdn.example/thumb.jpg',
            ]),
        ]);

        $entry = PublishLogEntry::factory()->create([
            'tiktok_url' => 'https://www.tiktok.com/@pilot/video/7123456789012345678',
            'tiktok_thumbnail_url' => null,
        ]);

        app(TikTokThumbnailService::class)->hydrateEntry($entry);

        $entry->refresh();

        $this->assertSame('https://cdn.example/thumb.jpg', $entry->tiktok_thumbnail_url);
    }

    public function test_tiktok_thumbnail_service_uses_placeholder_when_oembed_fails(): void
    {
        Http::fake([
            'www.tiktok.com/oembed*' => Http::response([], 400),
        ]);

        $entry = PublishLogEntry::factory()->create([
            'tiktok_url' => 'https://www.tiktok.com/@pilot/video/7123456789012345678',
            'tiktok_thumbnail_url' => null,
        ]);

        app(TikTokThumbnailService::class)->hydrateEntry($entry);

        $entry->refresh();

        $this->assertSame(url('/images/demo-video-thumb.svg'), $entry->tiktok_thumbnail_url);
    }

    public function test_operator_dashboard_includes_chart_sections(): void
    {
        $operator = User::factory()->operator()->create();
        $creator = Creator::factory()->create();

        PublishLogEntry::factory()->create([
            'creator_id' => $creator->id,
            'status' => PublishStatus::Published,
            'posted_time' => now()->subDay(),
        ]);

        PublishLogEntry::factory()->create([
            'creator_id' => $creator->id,
            'status' => PublishStatus::PendingApproval,
            'logged_on' => now()->toDateString(),
        ]);

        $response = $this->actingAs($operator)->get(route('operator.dashboard'));

        $response->assertOk();
        $response->assertSee('Publish velocity · 7 days', false);
        $response->assertSee('Pending queue · logged this week', false);
        $response->assertSee('Pending by creator', false);
    }

    public function test_creator_approvals_page_has_touch_friendly_actions(): void
    {
        $creatorUser = User::factory()->creator()->create();
        $creator = Creator::factory()->create(['user_id' => $creatorUser->id]);

        PublishLogEntry::factory()->create([
            'creator_id' => $creator->id,
            'status' => PublishStatus::PendingApproval,
            'tiktok_thumbnail_url' => 'https://cdn.example/thumb.jpg',
        ]);

        $response = $this->actingAs($creatorUser)->get(route('creator.approvals.index'));

        $response->assertOk();
        $response->assertSee('ops-approval-actions', false);
        $response->assertSee('ops-btn-touch', false);
        $response->assertSee('https://cdn.example/thumb.jpg', false);
    }
}
