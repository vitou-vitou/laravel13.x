<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="ops-page-title">Subscription</h2>
            <p class="ops-page-subtitle">Stripe Cashier · Track B software billing</p>
        </div>
    </x-slot>

    <div class="ops-page">
        <div class="ops-container-narrow ops-stack">
            <x-flash />

            @if (request('checkout') === 'success')
                <div class="ops-flash-success">
                    Checkout completed — your Pro subscription should activate shortly.
                </div>
            @endif

            <x-ops-panel>
                <div class="space-y-1">
                    <p class="text-sm text-stone-600">Effective plan: <strong class="text-stone-900">{{ $plan->label() }}</strong></p>
                    <p class="text-sm text-stone-600">Creators onboarded: <strong class="text-stone-900">{{ $creatorCount }}</strong> / {{ $plan->creatorLimit() }}</p>
                </div>
                <p class="text-sm text-stone-500 mt-3 leading-relaxed">Separate from creator commission settlement — this bills portal access for operators.</p>

                <div class="mt-6 flex flex-wrap gap-3 pt-4 border-t border-stone-100">
                    @if ($hasProSubscription)
                        <form method="POST" action="{{ route('settings.subscription.portal') }}">
                            @csrf
                            <x-primary-button>Manage in Stripe portal</x-primary-button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('settings.subscription.checkout') }}">
                            @csrf
                            <x-primary-button>Upgrade to Pro (Stripe Checkout)</x-primary-button>
                        </form>
                    @endif
                    <a href="{{ route('operator.billing.index') }}" class="ops-btn-secondary">Back to billing overview</a>
                </div>
            </x-ops-panel>
        </div>
    </div>
</x-app-layout>
