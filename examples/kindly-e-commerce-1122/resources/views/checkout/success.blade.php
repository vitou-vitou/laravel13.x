@extends('layouts.shop')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Thanks for your order</h1>
    <p class="text-sm text-gray-600 mb-4">
        Order #{{ $order->id }} — we are confirming your payment with Stripe.
        This page does not mark your order as paid; refresh your
        <a href="{{ route('orders.show', $order) }}" class="underline">order details</a> in a moment.
    </p>
    <p class="text-sm">Status: <strong>{{ $order->status }}</strong></p>

    @if (session('status'))
        <p class="mt-3 text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded p-3">{{ session('status') }}</p>
    @endif

    @if (app()->environment('local') && \App\Services\Stripe\LocalDevStripeCheckoutService::isLocalDevSession($order->stripe_checkout_session_id) && $order->isPending())
        <form method="POST" action="{{ route('dev.stripe.simulate-paid', $order) }}" class="mt-4">
            @csrf
            <button type="submit" class="bg-gray-800 text-white rounded-md px-6 py-2 text-sm hover:bg-gray-700">
                Simulate Stripe payment (local dev)
            </button>
        </form>
        <p class="mt-2 text-xs text-gray-500">Only shown when STRIPE_SECRET is not set. Real Stripe uses webhooks instead.</p>
    @endif

    <p class="mt-4"><a href="{{ route('orders.index') }}" class="text-sm underline">← All orders</a></p>
@endsection
