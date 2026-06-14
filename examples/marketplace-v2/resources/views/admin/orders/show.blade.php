<x-app-layout>
    <x-store-page :title="'Order #'.$order->id" max="max-w-4xl">
        <x-admin-subnav class="mt-6" />
        <x-flash-status class="mt-6" />

        <div class="store-panel mt-6 space-y-6">
            <div class="flex flex-wrap gap-4 text-sm">
                <p>Status: <span class="font-semibold text-stone-900">{{ $order->status->value }}</span></p>
                <p>Customer: <span class="font-semibold text-stone-900">{{ $order->user->email }}</span></p>
                <p>Total: <span class="font-semibold text-stone-900">{{ $order->formattedTotal() }}</span></p>
                @if ($order->payment)
                    <p>Payment: <span class="font-semibold text-stone-900">{{ $order->payment->status->value }}</span></p>
                    <p>Refunded: <span class="font-semibold text-stone-900">${{ number_format($order->payment->refunded_cents / 100, 2) }}</span></p>
                @endif
            </div>

            @if ($order->payment && $refundableCents > 0)
                <form method="POST" action="{{ route('admin.orders.refund', $order) }}" class="rounded-xl border border-stone-200 bg-stone-50/50 p-4 space-y-3">
                    @csrf
                    <h2 class="font-semibold text-stone-900">Issue refund</h2>
                    <p class="text-sm text-stone-600">Up to ${{ number_format($refundableCents / 100, 2) }} refundable.</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-stone-700">Amount (cents)</label>
                            <input type="number" name="amount_cents" value="{{ old('amount_cents', $refundableCents) }}" min="1" max="{{ $refundableCents }}" class="store-input mt-1" required>
                            @error('amount_cents')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-stone-700">Reason</label>
                            <textarea name="reason" rows="2" class="store-input mt-1" required>{{ old('reason') }}</textarea>
                            @error('reason')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <button type="submit" class="btn-brand-outline">Refund payment</button>
                </form>
            @endif

            @if ($order->payment?->refunds->isNotEmpty())
                <div>
                    <h2 class="font-semibold text-stone-900">Refunds</h2>
                    <ul class="mt-2 space-y-2 text-sm text-stone-600">
                        @foreach ($order->payment->refunds as $refund)
                            <li class="rounded-lg border border-stone-100 px-3 py-2">
                                ${{ number_format($refund->amount_cents / 100, 2) }} — {{ $refund->status->value }} — {{ $refund->reason }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @foreach ($order->groups as $group)
                <div class="rounded-xl border border-stone-100 bg-white p-4 text-sm">
                    <p class="font-semibold text-stone-900">{{ $group->vendor->store_name }}</p>
                    <p class="text-stone-500">{{ $group->status->value }} — ${{ number_format($group->subtotal_cents / 100, 2) }}</p>
                </div>
            @endforeach
        </div>
    </x-store-page>
</x-app-layout>
