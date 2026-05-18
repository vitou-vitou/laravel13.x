<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit invoice <span class="font-mono">{{ $invoice->number }}</span></h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-lg">
                <form method="POST" action="{{ route('invoices.update', $invoice) }}" class="divide-y divide-gray-100">
                    @csrf @method('PUT')

                    <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <x-input-label for="customer_id" value="Customer" />
                            <select id="customer_id" name="customer_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                @foreach ($customers as $c)
                                    <option value="{{ $c->id }}" @selected($invoice->customer_id === $c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="number" value="Number" />
                            <x-text-input id="number" name="number" type="text" class="mt-1 block w-full font-mono" value="{{ old('number', $invoice->number) }}" required />
                        </div>
                        <div>
                            <x-input-label for="status" value="Status" />
                            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                @foreach (['draft','sent','paid'] as $s)
                                    <option value="{{ $s }}" @selected($invoice->status === $s)>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="issued_on" value="Issued on" />
                            <x-text-input id="issued_on" name="issued_on" type="date" class="mt-1 block w-full" value="{{ old('issued_on', $invoice->issued_on->toDateString()) }}" required />
                        </div>
                        <div>
                            <x-input-label for="due_on" value="Due on" />
                            <x-text-input id="due_on" name="due_on" type="date" class="mt-1 block w-full" value="{{ old('due_on', $invoice->due_on->toDateString()) }}" required />
                        </div>
                    </div>

                    <div class="px-6 py-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-gray-800">Line items</h3>
                            <button type="button" onclick="addRow()"
                                class="inline-flex items-center gap-1 rounded-md bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                + Add item
                            </button>
                        </div>
                        <div id="items" class="space-y-2">
                            @foreach ($invoice->items as $i => $item)
                                <div class="grid grid-cols-12 gap-2 items-center">
                                    <input name="items[{{ $i }}][description]" required
                                        class="col-span-7 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        value="{{ $item->description }}">
                                    <input name="items[{{ $i }}][quantity]" type="number" min="1" required
                                        class="col-span-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-right tabular-nums"
                                        value="{{ $item->quantity }}">
                                    <input name="items[{{ $i }}][unit_price]" type="number" step="0.01" min="0" required
                                        class="col-span-3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-right tabular-nums"
                                        value="{{ $item->unit_price }}">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 flex items-center justify-end gap-3 sm:rounded-b-lg">
                        <a href="{{ route('invoices.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Cancel</a>
                        <x-primary-button>Save changes</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>let idx = {{ $invoice->items->count() }};</script>
    @verbatim
    <script>
        function addRow() {
            const row = document.createElement('div');
            row.className = 'grid grid-cols-12 gap-2 items-center';
            row.innerHTML = `
                <input name="items[${idx}][description]" placeholder="Description" required
                    class="col-span-7 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <input name="items[${idx}][quantity]" type="number" min="1" value="1" required
                    class="col-span-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-right tabular-nums">
                <input name="items[${idx}][unit_price]" type="number" step="0.01" min="0" value="0" required
                    class="col-span-3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-right tabular-nums">
            `;
            document.getElementById('items').appendChild(row);
            idx++;
        }
    </script>
    @endverbatim
</x-app-layout>
