<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cart</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            @if ($lines->isEmpty())
                <p class="bg-white shadow rounded p-6">Your cart is empty. <a href="{{ route('catalog.index') }}" class="underline">Browse products</a></p>
            @else
                <div class="bg-white shadow rounded p-6 space-y-4">
                    @foreach ($lines as $line)
                        <div class="flex justify-between items-center border-b pb-3">
                            <div>
                                <p class="font-medium">{{ $line->variant->product->name }} — {{ $line->variant->name }}</p>
                                <p class="text-sm text-gray-500">{{ $line->variant->product->vendor->store_name }}</p>
                            </div>
                            <form method="POST" action="{{ route('cart.update', $line->variant) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="quantity" value="{{ $line->quantity }}" min="0" class="border rounded w-20">
                                <button class="text-sm underline">Update</button>
                            </form>
                            <p class="font-medium">${{ number_format($line->lineTotalCents() / 100, 2) }}</p>
                        </div>
                    @endforeach

                    <div class="pt-4 space-y-2">
                        <h3 class="font-semibold">By vendor</h3>
                        @foreach ($vendorSubtotals as $subtotal)
                            <p class="text-sm">{{ $subtotal['vendor_name'] }}: ${{ number_format($subtotal['subtotal_cents'] / 100, 2) }}</p>
                        @endforeach
                        <p class="text-lg font-bold">Total: ${{ number_format($totalCents / 100, 2) }}</p>
                    </div>

                    @auth
                        <form method="POST" action="{{ route('checkout.store') }}">
                            @csrf
                            <button class="px-4 py-2 bg-gray-800 text-white rounded">Checkout</button>
                        </form>
                    @else
                        <p class="text-sm"><a href="{{ route('login') }}" class="underline">Log in</a> to checkout.</p>
                    @endauth
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
