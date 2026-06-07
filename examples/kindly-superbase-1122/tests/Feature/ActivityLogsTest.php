<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use Tests\TestCase;

class ActivityLogsTest extends TestCase
{
    public function test_activity_logs_endpoint_returns_first_page_with_metadata(): void
    {
        ActivityLog::factory()->count(8)->create();

        $response = $this->getJson(route('activity-logs.index', ['offset' => 0, 'limit' => 5]));

        $response->assertOk();
        $response->assertJsonCount(5, 'entries');
        $response->assertJsonPath('total', 8);
        $response->assertJsonPath('has_more', true);
        $response->assertJsonPath('offset', 0);
        $response->assertJsonPath('limit', 5);
        $response->assertJsonStructure([
            'summary' => ActivityLog::STATUSES,
            'entries' => [
                ['id', 'message', 'status', 'created_at_human'],
            ],
            'total',
            'has_more',
            'offset',
            'limit',
        ]);
    }

    public function test_activity_logs_endpoint_returns_next_page(): void
    {
        ActivityLog::factory()->count(8)->create();

        $response = $this->getJson(route('activity-logs.index', ['offset' => 5, 'limit' => 10]));

        $response->assertOk();
        $response->assertJsonCount(3, 'entries');
        $response->assertJsonPath('total', 8);
        $response->assertJsonPath('has_more', false);
        $response->assertJsonPath('offset', 5);
    }

    public function test_activity_logs_endpoint_filters_by_status(): void
    {
        ActivityLog::factory()->count(2)->processing()->create();
        ActivityLog::factory()->count(3)->failed()->create();

        $response = $this->getJson(route('activity-logs.index', [
            'status' => ActivityLog::STATUS_FAILED,
            'limit' => 5,
        ]));

        $response->assertOk();
        $response->assertJsonCount(3, 'entries');
        $response->assertJsonPath('total', 3);
        $response->assertJsonPath('status', ActivityLog::STATUS_FAILED);
        $response->assertJsonPath('summary.'.ActivityLog::STATUS_PROCESSING, 2);
        $response->assertJsonPath('summary.'.ActivityLog::STATUS_FAILED, 3);

        foreach ($response->json('entries') as $entry) {
            $this->assertSame(ActivityLog::STATUS_FAILED, $entry['status']);
        }
    }

    public function test_logs_tab_renders_status_filter_controls(): void
    {
        config(['supabase.url' => null, 'supabase.anon_key' => null]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-log-status-filter', false);
        $response->assertSee('data-log-status-filter-value', false);
        $response->assertSee('Filter by status', false);
        $response->assertSee('All', false);
        $response->assertSee('pending', false);
        $response->assertSee('processing', false);
        $response->assertSee('completed', false);
        $response->assertSee('failed', false);
    }

    public function test_logs_tab_shows_five_entries_per_status_and_load_more_when_more_exist(): void
    {
        foreach (range(1, 7) as $number) {
            ActivityLog::factory()->pending()->create([
                'message' => "Log entry {$number}",
                'context' => ['source' => 'supabase'],
            ]);
        }

        config(['supabase.url' => null, 'supabase.anon_key' => null]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-log-show-more', false);
        $response->assertSee('Load more', false);
        $response->assertSee('Log entry 7', false);
        $response->assertSee('Log entry 3', false);
        $response->assertDontSee('Log entry 2', false);
        $response->assertDontSee('Log entry 1', false);
    }

    public function test_logs_tab_paginates_each_status_independently(): void
    {
        ActivityLog::factory()->count(7)->pending()->create(['message' => 'Pending entry']);
        ActivityLog::factory()->count(2)->failed()->create(['message' => 'Failed entry']);

        config(['supabase.url' => null, 'supabase.anon_key' => null]);

        $response = $this->get('/');

        $response->assertOk();
        // Pending overflows (7 > 5) so its group gets a Load more control...
        $response->assertSee('data-log-status="pending"', false);
        // ...failed (2 <= 5) does not.
        $response->assertDontSee('data-log-status="failed"', false);
    }

    public function test_logs_tab_hides_load_more_when_five_or_fewer_entries(): void
    {
        ActivityLog::factory()->count(3)->pending()->create([
            'message' => 'Short list entry',
            'context' => ['source' => 'supabase'],
        ]);

        config(['supabase.url' => null, 'supabase.anon_key' => null]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('data-log-show-more', false);
        $response->assertDontSee('Load more', false);
    }

    public function test_grouped_endpoint_caps_entries_per_status_with_per_group_metadata(): void
    {
        ActivityLog::factory()->count(7)->pending()->create();
        ActivityLog::factory()->count(2)->failed()->create();

        $response = $this->getJson(route('activity-logs.index', ['grouped' => 1, 'limit' => 5]));

        $response->assertOk();
        $response->assertJsonPath('grouped', true);

        $groups = collect($response->json('groups'))->keyBy('status');

        $this->assertCount(5, $groups['pending']['entries']);
        $this->assertSame(7, $groups['pending']['total']);
        $this->assertTrue($groups['pending']['has_more']);

        $this->assertCount(2, $groups['failed']['entries']);
        $this->assertSame(2, $groups['failed']['total']);
        $this->assertFalse($groups['failed']['has_more']);
    }

    public function test_health_check_logs_include_total_and_has_more(): void
    {
        ActivityLog::factory()->count(6)->create();

        config([
            'supabase.url' => null,
            'supabase.anon_key' => null,
        ]);

        $response = $this->postJson(route('supabase.health-check'));

        $response->assertOk();
        $response->assertJsonPath('logs.total', 7);
        $response->assertJsonPath('logs.has_more', true);
        $response->assertJsonCount(5, 'logs.entries');
    }
}
