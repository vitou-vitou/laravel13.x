<x-app-layout>
    <x-store-page :title="'Order #'.$order->id" max="max-w-4xl">
        <x-flash-status class="mt-6" />

        <div class="store-panel mt-6 space-y-6">
            <div class="flex flex-wrap gap-4 text-sm">
                <p>Status: <span class="font-semibold text-stone-900">{{ $order->status->value }}</span></p>
                <p>Total: <span class="font-semibold text-stone-900">{{ $order->formattedTotal() }}</span></p>
            </div>

            @if ($order->isPaid())
                <p class="rounded-lg border border-stone-200 bg-stone-50/80 px-4 py-3 text-sm text-stone-600">
                    <span class="font-medium text-stone-900">Buyer protection:</span>
                    Problems with a shipment? File a dispute on the vendor section below. Refunds are reviewed by admins when eligible.
                </p>
            @endif

            @if ($order->shipping_address_snapshot)
                <div class="rounded-xl border border-stone-100 bg-white p-4 text-sm text-stone-600">
                    <p class="font-medium text-stone-900">Ship to</p>
                    <p class="mt-1">{{ $order->shipping_address_snapshot['name'] ?? '' }}</p>
                    <p>{{ $order->shipping_address_snapshot['line1'] ?? '' }}</p>
                    @if (! empty($order->shipping_address_snapshot['line2']))
                        <p>{{ $order->shipping_address_snapshot['line2'] }}</p>
                    @endif
                    <p>
                        {{ $order->shipping_address_snapshot['city'] ?? '' }},
                        {{ $order->shipping_address_snapshot['region'] ?? '' }}
                        {{ $order->shipping_address_snapshot['postal_code'] ?? '' }}
                    </p>
                </div>
            @endif

            @foreach ($order->groups as $group)
                <div class="rounded-xl border border-stone-100 bg-stone-50/50 p-4 space-y-3">
                    <div>
                        <p class="font-semibold text-stone-900">{{ $group->vendor->store_name }}</p>
                        <x-order-timeline :group="$group" :order-paid="$order->isPaid()" class="mt-3" />
                    </div>
                    <ul class="space-y-2 text-sm">
                        @foreach ($group->lines as $line)
                            <li class="flex flex-wrap items-center justify-between gap-2">
                                <span class="text-stone-700">{{ $line->product_name_snapshot }} ({{ $line->variant_name_snapshot }}) × {{ $line->quantity }}</span>
                                @if ($order->isPaid() && $line->variant)
                                    <a href="{{ route('reviews.create', [$order, $line->variant->product_id]) }}" class="link-brand text-xs">Review</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    @if ($order->isPaid() && ! $group->dispute && in_array($group->status->value, ['shipped', 'delivered', 'completed'], true))
                        <form method="POST" action="{{ route('disputes.store', $group) }}" class="space-y-2 border-t border-stone-200 pt-3">
                            @csrf
                            <textarea name="reason" rows="2" class="store-input text-sm" placeholder="Describe the issue" required></textarea>
                            <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700">File dispute</button>
                        </form>
                    @elseif ($group->dispute)
                        <a href="{{ route('disputes.show', $group->dispute) }}" class="link-brand text-sm">View dispute →</a>
                    @endif
                </div>
            @endforeach

            @if ($order->isPending())
                <p class="text-sm text-stone-600">Complete payment via Stripe checkout. If you already paid, refresh in a moment.</p>
            @endif
        </div>
    </x-store-page>
</x-app-layout>
