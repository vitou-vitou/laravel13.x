<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Services\SupabaseActionLogger;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SupabaseActionLoggerTest extends TestCase
{
    public function test_health_check_logs_pending_when_not_configured(): void
    {
        config([
            'supabase.url' => null,
            'supabase.anon_key' => null,
        ]);

        $this->postJson(route('supabase.health-check'), [
            'trigger' => SupabaseActionLogger::TRIGGER_TEST_CONNECTION,
        ])->assertOk();

        $this->assertDatabaseHas('activity_logs', [
            'action' => SupabaseActionLogger::ACTION_HEALTH_CHECK,
            'status' => ActivityLog::STATUS_PENDING,
            'message' => 'Supabase health check: Set SUPABASE_URL and SUPABASE_ANON_KEY in .env',
        ]);
    }

    public function test_health_check_logs_completed_when_healthy(): void
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

        $this->postJson(route('supabase.health-check'), [
            'trigger' => SupabaseActionLogger::TRIGGER_TAB_OPEN,
        ])->assertOk();

        $log = ActivityLog::query()->where('action', SupabaseActionLogger::ACTION_HEALTH_CHECK)->first();

        $this->assertNotNull($log);
        $this->assertSame(ActivityLog::STATUS_COMPLETED, $log->status);
        $this->assertSame('Supabase health check: Supabase auth service is reachable.', $log->message);
        $this->assertSame('supabase', $log->context['source'] ?? null);
        $this->assertSame('healthy', $log->context['result'] ?? null);
        $this->assertSame(SupabaseActionLogger::TRIGGER_TAB_OPEN, $log->context['trigger'] ?? null);
        $this->assertSame('GoTrue', $log->context['details']['name'] ?? null);
    }

    public function test_health_check_logs_failed_when_unhealthy(): void
    {
        config([
            'supabase.url' => 'https://example.supabase.co',
            'supabase.anon_key' => 'test-anon-key',
        ]);

        Http::fake([
            'example.supabase.co/auth/v1/health' => Http::response([], 503),
        ]);

        $this->postJson(route('supabase.health-check'))->assertOk();

        $this->assertDatabaseHas('activity_logs', [
            'action' => SupabaseActionLogger::ACTION_HEALTH_CHECK,
            'status' => ActivityLog::STATUS_FAILED,
            'message' => 'Supabase health check: Supabase returned HTTP 503.',
        ]);
    }

    public function test_each_supabase_action_creates_a_unique_log_entry(): void
    {
        config([
            'supabase.url' => null,
            'supabase.anon_key' => null,
        ]);

        $this->postJson(route('supabase.health-check'), [
            'trigger' => SupabaseActionLogger::TRIGGER_TAB_OPEN,
        ])->assertOk();

        $this->postJson(route('supabase.health-check'), [
            'trigger' => SupabaseActionLogger::TRIGGER_TEST_CONNECTION,
        ])->assertOk();

        $this->assertSame(2, ActivityLog::query()->where('action', SupabaseActionLogger::ACTION_HEALTH_CHECK)->count());
    }

    public function test_health_check_log_appears_on_logs_tab(): void
    {
        config([
            'supabase.url' => null,
            'supabase.anon_key' => null,
        ]);

        $this->postJson(route('supabase.health-check'), [
            'trigger' => SupabaseActionLogger::TRIGGER_TAB_OPEN,
        ])->assertOk();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Supabase health check: Set SUPABASE_URL and SUPABASE_ANON_KEY in .env', false);
        $response->assertSee('supabase · health check', false);
        $response->assertSee('tab open', false);
    }

    public function test_health_check_assigns_transaction_id_to_log_and_api_payload(): void
    {
        config([
            'supabase.url' => null,
            'supabase.anon_key' => null,
        ]);

        $response = $this->postJson(route('supabase.health-check'), [
            'trigger' => SupabaseActionLogger::TRIGGER_TEST_CONNECTION,
        ]);

        $response->assertOk();
        $transactionId = $response->json('log.transaction_id');
        $this->assertIsString($transactionId);
        $this->assertMatchesRegularExpression(
            '/^sup_[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $transactionId,
        );
        $response->assertJsonPath('health.transaction_id', $transactionId);

        $log = ActivityLog::query()->where('transaction_id', $transactionId)->first();
        $this->assertNotNull($log);
        $this->assertSame($transactionId, $log->context['transaction_id'] ?? null);
    }

    public function test_each_health_check_gets_a_unique_transaction_id(): void
    {
        config([
            'supabase.url' => null,
            'supabase.anon_key' => null,
        ]);

        $first = $this->postJson(route('supabase.health-check'), [
            'trigger' => SupabaseActionLogger::TRIGGER_TAB_OPEN,
        ])->assertOk()->json('log.transaction_id');

        $second = $this->postJson(route('supabase.health-check'), [
            'trigger' => SupabaseActionLogger::TRIGGER_TEST_CONNECTION,
        ])->assertOk()->json('log.transaction_id');

        $this->assertIsString($first);
        $this->assertIsString($second);
        $this->assertNotSame($first, $second);
    }
}
