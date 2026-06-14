<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Order #{{ $order->id }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow rounded p-6 space-y-4">
                <p>Status: <strong>{{ $order->status->value }}</strong></p>
                <p>Total: <strong>{{ $order->formattedTotal() }}</strong></p>

                @foreach ($order->groups as $group)
                    <div class="border rounded p-4 space-y-2">
                        <p class="font-medium">{{ $group->vendor->store_name }}</p>
                        <p class="text-sm text-gray-500">Group status: {{ $group->status->value }}</p>
                        @if ($group->tracking_number)
                            <p class="text-sm">Tracking: {{ $group->tracking_number }}</p>
                        @endif
                        <ul class="mt-2 text-sm">
                            @foreach ($group->lines as $line)
                                <li class="flex justify-between gap-4">
                                    <span>{{ $line->product_name_snapshot }} ({{ $line->variant_name_snapshot }}) × {{ $line->quantity }}</span>
                                    @if ($order->isPaid() && $line->variant)
                                        <a href="{{ route('reviews.create', [$order, $line->variant->product_id]) }}" class="underline text-xs">Review</a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        @if ($order->isPaid() && ! $group->dispute && in_array($group->status->value, ['shipped', 'delivered', 'completed'], true))
                            <form method="POST" action="{{ route('disputes.store', $group) }}" class="mt-2 space-y-2">
                                @csrf
                                <textarea name="reason" rows="2" class="w-full border rounded text-sm" placeholder="Describe the issue" required></textarea>
                                <button class="text-sm underline text-red-700">File dispute</button>
                            </form>
                        @elseif ($group->dispute)
                            <a href="{{ route('disputes.show', $group->dispute) }}" class="text-sm underline">View dispute</a>
                        @endif
                    </div>
                @endforeach

                @if ($order->isPending())
                    <p class="text-sm text-gray-600">Complete payment via Stripe checkout. If you already paid, refresh in a moment.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
