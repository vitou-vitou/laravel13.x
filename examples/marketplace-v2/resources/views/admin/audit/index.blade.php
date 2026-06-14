<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Payment audit log</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-2">
            @foreach ($logs as $log)
                <div class="bg-white shadow rounded p-3 text-sm">
                    Payment #{{ $log->payment_id }}
                    @if ($log->payment?->order)
                        (order #{{ $log->payment->order_id }})
                    @endif
                    — {{ $log->from_status ?? 'null' }} → {{ $log->to_status }}
                    @if ($log->note)
                        <span class="text-gray-500">({{ $log->note }})</span>
                    @endif
                </div>
            @endforeach

            {{ $logs->links() }}
        </div>
    </div>
</x-app-layout>
