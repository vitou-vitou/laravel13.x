<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\RelationManagers\OrdersRelationManager;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_guest_cannot_access_customers_index(): void
    {
        $this->get(CustomerResource::getUrl('index'))
            ->assertRedirect('/admin/login');
    }

    public function test_authenticated_user_can_list_customers(): void
    {
        $user = $this->adminUser();

        Customer::factory()->create(['name' => 'Filament Customer']);

        $this->actingAs($user)
            ->get(CustomerResource::getUrl('index'))
            ->assertOk()
            ->assertSee('Filament Customer');
    }

    public function test_authenticated_user_can_create_customer(): void
    {
        $user = $this->adminUser();

        Livewire::actingAs($user)
            ->test(CreateCustomer::class)
            ->fillForm([
                'name' => 'New Customer',
                'email' => 'new@example.com',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('customers', [
            'name' => 'New Customer',
            'email' => 'new@example.com',
        ]);
    }

    public function test_authenticated_user_can_update_customer(): void
    {
        $user = $this->adminUser();
        $customer = Customer::factory()->create(['name' => 'Before Edit']);

        Livewire::actingAs($user)
            ->test(EditCustomer::class, ['record' => $customer->getRouteKey()])
            ->fillForm([
                'name' => 'After Edit',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'After Edit',
        ]);
    }

    public function test_authenticated_user_can_delete_customer_without_orders(): void
    {
        $user = $this->adminUser();
        $customer = Customer::factory()->create();

        Livewire::actingAs($user)
            ->test(EditCustomer::class, ['record' => $customer->getRouteKey()])
            ->callAction('delete');

        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_customer_edit_shows_orders_relation_manager(): void
    {
        $user = $this->adminUser();
        $customer = Customer::factory()->create();
        $order = Order::factory()->forCustomer($customer)->create([
            'amount_cents' => 4_200,
            'status' => 'paid',
        ]);

        Livewire::actingAs($user)
            ->test(OrdersRelationManager::class, [
                'ownerRecord' => $customer,
                'pageClass' => EditCustomer::class,
            ])
            ->assertCanSeeTableRecords([$order]);
    }

    public function test_customer_orders_relation_manager_can_create_order(): void
    {
        $user = $this->adminUser();
        $customer = Customer::factory()->create();

        Livewire::actingAs($user)
            ->test(OrdersRelationManager::class, [
                'ownerRecord' => $customer,
                'pageClass' => EditCustomer::class,
            ])
            ->callTableAction('create', data: [
                'amount_cents' => 3_500,
                'status' => 'pending',
                'ordered_at' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'amount_cents' => 3_500,
            'status' => 'pending',
        ]);
    }
}
