<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_guest_cannot_access_orders_index(): void
    {
        $this->get(OrderResource::getUrl('index'))
            ->assertRedirect('/admin/login');
    }

    public function test_authenticated_user_can_list_orders(): void
    {
        $user = $this->adminUser();
        $customer = Customer::factory()->create(['name' => 'Filament Customer']);

        Order::factory()->forCustomer($customer)->create();

        $this->actingAs($user)
            ->get(OrderResource::getUrl('index'))
            ->assertOk()
            ->assertSee('Filament Customer');
    }

    public function test_authenticated_user_can_create_order(): void
    {
        $user = $this->adminUser();
        $customer = Customer::factory()->create(['name' => 'New Admin Order']);

        Livewire::actingAs($user)
            ->test(CreateOrder::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'amount_cents' => 25_000,
                'status' => 'paid',
                'ordered_at' => now()->toDateTimeString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'amount_cents' => 25_000,
            'status' => 'paid',
        ]);
    }

    public function test_authenticated_user_can_update_order(): void
    {
        $user = $this->adminUser();
        $before = Customer::factory()->create(['name' => 'Before Edit']);
        $after = Customer::factory()->create(['name' => 'After Edit']);
        $order = Order::factory()->forCustomer($before)->create();

        Livewire::actingAs($user)
            ->test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->fillForm([
                'customer_id' => $after->id,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'customer_id' => $after->id,
        ]);
    }

    public function test_authenticated_user_can_delete_order(): void
    {
        $user = $this->adminUser();

        $order = Order::factory()->create();

        Livewire::actingAs($user)
            ->test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->callAction('delete');

        $this->assertDatabaseMissing('orders', [
            'id' => $order->id,
        ]);
    }
}
