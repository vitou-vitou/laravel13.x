<x-app-layout>
    <x-store-page :title="'Order #'.$order->id" max="max-w-4xl">
        <x-flash-status class="mt-6" />

        <div class="store-panel mt-6 space-y-6">
            <div class="flex flex-wrap gap-4 text-sm">
                <p>Status: <span class="font-semibold text-stone-900">{{ $order->status->value }}</span></p>
                <p>Total: <span class="font-semibold text-stone-900">{{ $order->formattedTotal() }}</span></p>
            </div>

            @foreach ($order->groups as $group)
                <div class="rounded-xl border border-stone-100 bg-stone-50/50 p-4 space-y-3">
                    <div>
                        <p class="font-semibold text-stone-900">{{ $group->vendor->store_name }}</p>
                        <p class="text-sm text-stone-500">Group status: {{ $group->status->value }}</p>
                        @if ($group->tracking_number)
                            <p class="text-sm text-stone-600">Tracking: {{ $group->tracking_number }}</p>
                        @endif
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
