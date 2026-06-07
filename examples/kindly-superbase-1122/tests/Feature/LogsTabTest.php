<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use Tests\TestCase;

class LogsTabTest extends TestCase
{
    public function test_logs_tab_lists_manual_entries(): void
    {
        ActivityLog::factory()->processing()->create([
            'action' => 'sync_profiles',
            'message' => 'Sync in progress',
            'context' => ['source' => 'supabase'],
        ]);
        ActivityLog::factory()->failed()->create([
            'action' => 'webhook_delivery',
            'message' => 'Import failed',
            'context' => ['source' => 'supabase'],
        ]);

        config(['supabase.url' => null, 'supabase.anon_key' => null]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('logs', false);
        $response->assertSee('Activity logs', false);
        $response->assertSee('Showing 2 most recent', false);
        $response->assertSee('data-log-showing', false);
        $response->assertSee('data-log-summary', false);
        $response->assertSee('Sync in progress', false);
        $response->assertSee('Import failed', false);
        $response->assertSee('supabase · sync profiles', false);
    }

    public function test_logs_tab_shows_five_most_recent_when_more_exist(): void
    {
        for ($index = 1; $index <= 6; $index++) {
            ActivityLog::factory()->create([
                'action' => 'sync_profiles',
                'message' => "Log entry {$index}",
            ]);
        }

        config(['supabase.url' => null, 'supabase.anon_key' => null]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Showing 5 of 6 most recent', false);
        $response->assertSee('Log entry 6', false);
        $response->assertSee('Log entry 2', false);
        $response->assertDontSee('Log entry 1', false);
    }
}
