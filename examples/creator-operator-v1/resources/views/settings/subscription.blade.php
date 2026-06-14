<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Subscription</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (request('checkout') === 'success')
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">
                    Checkout completed — your Pro subscription should activate shortly.
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg p-6 border border-stone-100">
                <p class="text-sm text-stone-600">Effective plan: <strong>{{ $plan->label() }}</strong></p>
                <p class="text-sm text-stone-600 mt-1">Creators onboarded: <strong>{{ $creatorCount }}</strong> / {{ $plan->creatorLimit() }}</p>
                <p class="text-sm text-stone-500 mt-2">Track B — Stripe Cashier (test mode). Software billing for portal access, separate from creator commission settlement.</p>

                <div class="mt-6 flex flex-wrap gap-3">
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
                    <a href="{{ route('operator.billing.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-stone-300 rounded-md font-semibold text-xs text-stone-700 uppercase tracking-widest hover:bg-stone-50">
                        Back to billing overview
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
