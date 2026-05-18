<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
    </x-slot>

    @php
        $customerCount = \App\Models\Customer::count();
        $invoiceCount = \App\Models\Invoice::count();
        $outstanding = \App\Models\Invoice::with('items')->where('status', '!=', 'paid')->get()->sum(fn ($i) => $i->total);
        $paid = \App\Models\Invoice::with('items')->where('status', 'paid')->get()->sum(fn ($i) => $i->total);
    @endphp

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-inset ring-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            @if ($customerCount === 0)
                <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-lg px-6 py-10 text-center">
                    <h3 class="text-base font-semibold text-gray-900">Welcome, {{ Auth::user()->name }}.</h3>
                    <p class="mt-1 text-sm text-gray-500 max-w-md mx-auto">Add your first customer, then create an invoice. Two steps to a downloadable PDF.</p>
                    <div class="mt-5">
                        <a href="{{ route('customers.create') }}"
                           class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500">
                            Add first customer
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <a href="{{ route('customers.index') }}" class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-lg p-5 hover:ring-indigo-300 transition">
                        <dt class="text-xs uppercase tracking-wider text-gray-500">Customers</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 tabular-nums">{{ $customerCount }}</dd>
                    </a>
                    <a href="{{ route('invoices.index') }}" class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-lg p-5 hover:ring-indigo-300 transition">
                        <dt class="text-xs uppercase tracking-wider text-gray-500">Invoices</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 tabular-nums">{{ $invoiceCount }}</dd>
                    </a>
                    <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-lg p-5">
                        <dt class="text-xs uppercase tracking-wider text-gray-500">Outstanding</dt>
                        <dd class="mt-1 text-2xl font-semibold text-amber-700 tabular-nums">{{ number_format($outstanding, 2) }}</dd>
                    </div>
                    <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-lg p-5">
                        <dt class="text-xs uppercase tracking-wider text-gray-500">Paid</dt>
                        <dd class="mt-1 text-2xl font-semibold text-emerald-700 tabular-nums">{{ number_format($paid, 2) }}</dd>
                    </div>
                </div>

                <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-lg px-6 py-5 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Quick actions</h3>
                        <p class="mt-1 text-sm text-gray-500">Add another customer or draft a new invoice.</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('customers.create') }}" class="rounded-md bg-white px-3 py-1.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">New customer</a>
                        <a href="{{ route('invoices.create') }}" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500">New invoice</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
