<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500">Invoice</p>
                <h2 class="font-mono font-semibold text-xl text-gray-900 leading-tight">{{ $invoice->number }}</h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('invoices.edit', $invoice) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Edit</a>
                @if (Route::has('invoices.pdf'))
                    <a href="{{ route('invoices.pdf', $invoice) }}"
                       class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-800">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Download PDF
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    @php
        $statusStyles = [
            'draft' => 'bg-gray-100 text-gray-700 ring-gray-200',
            'sent' => 'bg-amber-50 text-amber-800 ring-amber-200',
            'paid' => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
        ];
    @endphp

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-lg p-5">
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Customer</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $invoice->customer->name }}</dd>
                    <dd class="mt-1 text-sm text-gray-500">{{ $invoice->customer->email }}</dd>
                </div>
                <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-lg p-5">
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Dates</dt>
                    <dd class="mt-1 text-sm text-gray-900">Issued {{ $invoice->issued_on->format('M j, Y') }}</dd>
                    <dd class="mt-1 text-sm text-gray-500">Due {{ $invoice->due_on->format('M j, Y') }}</dd>
                </div>
                <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-lg p-5">
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Status</dt>
                    <dd class="mt-2">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $statusStyles[$invoice->status] ?? $statusStyles['draft'] }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </dd>
                </div>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Description</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Qty</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Unit</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Line total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($invoice->items as $i)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $i->description }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-700 tabular-nums">{{ $i->quantity }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-700 tabular-nums">{{ number_format($i->unit_price, 2) }}</td>
                                <td class="px-6 py-4 text-right text-sm font-medium text-gray-900 tabular-nums">{{ number_format($i->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-right text-sm font-semibold text-gray-700 uppercase tracking-wider">Total</td>
                            <td class="px-6 py-3 text-right text-base font-semibold text-gray-900 tabular-nums">{{ number_format($invoice->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div>
                <a href="{{ route('invoices.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back to invoices</a>
            </div>
        </div>
    </div>
</x-app-layout>
