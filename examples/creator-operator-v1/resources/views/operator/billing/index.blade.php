<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="ops-page-title">Billing & plan</h2>
            <p class="ops-page-subtitle">Portal software billing — separate from creator commission</p>
        </div>
    </x-slot>

    <div class="ops-page">
        <div class="ops-container-narrow ops-stack">
            <x-flash />

            @if ($usesStripe)
                <div class="ops-flash-info">
                    Track B Stripe billing is active. Creator limits follow your Cashier subscription.
                    <a href="{{ route('settings.subscription') }}" class="ops-link ml-1">Manage subscription</a>
                </div>
            @else
                <div class="ops-flash-warn">
                    Track A mock billing — no Stripe charge. Set <code class="text-xs rounded bg-amber-100/80 px-1">OPERATOR_BILLING_MODE=stripe</code> and Stripe keys for live Checkout.
                </div>
            @endif

            <x-ops-panel>
                <div class="space-y-1">
                    <p class="text-sm text-stone-600">Current plan: <strong class="text-stone-900">{{ $plan->label() }}</strong></p>
                    <p class="text-sm text-stone-600">Creators onboarded: <strong class="text-stone-900">{{ $creatorCount }}</strong> / {{ $plan->creatorLimit() }}</p>
                </div>

                @if ($usesStripe)
                    <div class="mt-6 space-y-4 pt-4 border-t border-stone-100">
                        @if ($hasProSubscription)
                            <p class="text-sm text-stone-600">Pro subscription active via Stripe.</p>
                        @else
                            <p class="text-sm text-stone-600">On Starter limits until you upgrade via Stripe Checkout.</p>
                        @endif
                        <a href="{{ route('settings.subscription') }}" class="ops-btn-primary">Subscription settings</a>
                    </div>
                @else
                    <form method="POST" action="{{ route('operator.billing.plan') }}" class="mt-6 space-y-4 pt-4 border-t border-stone-100">
                        @csrf
                        @foreach ($plans as $key => $meta)
                            <label @class(['flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition-colors', 'border-indigo-500 bg-indigo-50/60 ring-1 ring-indigo-500/20' => $plan->value === $key, 'border-stone-200 hover:border-stone-300' => $plan->value !== $key])>
                                <input type="radio" name="operator_plan" value="{{ $key }}" @checked($plan->value === $key) class="mt-1 rounded border-stone-300 text-indigo-600 focus:ring-indigo-500">
                                <span>
                                    <span class="font-semibold text-stone-900">{{ $meta['label'] }}</span>
                                    <span class="text-stone-500 text-sm block mt-0.5">{{ $meta['description'] }}</span>
                                    <span class="text-stone-500 text-sm">Limit: {{ $meta['creator_limit'] }} creators · ${{ $meta['price_monthly'] }}/mo</span>
                                </span>
                            </label>
                        @endforeach
                        <x-primary-button>Update plan</x-primary-button>
                    </form>
                @endif
            </x-ops-panel>
        </div>
    </div>
</x-app-layout>
