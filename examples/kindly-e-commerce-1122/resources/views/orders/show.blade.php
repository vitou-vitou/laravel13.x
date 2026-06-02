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

    <div class="bg-white rounded-lg shadow p-6 text-sm mb-6">
        <h2 class="font-semibold mb-2">Timeline</h2>
        <ul class="list-disc list-inside text-gray-700 space-y-1">
            <li>Placed: {{ $order->created_at->format('M j, Y g:i A') }}</li>
            @if ($order->paid_at)
                <li>Paid: {{ $order->paid_at->format('M j, Y g:i A') }}</li>
            @endif
            @if ($order->shipped_at)
                <li>Shipped: {{ $order->shipped_at->format('M j, Y g:i A') }}</li>
            @endif
        </ul>
    </div>

    @if ($order->isPending())
        <p class="text-sm text-gray-600">Payment is pending. Complete checkout via Stripe from your cart if you did not finish paying.</p>
    @endif

    @if (auth()->user()->is_admin && $order->isPaid())
        <form method="POST" action="{{ route('admin.orders.ship', $order) }}" class="mt-4">
            @csrf
            <button type="submit" class="rounded bg-gray-900 text-white text-sm px-4 py-2">
                Mark as shipped
            </button>
        </form>
    @endif

    <p class="mt-4"><a href="{{ route('orders.index') }}" class="text-sm underline">← All orders</a></p>
@endsection
