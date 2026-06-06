<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadcastOrdersChannelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-key',
            'broadcasting.connections.reverb.secret' => 'test-secret',
            'broadcasting.connections.reverb.app_id' => 'test-app',
            'broadcasting.connections.reverb.options.host' => 'localhost',
            'broadcasting.connections.reverb.options.port' => 8080,
            'broadcasting.connections.reverb.options.scheme' => 'http',
            'broadcasting.connections.reverb.options.useTLS' => false,
        ]);
    }

    public function test_admin_has_broadcast_access_permission(): void
    {
        $admin = $this->adminUser();

        $this->assertTrue($admin->can('access_admin'));
    }

    public function test_customer_cannot_authorize_orders_channel(): void
    {
        $this->seedRoles();
        $user = User::factory()->create();
        $user->assignRole('customer');

        $this->actingAs($user)
            ->postJson('/broadcasting/auth', [
                'channel_name' => 'private-orders',
                'socket_id' => '1.1',
            ])
            ->assertForbidden();
    }
}
