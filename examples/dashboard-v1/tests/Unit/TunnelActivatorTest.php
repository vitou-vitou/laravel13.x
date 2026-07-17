<?php

namespace Tests\Unit;

use App\Models\Tunnel;
use App\Services\Tunnel\NgrokEnvSync;
use App\Services\Tunnel\NgrokTrafficPolicyWriter;
use App\Services\Tunnel\TunnelActivator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TunnelActivatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_activate_marks_only_one_tunnel_active(): void
    {
        $envPath = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($envPath, "NGROK_DEV_DOMAIN=\n");
        $policyPath = $this->samplePolicyPath();

        $first = Tunnel::factory()->create([
            'name' => 'First',
            'domain' => 'first.ngrok-free.dev',
            'herd_host' => 'dashboard-v1.test',
        ]);
        $second = Tunnel::factory()->create([
            'name' => 'Second',
            'domain' => 'second.ngrok-free.dev',
            'herd_host' => 'alt-dashboard.test',
        ]);

        $activator = $this->activator($envPath, $policyPath);
        $activator->activate($first);

        $this->assertTrue($first->fresh()->is_active);
        $this->assertFalse($second->fresh()->is_active);
        $this->assertStringContainsString('host: dashboard-v1.test', file_get_contents($policyPath));

        $activator->activate($second);

        $this->assertFalse($first->fresh()->is_active);
        $this->assertTrue($second->fresh()->is_active);
        $this->assertStringContainsString('NGROK_DEV_DOMAIN=second.ngrok-free.dev', file_get_contents($envPath));
        $this->assertStringContainsString('host: alt-dashboard.test', file_get_contents($policyPath));

        @unlink($envPath);
        @unlink($policyPath);
    }

    private function activator(string $envPath, string $policyPath): TunnelActivator
    {
        return new TunnelActivator(
            new NgrokEnvSync($envPath),
            new NgrokTrafficPolicyWriter($policyPath),
        );
    }

    private function samplePolicyPath(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'policy');
        file_put_contents($path, <<<'YAML'
# Herd routes by Host; ngrok must send dashboard-v1.test upstream.
on_http_request:
  - actions:
      - type: add-headers
        config:
          headers:
            host: dashboard-v1.test
YAML);

        return $path;
    }
}
