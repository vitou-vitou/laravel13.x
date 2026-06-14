<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Checkout cancelled</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6 space-y-4">
                <p class="text-sm text-gray-600">Payment for order #{{ $order->id }} was not completed.</p>
                <p><a href="{{ route('orders.show', $order) }}" class="text-sm underline">View order</a></p>
            </div>
        </div>
    </div>
</x-app-layout>
