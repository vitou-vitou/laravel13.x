<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $product->name }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 bg-white shadow rounded p-6 space-y-4">
            <p class="text-sm text-gray-500">Sold by {{ $product->vendor->store_name }}</p>
            <p>{{ $product->description }}</p>

            <form method="POST" action="{{ route('cart.store') }}" class="space-y-3">
                @csrf
                <label class="block text-sm font-medium">Variant</label>
                <select name="product_variant_id" class="border rounded w-full" required>
                    @foreach ($product->variants as $variant)
                        <option value="{{ $variant->id }}">
                            {{ $variant->name }} — {{ $variant->formattedPrice() }} ({{ $variant->stock_qty }} in stock)
                        </option>
                    @endforeach
                </select>
                <label class="block text-sm font-medium">Quantity</label>
                <input type="number" name="quantity" value="1" min="1" class="border rounded w-24">
                <button class="px-4 py-2 bg-gray-800 text-white rounded">Add to cart</button>
            </form>
        </div>
    </div>
</x-app-layout>
