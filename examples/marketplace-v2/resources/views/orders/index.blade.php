<x-app-layout>
    <x-store-page title="My orders" max="max-w-4xl">
        <x-flash-status class="mt-6" />

        <div class="mt-6 space-y-4">
            @forelse ($orders as $order)
                <div class="store-panel flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-semibold text-stone-900">Order #{{ $order->id }}</p>
                        <p class="text-sm text-stone-500">{{ $order->status->value }} — {{ $order->formattedTotal() }}</p>
                        @if ($order->isPaid() && $order->groups->isNotEmpty())
                            @php($group = $order->groups->first())
                            <div class="mt-2 max-w-md">
                                <x-order-timeline :group="$group" :order-paid="true" />
                            </div>
                        @endif
                    </div>
                    <a href="{{ route('orders.show', $order) }}" class="link-brand text-sm">View details →</a>
                </div>
            @empty
                <div class="store-panel text-center">
                    <p class="font-medium text-stone-900">No orders yet</p>
                    <a href="{{ route('catalog.index') }}" class="btn-brand mt-4 inline-flex">Start shopping</a>
                </div>
            @endforelse

            <div class="pt-2">{{ $orders->links() }}</div>
        </div>
    </x-store-page>
</x-app-layout>
