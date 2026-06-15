<x-app-layout>
    <x-store-page title="Payment audit log">
        <x-admin-subnav class="mt-6" />

        <div class="mt-6 space-y-2">
            @foreach ($logs as $log)
                <div class="store-panel py-3 text-sm text-stone-700">
                    Payment #{{ $log->payment_id }}
                    @if ($log->payment?->order)
                        (order #{{ $log->payment->order_id }})
                    @endif
                    — {{ $log->from_status ?? 'null' }} → {{ $log->to_status }}
                    @if ($log->note)
                        <span class="text-stone-500">({{ $log->note }})</span>
                    @endif
                </div>
            @endforeach
            {{ $logs->links() }}
        </div>
    </x-store-page>
</x-app-layout>
