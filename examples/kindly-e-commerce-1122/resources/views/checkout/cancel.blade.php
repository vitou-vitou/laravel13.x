@extends('layouts.shop')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Checkout cancelled</h1>
    <p class="text-sm text-gray-600 mb-4">
        Order #{{ $order->id }} is still <strong>{{ $order->status }}</strong>.
        You can return to the shop or try checkout again from your cart.
    </p>
    <p class="mt-4">
        <a href="{{ route('shop.index') }}" class="text-sm underline">Continue shopping</a>
        ·
        <a href="{{ route('orders.show', $order) }}" class="text-sm underline">View order</a>
    </p>
@endsection
