<?php

namespace Tests\Unit;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_be_created_with_required_fields(): void
    {
        $customer = Customer::create([
            'name' => 'Acme Corp',
            'email' => 'billing@acme.test',
            'address' => '1 Way',
        ]);

        $this->assertDatabaseHas('customers', ['email' => 'billing@acme.test']);
        $this->assertSame('Acme Corp', $customer->name);
    }
}
