<x-app-layout>
    <form method="POST" action="{{ route('invoices.update', $invoice) }}" class="p-6 space-y-4">
        @csrf @method('PUT')
        <select name="customer_id" class="border p-2">
            @foreach ($customers as $c)
                <option value="{{ $c->id }}" @selected($invoice->customer_id === $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        <input name="number" class="border p-2" value="{{ old('number', $invoice->number) }}">
        <input type="date" name="issued_on" class="border p-2" value="{{ old('issued_on', $invoice->issued_on->toDateString()) }}">
        <input type="date" name="due_on" class="border p-2" value="{{ old('due_on', $invoice->due_on->toDateString()) }}">
        <select name="status" class="border p-2">
            @foreach (['draft','sent','paid'] as $s)
                <option value="{{ $s }}" @selected($invoice->status === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <div id="items" class="space-y-2">
            @foreach ($invoice->items as $i => $item)
                <div class="flex gap-2">
                    <input name="items[{{ $i }}][description]" class="border p-2 flex-1" value="{{ $item->description }}">
                    <input name="items[{{ $i }}][quantity]" type="number" min="1" class="border p-2 w-20" value="{{ $item->quantity }}">
                    <input name="items[{{ $i }}][unit_price]" type="number" step="0.01" min="0" class="border p-2 w-32" value="{{ $item->unit_price }}">
                </div>
            @endforeach
        </div>
        <button type="button" onclick="addRow()" class="bg-gray-300 px-3 py-1">+ Item</button>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2">Update</button>
    </form>
    <script>let idx = {{ $invoice->items->count() }};</script>
    @verbatim
    <script>
        function addRow() {
            const div = document.createElement('div');
            div.className = 'flex gap-2';
            div.innerHTML = `
                <input name="items[${idx}][description]" class="border p-2 flex-1">
                <input name="items[${idx}][quantity]" type="number" min="1" value="1" class="border p-2 w-20">
                <input name="items[${idx}][unit_price]" type="number" step="0.01" min="0" value="0" class="border p-2 w-32">
            `;
            document.getElementById('items').appendChild(div);
            idx++;
        }
    </script>
    @endverbatim
</x-app-layout>
