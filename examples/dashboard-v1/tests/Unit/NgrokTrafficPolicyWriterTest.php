<?php

namespace Tests\Unit;

use App\Services\Tunnel\NgrokTrafficPolicyWriter;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class NgrokTrafficPolicyWriterTest extends TestCase
{
    public function test_sync_updates_host_header_in_policy_file(): void
    {
        $path = $this->samplePolicyPath();

        (new NgrokTrafficPolicyWriter($path))->sync('other-app.test');

        $contents = file_get_contents($path);

        $this->assertStringContainsString('host: other-app.test', $contents);
        $this->assertStringContainsString('# Herd routes by Host; ngrok must send other-app.test upstream.', $contents);

        @unlink($path);
    }

    public function test_sync_rejects_ngrok_domain_as_herd_host(): void
    {
        $path = $this->samplePolicyPath();
        $writer = new NgrokTrafficPolicyWriter($path);

        $this->expectException(ValidationException::class);
        $writer->sync('evil.ngrok-free.dev');

        @unlink($path);
    }

    private function samplePolicyPath(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'policy');
        $this->assertNotFalse($path);

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
