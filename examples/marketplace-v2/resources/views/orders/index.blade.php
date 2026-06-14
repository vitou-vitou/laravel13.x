<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Orders</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @foreach ($orders as $order)
                <div class="bg-white shadow rounded p-4 flex justify-between">
                    <div>
                        <p class="font-medium">Order #{{ $order->id }}</p>
                        <p class="text-sm text-gray-500">{{ $order->status->value }} — {{ $order->formattedTotal() }}</p>
                    </div>
                    <a href="{{ route('orders.show', $order) }}" class="underline">View</a>
                </div>
            @endforeach
            {{ $orders->links() }}
        </div>
    </div>
</x-app-layout>
