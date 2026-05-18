<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Invoices</h2>
            <a href="{{ route('invoices.create') }}"
               class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New invoice
            </a>
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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-inset ring-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-lg overflow-hidden">
                @if ($invoices->isEmpty())
                    <div class="px-6 py-16 text-center">
                        <p class="text-sm text-gray-500">No invoices yet.</p>
                        <a href="{{ route('invoices.create') }}" class="mt-3 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-500">Create your first invoice</a>
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Number</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Issued</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Total</th>
                                <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($invoices as $inv)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('invoices.show', $inv) }}" class="text-sm font-mono font-medium text-gray-900 hover:text-indigo-600">{{ $inv->number }}</a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $inv->customer->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $inv->issued_on->format('M j, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $statusStyles[$inv->status] ?? $statusStyles['draft'] }}">
                                            {{ ucfirst($inv->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900 tabular-nums">{{ number_format($inv->total, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                        <a href="{{ route('invoices.edit', $inv) }}" class="text-indigo-600 hover:text-indigo-500">Edit</a>
                                        @if (Route::has('invoices.pdf'))
                                            <a href="{{ route('invoices.pdf', $inv) }}" class="text-gray-600 hover:text-gray-900">PDF</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div>{{ $invoices->links() }}</div>
        </div>
    </div>
</x-app-layout>
