<?php

namespace Tests\Unit;

use App\Models\Tunnel;
use App\Services\Tunnel\TunnelHealthChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TunnelHealthCheckerTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_returns_ok_on_http_200(): void
    {
        Http::fake([
            'https://healthy.ngrok-free.dev/login' => Http::response('ok', 200),
        ]);

        $tunnel = Tunnel::factory()->make(['domain' => 'healthy.ngrok-free.dev']);
        $result = (new TunnelHealthChecker)->check($tunnel);

        $this->assertSame('ok', $result['status']);
        $this->assertSame(200, $result['http_code']);
    }

    public function test_check_returns_error_on_http_400(): void
    {
        Http::fake([
            'https://broken.ngrok-free.dev/login' => Http::response('bad', 400),
        ]);

        $tunnel = Tunnel::factory()->make(['domain' => 'broken.ngrok-free.dev']);
        $result = (new TunnelHealthChecker)->check($tunnel);

        $this->assertSame('error', $result['status']);
        $this->assertSame(400, $result['http_code']);
    }

    public function test_verify_and_store_persists_status(): void
    {
        Http::fake([
            'https://stored.ngrok-free.dev/login' => Http::response('ok', 200),
        ]);

        $tunnel = Tunnel::factory()->create(['domain' => 'stored.ngrok-free.dev']);

        (new TunnelHealthChecker)->verifyAndStore($tunnel);

        $tunnel->refresh();
        $this->assertSame('ok', $tunnel->last_verified_status);
        $this->assertNotNull($tunnel->last_verified_at);
    }
}
