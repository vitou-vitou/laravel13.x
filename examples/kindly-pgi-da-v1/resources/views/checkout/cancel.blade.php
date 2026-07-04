<x-app-layout>
    <x-store-page title="Checkout cancelled" max="max-w-xl">
        <div class="store-panel mt-6 space-y-4">
            <p class="text-stone-600">Payment for order #{{ $order->id }} was not completed.</p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('orders.show', $order) }}" class="btn-brand">View order</a>
                <a href="{{ route('cart.index') }}" class="btn-brand-outline">Back to cart</a>
            </div>
        </div>
    </x-store-page>
</x-app-layout>
