<x-app-layout>
    <div class="bg-stone-50">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <nav class="mb-6 text-sm text-stone-500">
                <a href="{{ route('catalog.index') }}" class="hover:text-brand-600">Shop</a>
                <span class="mx-2">/</span>
                <span class="text-stone-800">{{ $product->name }}</span>
            </nav>

            <div class="grid gap-10 lg:grid-cols-2">
                <div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-sm">
                    <img
                        src="{{ $product->displayImageUrl() }}"
                        alt="{{ $product->name }}"
                        class="aspect-square w-full object-cover"
                    />
                </div>

                <div class="lg:sticky lg:top-8 lg:self-start">
                    <div class="rounded-2xl border border-stone-200/80 bg-white p-6 shadow-sm sm:p-8">
                        <p class="text-sm font-semibold uppercase tracking-wide text-brand-600">
                            {{ $product->vendor->store_name }}
                        </p>
                        <h1 class="mt-2 text-3xl font-bold tracking-tight text-stone-900">{{ $product->name }}</h1>
                        @if ($product->category)
                            <p class="mt-2 text-sm text-stone-500">{{ $product->category->name }}</p>
                        @endif
                        <p class="mt-4 text-stone-600 leading-relaxed">{{ $product->description }}</p>

                        <form method="POST" action="{{ route('cart.store') }}" class="mt-8 space-y-4 border-t border-stone-100 pt-6">
                            @csrf
                            <div>
                                <label for="product_variant_id" class="block text-sm font-medium text-stone-700">Variant</label>
                                <select id="product_variant_id" name="product_variant_id" class="store-input mt-1" required>
                                    @foreach ($product->variants as $variant)
                                        <option value="{{ $variant->id }}">
                                            {{ $variant->name }} — {{ $variant->formattedPrice() }} ({{ $variant->stock_qty }} in stock)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-stone-700">Quantity</label>
                                <input id="quantity" type="number" name="quantity" value="1" min="1" class="store-input mt-1 w-28">
                            </div>
                            <button type="submit" class="btn-brand w-full sm:w-auto">Add to cart</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
