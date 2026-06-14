<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin — Disputes</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            @foreach ($disputes as $dispute)
                <div class="bg-white shadow rounded p-4 space-y-2">
                    <p class="font-medium">Dispute #{{ $dispute->id }} — {{ $dispute->status->value }}</p>
                    <p class="text-sm text-gray-500">{{ $dispute->orderGroup->vendor->store_name }} / order #{{ $dispute->orderGroup->order_id }}</p>
                    <p class="text-sm">{{ \Illuminate\Support\Str::limit($dispute->reason, 120) }}</p>
                    @if (! in_array($dispute->status->value, ['resolved_buyer', 'resolved_vendor'], true))
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}">
                                @csrf
                                <input type="hidden" name="resolution" value="buyer">
                                <button class="text-sm underline">Resolve for buyer</button>
                            </form>
                            <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}">
                                @csrf
                                <input type="hidden" name="resolution" value="vendor">
                                <button class="text-sm underline">Resolve for vendor</button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach

            {{ $disputes->links() }}
        </div>
    </div>
</x-app-layout>
