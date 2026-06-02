@extends('layouts.shop')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Your orders</h1>

    @forelse ($orders as $order)
        <article class="bg-white rounded-lg shadow p-6 mb-4">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="font-semibold"><a href="{{ route('orders.show', $order) }}" class="hover:underline">Order #{{ $order->id }}</a></h2>
                    <p class="text-sm text-gray-600">Status: {{ $order->status }} · {{ $order->created_at->format('M j, Y') }}</p>
                    @if ($order->coupon_code)
                        <p class="text-xs text-green-700">Coupon {{ $order->coupon_code }} applied</p>
                    @endif
                </div>
                <p class="font-medium">{{ $order->formattedTotal() }}</p>
            </div>
            <ul class="mt-4 text-sm text-gray-700 list-disc list-inside">
                @foreach ($order->items as $item)
                    <li>{{ $item->product->name }} × {{ $item->quantity }} @ {{ '$'.number_format($item->unit_price_cents / 100, 2) }}</li>
                @endforeach
            </ul>
        </article>
    @empty
        <p class="text-gray-600">No orders yet.</p>
    @endforelse
@endsection
