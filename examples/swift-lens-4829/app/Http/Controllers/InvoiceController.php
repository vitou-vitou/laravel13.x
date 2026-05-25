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
        if (Customer::count() === 0) {
            return redirect()
                ->route('customers.create')
                ->with('status', 'Add a customer before creating an invoice.');
        }

        $last = Invoice::orderByDesc('id')->value('number');
        $nextNumber = $this->suggestNextNumber($last);

        return view('invoices.create', [
            'customers' => Customer::orderBy('name')->get(),
            'nextNumber' => $nextNumber,
            'defaultIssuedOn' => now()->toDateString(),
            'defaultDueOn' => now()->addDays(30)->toDateString(),
        ]);
    }

    private function suggestNextNumber(?string $last): string
    {
        if ($last && preg_match('/^(.*?)(\d+)$/', $last, $m)) {
            $width = max(strlen($m[2]), 3);

            return $m[1].str_pad((string) ((int) $m[2] + 1), $width, '0', STR_PAD_LEFT);
        }

        return 'INV-001';
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
