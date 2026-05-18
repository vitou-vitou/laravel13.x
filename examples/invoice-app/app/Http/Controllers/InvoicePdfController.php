<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfController extends Controller
{
    public function __invoke(Invoice $invoice)
    {
        $invoice->load('customer', 'items');

        return Pdf::loadView('invoices.pdf', ['invoice' => $invoice])
            ->download("{$invoice->number}.pdf");
    }
}
