<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SupabaseHealthCheckTest extends TestCase
{
    public function test_supabase_tab_starts_idle_until_health_check_runs(): void
    {
        config([
            'supabase.url' => null,
            'supabase.anon_key' => null,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-supabase-status="idle"', false);
        $response->assertSee('data-supabase-health', false);
        $response->assertSee('>Health<', false);
        $response->assertSee('Open the supabase tab or click Test connection to run a health check.', false);
    }

    public function test_reports_not_configured_when_env_is_missing(): void
    {
        config([
            'supabase.url' => null,
            'supabase.anon_key' => null,
        ]);

        $response = $this->postJson(route('supabase.health-check'));

        $response->assertOk();
        $response->assertJsonPath('health.status', 'not_configured');
        $response->assertJsonPath('health.message', 'Set SUPABASE_URL and SUPABASE_ANON_KEY in .env');
        $response->assertJsonStructure([
            'health' => ['transaction_id'],
            'logs' => ['summary', 'entries', 'showing', 'total', 'has_more', 'limit', 'offset'],
            'log' => ['transaction_id'],
        ]);
    }

    public function test_reports_healthy_when_supabase_auth_health_succeeds(): void
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
        $response->assertJsonPath('health.endpoint', 'https://example.supabase.co/auth/v1/health');
        $response->assertJsonPath('health.http_status', 200);
        $response->assertJsonPath('health.details.name', 'GoTrue');
    }

    public function test_reports_unhealthy_when_supabase_auth_health_fails(): void
    {
        config([
            'supabase.url' => 'https://example.supabase.co',
            'supabase.anon_key' => 'test-anon-key',
        ]);

        Http::fake([
            'example.supabase.co/auth/v1/health' => Http::response([], 503),
        ]);

        $response = $this->postJson(route('supabase.health-check'));

        $response->assertOk();
        $response->assertJsonPath('health.status', 'unhealthy');
        $response->assertJsonPath('health.message', 'Supabase returned HTTP 503.');
    }
}
