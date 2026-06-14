<x-app-layout>
    <x-store-page title="Commission rate" max="max-w-md">
        <x-admin-subnav class="mt-6" />
        <x-flash-status class="mt-6" />

        <form method="POST" action="{{ route('admin.commission.update') }}" class="store-panel mt-6 space-y-4">
            @csrf
            <label class="block text-sm font-medium text-stone-700">Default commission (basis points)</label>
            <input type="number" name="default_commission_bps" value="{{ $bps }}" min="0" max="5000" class="store-input" required>
            <p class="text-xs text-stone-500">1000 bps = 10%</p>
            <button type="submit" class="btn-brand">Save</button>
        </form>
    </x-store-page>
</x-app-layout>
