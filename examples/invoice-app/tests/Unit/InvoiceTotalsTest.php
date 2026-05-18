<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTotalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_total_sums_line_items(): void
    {
        $customer = Customer::factory()->create();
        $invoice = Invoice::factory()->for($customer)->create();

        InvoiceItem::factory()->for($invoice)->create(['quantity' => 2, 'unit_price' => 50.00]);
        InvoiceItem::factory()->for($invoice)->create(['quantity' => 1, 'unit_price' => 25.50]);

        $this->assertEqualsWithDelta(125.50, $invoice->fresh()->total, 0.001);
    }

    public function test_line_total_is_quantity_times_unit_price(): void
    {
        $item = new InvoiceItem(['quantity' => 3, 'unit_price' => 10.00]);
        $this->assertEqualsWithDelta(30.00, $item->line_total, 0.001);
    }
}
