<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Services\ActivityLogBoard;
use Tests\TestCase;

class ActivityLogBoardTest extends TestCase
{
    public function test_snapshot_limits_entries_to_five_by_default(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            ActivityLog::factory()->create([
                'message' => "Log entry {$i}",
            ]);
        }

        $snapshot = app(ActivityLogBoard::class)->snapshot();

        $this->assertCount(5, $snapshot['entries']);
        $this->assertSame(8, $snapshot['total']);
        $this->assertTrue($snapshot['has_more']);
        $this->assertSame('Log entry 8', $snapshot['entries']->first()->message);
        $this->assertSame('Log entry 4', $snapshot['entries']->last()->message);
    }

    public function test_snapshot_summary_reflects_all_logs_not_only_visible_entries(): void
    {
        ActivityLog::factory()->count(3)->processing()->create();
        ActivityLog::factory()->count(2)->failed()->create();

        $snapshot = app(ActivityLogBoard::class)->snapshot();

        $this->assertSame(3, $snapshot['summary'][ActivityLog::STATUS_PROCESSING]);
        $this->assertSame(2, $snapshot['summary'][ActivityLog::STATUS_FAILED]);
        $this->assertCount(5, $snapshot['entries']);
    }

    public function test_snapshot_filters_entries_by_status(): void
    {
        ActivityLog::factory()->count(2)->processing()->create();
        ActivityLog::factory()->count(3)->failed()->create([
            'message' => 'Failed sync',
        ]);

        $snapshot = app(ActivityLogBoard::class)->snapshot(
            ActivityLogBoard::DEFAULT_LIMIT,
            0,
            ActivityLog::STATUS_FAILED,
        );

        $this->assertSame(3, $snapshot['total']);
        $this->assertCount(3, $snapshot['entries']);
        $this->assertTrue($snapshot['entries']->every(
            fn (ActivityLog $entry) => $entry->status === ActivityLog::STATUS_FAILED
        ));
        $this->assertSame(2, $snapshot['summary'][ActivityLog::STATUS_PROCESSING]);
        $this->assertSame(3, $snapshot['summary'][ActivityLog::STATUS_FAILED]);
        $this->assertSame(ActivityLog::STATUS_FAILED, $snapshot['status']);
    }

    public function test_preview_message_truncates_with_ellipsis(): void
    {
        $board = app(ActivityLogBoard::class);
        $longMessage = str_repeat('Supabase sync detail. ', 20);

        $this->assertTrue($board->shouldTruncateMessage($longMessage));
        $this->assertStringEndsWith('…', $board->previewMessage($longMessage));
        $this->assertLessThanOrEqual(ActivityLogBoard::MESSAGE_PREVIEW_LENGTH + 3, mb_strlen($board->previewMessage($longMessage)));
    }

    public function test_home_truncates_long_log_messages_with_expand_control(): void
    {
        $longMessage = str_repeat('Supabase sync detail. ', 20);

        ActivityLog::factory()->create([
            'message' => $longMessage,
        ]);

        config(['supabase.url' => null, 'supabase.anon_key' => null]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-log-message-toggle', false);
        $response->assertSee('data-log-message-full', false);
        $response->assertSee('Show more', false);
        $response->assertSee(app(ActivityLogBoard::class)->previewMessage($longMessage), false);
    }
}
