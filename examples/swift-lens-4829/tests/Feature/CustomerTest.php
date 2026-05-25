<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_list_customers(): void
    {
        $this->get('/customers')->assertRedirect('/login');
    }

    public function test_user_can_create_customer(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/customers', [
                'name' => 'Acme',
                'email' => 'a@a.test',
                'address' => '1 St',
            ])
            ->assertRedirect('/customers');

        $this->assertDatabaseHas('customers', ['email' => 'a@a.test']);
    }

    public function test_user_can_update_customer(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs(User::factory()->create())
            ->put("/customers/{$customer->id}", [
                'name' => 'Updated',
                'email' => $customer->email,
                'address' => $customer->address,
            ])
            ->assertRedirect('/customers');

        $this->assertSame('Updated', $customer->fresh()->name);
    }

    public function test_user_can_delete_customer(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete("/customers/{$customer->id}")
            ->assertRedirect('/customers');

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}
