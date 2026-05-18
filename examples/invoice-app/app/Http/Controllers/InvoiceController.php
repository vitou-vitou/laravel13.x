<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index()
    {
        return view('invoices.index', [
            'invoices' => Invoice::with('customer')->latest()->paginate(20),
        ]);
    }

    public function create()
    {
        return view('invoices.create', ['customers' => Customer::orderBy('name')->get()]);
    }

    public function store(StoreInvoiceRequest $request)
    {
        DB::transaction(function () use ($request) {
            $invoice = Invoice::create($request->safe()->except('items'));
            $invoice->items()->createMany($request->validated('items'));
        });

        return redirect('/invoices');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('customer', 'items');

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('items');

        return view('invoices.edit', [
            'invoice' => $invoice,
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        DB::transaction(function () use ($request, $invoice) {
            $invoice->update($request->safe()->except('items'));
            $invoice->items()->delete();
            $invoice->items()->createMany($request->validated('items'));
        });

        return redirect('/invoices');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect('/invoices');
    }
}
