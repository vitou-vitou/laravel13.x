<?php

namespace Tests\Feature;

use App\Models\Tunnel;
use Database\Seeders\TunnelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TunnelSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_five_profiles_with_first_active(): void
    {
        config(['tunnel.enabled' => true]);

        $this->seed(TunnelSeeder::class);

        $this->assertSame(5, Tunnel::query()->count());

        $default = Tunnel::defaultTemplate();

        $this->assertNotNull($default);
        $this->assertTrue($default->is_active);
        $this->assertSame('Default — local SSO', $default->name);
        $this->assertSame(1, Tunnel::query()->where('is_active', true)->count());
    }

    public function test_seeder_is_idempotent_by_name(): void
    {
        config(['tunnel.enabled' => true]);

        $this->seed(TunnelSeeder::class);
        $this->seed(TunnelSeeder::class);

        $this->assertSame(5, Tunnel::query()->count());
    }
}
