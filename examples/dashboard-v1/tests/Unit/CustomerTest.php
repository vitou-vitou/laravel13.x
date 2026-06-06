<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_has_many_orders(): void
    {
        $customer = Customer::factory()->create(['name' => 'Acme Corp']);

        Order::factory()->count(2)->forCustomer($customer)->create();

        $this->assertCount(2, $customer->orders);
        $this->assertTrue($customer->orders->every(fn (Order $order) => $order->customer_id === $customer->id));
    }

    public function test_order_belongs_to_customer(): void
    {
        $customer = Customer::factory()->create(['name' => 'Jordan Lee']);
        $order = Order::factory()->forCustomer($customer)->create();

        $this->assertTrue($order->customer->is($customer));
        $this->assertSame('Jordan Lee', $order->customer->name);
    }
}
