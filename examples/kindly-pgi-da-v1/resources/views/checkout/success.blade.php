<x-app-layout>
    <x-store-page title="Thanks for your order" max="max-w-xl">
        <div class="store-panel mt-6 space-y-4">
            <p class="text-stone-600">
                Order #{{ $order->id }} — we are confirming your payment with Stripe.
                Refresh your
                <a href="{{ route('orders.show', $order) }}" class="link-brand">order details</a> in a moment.
            </p>
            <p class="text-sm text-stone-600">Status: <span class="font-semibold text-stone-900">{{ $order->status->value }}</span></p>

            @if (session('status'))
                <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ session('status') }}</p>
            @endif

            @if (app()->environment('local') && \App\Services\Stripe\LocalDevStripeCheckoutService::isLocalDevSession($order->stripe_checkout_session_id) && $order->isPending())
                <form method="POST" action="{{ route('dev.stripe.simulate-paid', $order) }}" class="border-t border-stone-100 pt-4">
                    @csrf
                    <button type="submit" class="btn-brand">Simulate Stripe payment (local dev)</button>
                </form>
                <p class="text-xs text-stone-500">Only shown when STRIPE_SECRET is not set.</p>
            @endif

            <a href="{{ route('orders.index') }}" class="link-brand inline-block text-sm">← All orders</a>
        </div>
    </x-store-page>
</x-app-layout>
