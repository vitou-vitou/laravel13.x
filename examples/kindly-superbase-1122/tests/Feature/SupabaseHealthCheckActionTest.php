<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SupabaseHealthCheckActionTest extends TestCase
{
    public function test_health_check_endpoint_returns_not_configured_json(): void
    {
        config([
            'supabase.url' => null,
            'supabase.anon_key' => null,
        ]);

        $response = $this->postJson(route('supabase.health-check'));

        $response->assertOk();
        $response->assertJsonPath('health.status', 'not_configured');
        $response->assertJsonPath('health.message', 'Set SUPABASE_URL and SUPABASE_ANON_KEY in .env');
        $response->assertJsonPath('health.endpoint', null);
        $response->assertJsonStructure([
            'health' => ['checked_at'],
            'logs' => ['summary', 'entries', 'showing', 'total', 'has_more', 'limit', 'offset'],
            'log',
        ]);
    }

    public function test_health_check_endpoint_returns_healthy_json(): void
    {
        config([
            'supabase.url' => 'https://example.supabase.co',
            'supabase.anon_key' => 'test-anon-key',
        ]);

        Http::fake([
            'example.supabase.co/auth/v1/health' => Http::response([
                'name' => 'GoTrue',
                'version' => 'v2.999.0',
            ], 200),
        ]);

        $response = $this->postJson(route('supabase.health-check'));

        $response->assertOk();
        $response->assertJsonPath('health.status', 'healthy');
        $response->assertJsonPath('health.message', 'Supabase auth service is reachable.');
        $response->assertJsonPath('health.endpoint', 'https://example.supabase.co/auth/v1/health');
        $response->assertJsonPath('health.http_status', 200);
        $response->assertJsonPath('health.details.name', 'GoTrue');
        $response->assertJsonPath('log.trigger', 'test_connection');
    }

    public function test_welcome_page_shows_test_connection_button_and_loading_hooks(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-supabase-health-check', false);
        $response->assertSee('data-supabase-loading', false);
        $response->assertSee('data-supabase-tab-loading', false);
        $response->assertSee('Test connection', false);
        $response->assertSee(route('supabase.health-check'), false);
    }
}
