<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Billing & plan</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="rounded-lg border border-amber-100 bg-amber-50 p-4 text-sm text-amber-900">
                Track A mock billing — no Stripe charge. Live Checkout is out of scope for W1–W6.
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6 border border-stone-100">
                <p class="text-sm text-stone-600">Current plan: <strong>{{ $plan->label() }}</strong></p>
                <p class="text-sm text-stone-600 mt-1">Creators onboarded: <strong>{{ $creatorCount }}</strong> / {{ $plan->creatorLimit() }}</p>

                <form method="POST" action="{{ route('operator.billing.plan') }}" class="mt-6 space-y-4">
                    @csrf
                    @foreach ($plans as $key => $meta)
                        <label class="flex items-start gap-3 p-4 rounded-lg border @if($plan->value === $key) border-indigo-500 bg-indigo-50 @else border-stone-200 @endif">
                            <input type="radio" name="operator_plan" value="{{ $key }}" @checked($plan->value === $key) class="mt-1">
                            <span>
                                <span class="font-medium">{{ $meta['label'] }}</span>
                                <span class="text-stone-500 text-sm block">{{ $meta['description'] }}</span>
                                <span class="text-stone-500 text-sm">Limit: {{ $meta['creator_limit'] }} creators · ${{ $meta['price_monthly'] }}/mo</span>
                            </span>
                        </label>
                    @endforeach
                    <x-primary-button>Update plan</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
