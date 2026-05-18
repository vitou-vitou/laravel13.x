<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_invoice_with_items(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post('/invoices', [
                'customer_id' => $customer->id,
                'number' => 'INV-001',
                'issued_on' => '2026-01-01',
                'due_on' => '2026-01-31',
                'status' => 'draft',
                'items' => [
                    ['description' => 'Service A', 'quantity' => 2, 'unit_price' => 100.00],
                    ['description' => 'Service B', 'quantity' => 1, 'unit_price' => 50.00],
                ],
            ])
            ->assertRedirect('/invoices');

        $this->assertDatabaseHas('invoices', ['number' => 'INV-001']);
        $this->assertDatabaseCount('invoice_items', 2);
    }

    public function test_user_can_view_invoice_with_total(): void
    {
        $invoice = Invoice::factory()
            ->hasItems(2, ['quantity' => 1, 'unit_price' => 50])
            ->create();

        $this->actingAs(User::factory()->create())
            ->get("/invoices/{$invoice->id}")
            ->assertOk()
            ->assertSee($invoice->number)
            ->assertSee('100.00');
    }
}
