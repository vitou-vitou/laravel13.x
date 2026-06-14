<x-app-layout>
    <x-store-page title="Your cart" max="max-w-4xl">
        <x-flash-status class="mt-6" />

            @if ($lines->isEmpty())
                <div class="store-card mt-6 p-10 text-center">
                    <p class="text-lg font-medium text-stone-900">Your cart is empty</p>
                    <p class="mt-1 text-stone-500">Browse the catalog and add a variant to get started.</p>
                    <a href="{{ route('catalog.index') }}" class="btn-brand mt-6 inline-flex">Browse products</a>
                </div>
            @else
                <div class="store-card mt-6 divide-y divide-stone-100">
                    @foreach ($lines as $line)
                        <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold text-stone-900">{{ $line->variant->product->name }} — {{ $line->variant->name }}</p>
                                <p class="text-sm text-stone-500">{{ $line->variant->product->vendor->store_name }}</p>
                            </div>
                            <form method="POST" action="{{ route('cart.update', $line->variant) }}" class="flex items-center gap-3">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="quantity" value="{{ $line->quantity }}" min="0" class="store-input w-24">
                                <button type="submit" class="text-sm font-medium text-brand-600 hover:text-brand-700">Update</button>
                            </form>
                            <p class="font-semibold text-stone-900">${{ number_format($line->lineTotalCents() / 100, 2) }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="store-card mt-6 p-6">
                    <h2 class="font-semibold text-stone-900">Order summary</h2>
                    <div class="mt-4 space-y-2 text-sm text-stone-600">
                        @foreach ($vendorSubtotals as $subtotal)
                            <div class="flex justify-between">
                                <span>{{ $subtotal['vendor_name'] }}</span>
                                <span>${{ number_format($subtotal['subtotal_cents'] / 100, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-4 border-t border-stone-100 pt-4 text-xl font-bold text-stone-900">
                        Total: ${{ number_format($totalCents / 100, 2) }}
                    </p>

                    @auth
                        <form method="POST" action="{{ route('checkout.store') }}" class="mt-6">
                            @csrf
                            <button type="submit" class="btn-brand">Checkout</button>
                        </form>
                    @else
                        <p class="mt-6 text-sm text-stone-600">
                            <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:text-brand-700">Log in</a> to checkout.
                        </p>
                    @endauth
                </div>
            @endif
    </x-store-page>
</x-app-layout>
