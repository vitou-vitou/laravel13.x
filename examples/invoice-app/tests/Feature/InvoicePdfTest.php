<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_download_invoice_pdf(): void
    {
        $invoice = Invoice::factory()->hasItems(1)->create();

        $response = $this->actingAs(User::factory()->create())
            ->get(route('invoices.pdf', $invoice));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }
}
