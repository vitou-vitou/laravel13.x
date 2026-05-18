<x-app-layout>
    <div class="p-6">
        <h1>{{ $invoice->number }}</h1>
        <p>Customer: {{ $invoice->customer->name }}</p>
        <p>Issued: {{ $invoice->issued_on->toDateString() }} — Due: {{ $invoice->due_on->toDateString() }}</p>
        <table class="mt-4 w-full">
            <thead><tr><th>Description</th><th>Qty</th><th>Unit</th><th>Total</th></tr></thead>
            <tbody>
            @foreach ($invoice->items as $i)
                <tr>
                    <td>{{ $i->description }}</td>
                    <td>{{ $i->quantity }}</td>
                    <td>{{ number_format($i->unit_price, 2) }}</td>
                    <td>{{ number_format($i->line_total, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot><tr><td colspan="3" class="text-right font-bold">Total</td><td>{{ number_format($invoice->total, 2) }}</td></tr></tfoot>
        </table>
        @if (Route::has('invoices.pdf'))
            <a href="{{ route('invoices.pdf', $invoice) }}" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2">Download PDF</a>
        @endif
    </div>
</x-app-layout>
