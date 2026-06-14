<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Commission rate</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="p-4 mb-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            <form method="POST" action="{{ route('admin.commission.update') }}" class="bg-white shadow rounded p-6 space-y-4">
                @csrf
                <label class="block text-sm font-medium">Default commission (basis points)</label>
                <input type="number" name="default_commission_bps" value="{{ $bps }}" min="0" max="5000" class="border rounded w-full" required>
                <p class="text-xs text-gray-500">1000 bps = 10%</p>
                <button class="px-4 py-2 bg-gray-800 text-white rounded">Save</button>
            </form>
        </div>
    </div>
</x-app-layout>
