<x-app-layout>
    <div class="p-6">
        <a href="{{ route('invoices.create') }}" class="text-blue-600">+ New Invoice</a>
        <table class="mt-4 w-full">
            <thead><tr><th>Number</th><th>Customer</th><th>Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @foreach ($invoices as $inv)
                <tr>
                    <td><a href="{{ route('invoices.show', $inv) }}">{{ $inv->number }}</a></td>
                    <td>{{ $inv->customer->name }}</td>
                    <td>{{ number_format($inv->total, 2) }}</td>
                    <td>{{ $inv->status }}</td>
                    <td>
                        <a href="{{ route('invoices.edit', $inv) }}">Edit</a>
                        @if (Route::has('invoices.pdf'))
                            <a href="{{ route('invoices.pdf', $inv) }}">PDF</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $invoices->links() }}
    </div>
</x-app-layout>
