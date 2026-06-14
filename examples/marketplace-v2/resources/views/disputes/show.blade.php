<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dispute #{{ $dispute->id }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow rounded p-6 space-y-2">
                <p class="text-sm">Status: <strong>{{ $dispute->status->value }}</strong></p>
                <p class="text-sm">Vendor: {{ $dispute->orderGroup->vendor->store_name }}</p>
                <p class="text-sm">Order: #{{ $dispute->orderGroup->order_id }}</p>
            </div>

            <div class="bg-white shadow rounded p-6 space-y-3">
                @foreach ($dispute->messages as $message)
                    <div class="border-b pb-2">
                        <p class="text-xs text-gray-500">{{ $message->user->name }}</p>
                        <p class="text-sm">{{ $message->body }}</p>
                    </div>
                @endforeach

                <form method="POST" action="{{ route('disputes.message', $dispute) }}" class="space-y-2">
                    @csrf
                    <textarea name="body" rows="3" class="w-full border rounded" required></textarea>
                    <button class="px-3 py-1 bg-gray-800 text-white rounded text-sm">Reply</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
