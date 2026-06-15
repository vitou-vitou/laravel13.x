<x-app-layout>
    <x-store-page title="Promo codes" max="max-w-3xl">
        <x-admin-subnav class="mt-6" />
        <x-flash-status class="mt-6" />

        <form method="POST" action="{{ route('admin.promo-codes.store') }}" class="store-panel mt-6 space-y-4">
            @csrf
            <h2 class="font-semibold text-stone-900">Create promo code</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-stone-700">Code</label>
                    <input type="text" name="code" value="{{ old('code') }}" class="store-input mt-1 uppercase" required maxlength="40">
                    @error('code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700">Type</label>
                    <select name="type" class="store-input mt-1" required>
                        <option value="percent" @selected(old('type') === 'percent')>Percent off</option>
                        <option value="fixed" @selected(old('type') === 'fixed')>Fixed amount (cents)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700">Value</label>
                    <input type="number" name="value" value="{{ old('value') }}" min="1" class="store-input mt-1" required>
                    <p class="mt-1 text-xs text-stone-500">Percent: 1–100. Fixed: amount in cents.</p>
                    @error('value')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700">Max uses (optional)</label>
                    <input type="number" name="max_uses" value="{{ old('max_uses') }}" min="1" class="store-input mt-1">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-stone-700">Expires at (optional)</label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="store-input mt-1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700">Vendor scope (optional)</label>
                    <select name="vendor_id" class="store-input mt-1">
                        <option value="">Platform-wide</option>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected((string) old('vendor_id') === (string) $vendor->id)>{{ $vendor->store_name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-stone-500">Leave blank for all vendors. Scoped codes discount only that vendor's cart lines.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700">Min subtotal (cents, optional)</label>
                    <input type="number" name="min_subtotal_cents" value="{{ old('min_subtotal_cents') }}" min="1" class="store-input mt-1">
                </div>
            </div>
            <button type="submit" class="btn-brand">Create</button>
        </form>

        <div class="store-card mt-8 overflow-hidden">
            <table class="min-w-full divide-y divide-stone-100 text-sm">
                <thead class="bg-stone-50 text-left text-stone-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Code</th>
                        <th class="px-4 py-3 font-medium">Scope</th>
                        <th class="px-4 py-3 font-medium">Discount</th>
                        <th class="px-4 py-3 font-medium">Uses</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($promoCodes as $promo)
                        <tr>
                            <td class="px-4 py-3 font-mono font-medium text-stone-900">{{ $promo->code }}</td>
                            <td class="px-4 py-3 text-stone-600">
                                {{ $promo->vendor?->store_name ?? 'Platform' }}
                                @if ($promo->min_subtotal_cents)
                                    <span class="block text-xs text-stone-500">Min ${{ number_format($promo->min_subtotal_cents / 100, 2) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-stone-600">
                                @if ($promo->type->value === 'percent')
                                    {{ $promo->value }}%
                                @else
                                    ${{ number_format($promo->value / 100, 2) }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-stone-600">
                                {{ $promo->uses_count }}@if ($promo->max_uses) / {{ $promo->max_uses }}@endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($promo->is_active && $promo->isUsable())
                                    <span class="text-emerald-700">Active</span>
                                @elseif ($promo->is_active)
                                    <span class="text-amber-700">Exhausted / expired</span>
                                @else
                                    <span class="text-stone-500">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if ($promo->is_active)
                                    <form method="POST" action="{{ route('admin.promo-codes.destroy', $promo) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-700">Deactivate</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-stone-500">No promo codes yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-store-page>
</x-app-layout>
