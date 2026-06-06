<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\Playground;
use App\Models\Order;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlaygroundPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_guest_cannot_access_playground(): void
    {
        $this->get(Playground::getUrl())
            ->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_playground_in_local(): void
    {
        $this->actingAs($this->adminUser())
            ->get(Playground::getUrl())
            ->assertOk()
            ->assertSee('Environment')
            ->assertSee('Echo broadcast demo');
    }

    public function test_broadcast_action_warns_when_no_orders(): void
    {
        Livewire::actingAs($this->adminUser())
            ->test(Playground::class)
            ->callAction('broadcastTest')
            ->assertNotified('No orders to broadcast');
    }

    public function test_broadcast_action_dispatches_event_when_order_exists(): void
    {
        $order = Order::factory()->create();

        Livewire::actingAs($this->adminUser())
            ->test(Playground::class)
            ->callAction('broadcastTest')
            ->assertNotified('Broadcast sent');

        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }
}
