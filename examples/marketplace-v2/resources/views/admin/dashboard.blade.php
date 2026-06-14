<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin dashboard</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <p class="text-sm text-gray-600">Open disputes: <strong>{{ $openDisputes }}</strong></p>
            <div class="flex gap-4 text-sm">
                <a href="{{ route('admin.vendors.index') }}" class="underline">Vendors</a>
                <a href="{{ route('admin.commission.edit') }}" class="underline">Commission</a>
                <a href="{{ route('admin.disputes.index') }}" class="underline">Disputes</a>
                <a href="{{ route('admin.audit') }}" class="underline">Payment audit</a>
            </div>

            @foreach ($orders as $order)
                <div class="bg-white shadow rounded p-4">
                    <p class="font-medium">Order #{{ $order->id }} — {{ $order->status->value }}</p>
                    <p class="text-sm text-gray-500">{{ $order->user->email }} — {{ $order->formattedTotal() }}</p>
                </div>
            @endforeach

            {{ $orders->links() }}
        </div>
    </div>
</x-app-layout>
