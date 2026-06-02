@extends('layouts.shop')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Order #{{ $order->id }}</h1>
    <p class="text-sm text-gray-600 mb-2">Status: <strong>{{ $order->status }}</strong></p>
    @if ($order->discount_cents > 0)
        <p class="text-sm text-gray-600">Subtotal: ${{ number_format($order->subtotal_cents / 100, 2) }} · Coupon {{ $order->coupon_code }}: −${{ number_format($order->discount_cents / 100, 2) }}</p>
    @endif
    <p class="text-sm text-gray-600 mb-4">Total: {{ $order->formattedTotal() }}</p>

    <ul class="bg-white rounded-lg shadow p-6 list-disc list-inside text-sm mb-6">
        @foreach ($order->items as $item)
            <li>{{ $item->product->name }} × {{ $item->quantity }}</li>
        @endforeach
    </ul>

    @if ($order->isPending())
        <p class="text-sm text-gray-600">Payment is pending. Complete checkout via Stripe from your cart if you did not finish paying.</p>
    @endif

    <p class="mt-4"><a href="{{ route('orders.index') }}" class="text-sm underline">← All orders</a></p>
@endsection
