<x-app-layout>
    <x-store-page title="Admin — Disputes">
        <x-admin-subnav class="mt-6" />
        <x-flash-status class="mt-6" />

        <div class="mt-6 space-y-3">
            @foreach ($disputes as $dispute)
                <div class="store-panel space-y-2">
                    <p class="font-semibold text-stone-900">Dispute #{{ $dispute->id }} — {{ $dispute->status->value }}</p>
                    <p class="text-sm text-stone-500">{{ $dispute->orderGroup->vendor->store_name }} / order #{{ $dispute->orderGroup->order_id }}</p>
                    <p class="text-sm text-stone-600">{{ \Illuminate\Support\Str::limit($dispute->reason, 120) }}</p>
                    @if (! in_array($dispute->status->value, ['resolved_buyer', 'resolved_vendor'], true))
                        <div class="flex flex-wrap gap-3 border-t border-stone-100 pt-3">
                            <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}">
                                @csrf
                                <input type="hidden" name="resolution" value="buyer">
                                <button type="submit" class="link-brand text-sm">Resolve for buyer</button>
                            </form>
                            <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}">
                                @csrf
                                <input type="hidden" name="resolution" value="vendor">
                                <button type="submit" class="link-brand text-sm">Resolve for vendor</button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
            {{ $disputes->links() }}
        </div>
    </x-store-page>
</x-app-layout>
