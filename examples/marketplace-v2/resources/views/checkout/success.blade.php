<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Payment</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6 space-y-4">
                <h1 class="text-lg font-semibold">Thanks for your order</h1>
                <p class="text-sm text-gray-600">
                    Order #{{ $order->id }} — we are confirming your payment with Stripe.
                    Refresh your
                    <a href="{{ route('orders.show', $order) }}" class="underline">order details</a> in a moment.
                </p>
                <p class="text-sm">Status: <strong>{{ $order->status->value }}</strong></p>

                @if (session('status'))
                    <p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded p-3">{{ session('status') }}</p>
                @endif

                @if (app()->environment('local') && \App\Services\Stripe\LocalDevStripeCheckoutService::isLocalDevSession($order->stripe_checkout_session_id) && $order->isPending())
                    <form method="POST" action="{{ route('dev.stripe.simulate-paid', $order) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="bg-gray-800 text-white rounded-md px-6 py-2 text-sm hover:bg-gray-700">
                            Simulate Stripe payment (local dev)
                        </button>
                    </form>
                    <p class="mt-2 text-xs text-gray-500">Only shown when STRIPE_SECRET is not set.</p>
                @endif

                <p><a href="{{ route('orders.index') }}" class="text-sm underline">← All orders</a></p>
            </div>
        </div>
    </div>
</x-app-layout>
