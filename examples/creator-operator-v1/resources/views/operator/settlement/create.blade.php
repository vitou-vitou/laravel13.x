<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add monthly settlement</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-creator-hub-nav :creator="$creator" />

            <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-900">
                <strong>Formula preview (example):</strong>
                attributed {{ number_format($preview['attributed_revenue'], 2) }} ·
                commission {{ number_format($preview['commission_amount'], 2) }} ·
                creator net {{ number_format($preview['creator_net'], 2) }}
                <span class="text-indigo-700">(S÷T attribution from README)</span>
            </div>

            <form method="POST" action="{{ route('operator.creators.settlement.store', $creator) }}" class="bg-white shadow-sm rounded-lg p-6 space-y-4 border border-stone-100">
                @csrf

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="period_start" value="Period start" />
                        <x-text-input id="period_start" name="period_start" type="date" class="mt-1 block w-full" :value="old('period_start')" required />
                    </div>
                    <div>
                        <x-input-label for="period_end" value="Period end" />
                        <x-text-input id="period_end" name="period_end" type="date" class="mt-1 block w-full" :value="old('period_end')" required />
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="platform" value="Platform" />
                        <select id="platform" name="platform" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            @foreach ($platforms as $platform)
                                <option value="{{ $platform->value }}" @selected(old('platform') === $platform->value)>{{ $platform->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="payout_status" value="Payout status" />
                        <select id="payout_status" name="payout_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            @foreach ($payoutStatuses as $status)
                                <option value="{{ $status->value }}" @selected(old('payout_status') === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <x-input-label for="gross_payout_local" value="Gross payout (local)" />
                        <x-text-input id="gross_payout_local" name="gross_payout_local" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('gross_payout_local')" required />
                    </div>
                    <div>
                        <x-input-label for="currency" value="Currency" />
                        <x-text-input id="currency" name="currency" maxlength="3" class="mt-1 block w-full uppercase" :value="old('currency', 'USD')" required />
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="s_views" value="S views (Shorts)" />
                        <x-text-input id="s_views" name="s_views" type="number" min="0" class="mt-1 block w-full" :value="old('s_views', 0)" required />
                    </div>
                    <div>
                        <x-input-label for="t_views" value="T views (TikTok total)" />
                        <x-text-input id="t_views" name="t_views" type="number" min="0" class="mt-1 block w-full" :value="old('t_views', 0)" required />
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="commission_rate_pct" value="Commission rate (%)" />
                        <x-text-input id="commission_rate_pct" name="commission_rate_pct" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full" :value="old('commission_rate_pct', 15)" required />
                    </div>
                    <div>
                        <x-input-label for="monthly_ops_fee" value="Monthly ops fee" />
                        <x-text-input id="monthly_ops_fee" name="monthly_ops_fee" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('monthly_ops_fee', 0)" required />
                    </div>
                </div>

                <div>
                    <x-input-label for="notes" value="Notes" />
                    <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('notes') }}</textarea>
                </div>

                <x-primary-button>Save settlement</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
