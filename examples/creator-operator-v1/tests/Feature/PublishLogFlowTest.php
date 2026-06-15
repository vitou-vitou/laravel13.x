<?php

namespace Tests\Feature;

use App\Enums\PublishStatus;
use App\Enums\UserRole;
use App\Models\Creator;
use App\Models\PublishLogEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishLogFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_add_pending_publish_row(): void
    {
        $operator = User::factory()->operator()->create();
        $creator = Creator::factory()->create();

        $response = $this->actingAs($operator)->post(route('operator.creators.publish-log.store', $creator), [
            'logged_on' => '2026-06-14',
            'tiktok_url' => 'https://www.tiktok.com/@pilot/video/7123456789012345678',
            'title_variant' => 'Hook title for Shorts',
            'notes' => 'Packaged for approval',
        ]);

        $response->assertRedirect(route('operator.creators.show', $creator));

        $this->assertDatabaseHas('publish_log_entries', [
            'creator_id' => $creator->id,
            'status' => PublishStatus::PendingApproval->value,
            'title_variant' => 'Hook title for Shorts',
        ]);
    }

    public function test_creator_can_approve_pending_row(): void
    {
        $creatorUser = User::factory()->creator()->create();
        $creator = Creator::factory()->create(['user_id' => $creatorUser->id]);
        $entry = PublishLogEntry::factory()->create([
            'creator_id' => $creator->id,
            'status' => PublishStatus::PendingApproval,
        ]);

        $response = $this->actingAs($creatorUser)->post(route('creator.approvals.approve', $entry));

        $response->assertRedirect();
        $entry->refresh();

        $this->assertSame(PublishStatus::Approved, $entry->status);
        $this->assertSame($creatorUser->id, $entry->approved_by_user_id);
    }

    public function test_creator_cannot_approve_another_creators_row(): void
    {
        $creatorUser = User::factory()->creator()->create();
        Creator::factory()->create(['user_id' => $creatorUser->id]);

        $otherEntry = PublishLogEntry::factory()->create([
            'status' => PublishStatus::PendingApproval,
        ]);

        $this->actingAs($creatorUser)
            ->post(route('creator.approvals.approve', $otherEntry))
            ->assertForbidden();
    }

    public function test_operator_can_mark_approved_row_published(): void
    {
        $operator = User::factory()->operator()->create();
        $creator = Creator::factory()->create();
        $entry = PublishLogEntry::factory()->approved()->create([
            'creator_id' => $creator->id,
        ]);

        $response = $this->actingAs($operator)->post(route('operator.creators.publish-log.publish', [$creator, $entry]), [
            'yt_url' => 'https://youtube.com/shorts/live123',
            'ig_url' => 'https://instagram.com/reel/live123',
        ]);

        $response->assertRedirect(route('operator.creators.show', $creator));

        $entry->refresh();
        $creator->refresh();

        $this->assertSame(PublishStatus::Published, $entry->status);
        $this->assertNotNull($creator->last_run_date);
    }

    public function test_creator_is_redirected_to_approvals_from_dashboard(): void
    {
        $creatorUser = User::factory()->creator()->create();
        Creator::factory()->create(['user_id' => $creatorUser->id]);

        $this->actingAs($creatorUser)
            ->get(route('dashboard'))
            ->assertRedirect(route('creator.approvals.index'));
    }

    public function test_operator_is_redirected_to_operator_dashboard(): void
    {
        $operator = User::factory()->operator()->create();

        $this->actingAs($operator)
            ->get(route('dashboard'))
            ->assertRedirect(route('operator.dashboard'));
    }

    public function test_creator_without_profile_gets_403_on_approvals(): void
    {
        $creatorUser = User::factory()->creator()->create();

        $this->actingAs($creatorUser)
            ->get(route('creator.approvals.index'))
            ->assertForbidden();
    }
}
