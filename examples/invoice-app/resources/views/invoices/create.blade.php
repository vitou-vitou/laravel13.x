<x-app-layout>
    <form method="POST" action="{{ route('invoices.store') }}" class="p-6 space-y-4">
        @csrf
        <select name="customer_id" class="border p-2">
            @foreach ($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
        <input name="number" placeholder="INV-001" class="border p-2">
        <input type="date" name="issued_on" class="border p-2">
        <input type="date" name="due_on" class="border p-2">
        <select name="status" class="border p-2">
            <option value="draft">Draft</option>
            <option value="sent">Sent</option>
            <option value="paid">Paid</option>
        </select>
        <div id="items" class="space-y-2">
            <div class="flex gap-2">
                <input name="items[0][description]" placeholder="Description" class="border p-2 flex-1">
                <input name="items[0][quantity]" type="number" min="1" value="1" class="border p-2 w-20">
                <input name="items[0][unit_price]" type="number" step="0.01" min="0" value="0" class="border p-2 w-32">
            </div>
        </div>
        <button type="button" onclick="addRow()" class="bg-gray-300 px-3 py-1">+ Item</button>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2">Create</button>
    </form>
    @verbatim
    <script>
        let idx = 1;
        function addRow() {
            const div = document.createElement('div');
            div.className = 'flex gap-2';
            div.innerHTML = `
                <input name="items[${idx}][description]" placeholder="Description" class="border p-2 flex-1">
                <input name="items[${idx}][quantity]" type="number" min="1" value="1" class="border p-2 w-20">
                <input name="items[${idx}][unit_price]" type="number" step="0.01" min="0" value="0" class="border p-2 w-32">
            `;
            document.getElementById('items').appendChild(div);
            idx++;
        }
    </script>
    @endverbatim
</x-app-layout>
