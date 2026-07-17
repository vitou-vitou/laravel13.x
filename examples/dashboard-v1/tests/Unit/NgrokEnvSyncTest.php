<?php

namespace Tests\Unit;

use App\Models\Tunnel;
use App\Services\Tunnel\EnvFileWriter;
use App\Services\Tunnel\NgrokEnvSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class NgrokEnvSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_updates_env_file_keys(): void
    {
        $envPath = tempnam(sys_get_temp_dir(), 'env');
        $this->assertNotFalse($envPath);

        file_put_contents($envPath, "APP_NAME=Laravel\nGOOGLE_REDIRECT_URI=http://old.test/callback\n");

        $sync = new NgrokEnvSync($envPath);
        $result = $sync->sync('abc123.ngrok-free.dev');

        $contents = file_get_contents($envPath);

        $this->assertStringContainsString('NGROK_DEV_DOMAIN=abc123.ngrok-free.dev', $contents);
        $this->assertStringContainsString('GOOGLE_REDIRECT_URI=https://abc123.ngrok-free.dev/auth/google/callback', $contents);
        $this->assertStringContainsString('MICROSOFT_REDIRECT_URI=https://abc123.ngrok-free.dev/auth/microsoft/callback', $contents);
        $this->assertStringContainsString('GITHUB_REDIRECT_URI=https://abc123.ngrok-free.dev/auth/github/callback', $contents);
        $this->assertSame('abc123.ngrok-free.dev', $result['domain']);
        $this->assertSame('https://abc123.ngrok-free.dev', $result['base_url']);

        @unlink($envPath);
    }

    public function test_sync_rejects_local_domain(): void
    {
        $envPath = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($envPath, "APP_NAME=Laravel\n");

        $sync = new NgrokEnvSync($envPath);

        $this->expectException(ValidationException::class);
        $sync->sync('dashboard-v1.test');

        @unlink($envPath);
    }

    public function test_normalize_domain_strips_scheme(): void
    {
        $this->assertSame('foo.ngrok-free.dev', Tunnel::normalizeDomain('https://foo.ngrok-free.dev/login'));
    }

    public function test_env_file_writer_appends_missing_key(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($path, "EXISTING=1\n");

        (new EnvFileWriter($path))->set('NEW_KEY', 'value');

        $this->assertStringContainsString('NEW_KEY=value', file_get_contents($path));

        @unlink($path);
    }
}
