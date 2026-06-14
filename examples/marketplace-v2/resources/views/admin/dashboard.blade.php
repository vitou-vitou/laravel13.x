<x-app-layout>
    <x-store-page title="Admin dashboard">
        <x-admin-subnav class="mt-6" />

        <p class="mt-4 text-sm text-stone-600">
            Open disputes: <span class="font-semibold text-stone-900">{{ $openDisputes }}</span>
        </p>

        <div class="mt-6 space-y-3">
            @foreach ($orders as $order)
                <div class="store-panel">
                    <p class="font-semibold text-stone-900">Order #{{ $order->id }} — {{ $order->status->value }}</p>
                    <p class="text-sm text-stone-500">{{ $order->user->email }} — {{ $order->formattedTotal() }}</p>
                </div>
            @endforeach
            {{ $orders->links() }}
        </div>
    </x-store-page>
</x-app-layout>
