<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="ops-page-title">Edit {{ '@'.$creator->handle }}</h2>
            <p class="ops-page-subtitle">Onboarding profile and batch settings</p>
        </div>
    </x-slot>

    <div class="ops-page">
        <div class="ops-container-form">
            <x-ops-panel>
                <form method="POST" action="{{ route('operator.creators.update', $creator) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="tiktok_url" value="TikTok profile URL" />
                        <x-text-input id="tiktok_url" name="tiktok_url" type="url" class="mt-1 block w-full" :value="old('tiktok_url', $creator->tiktok_url)" required />
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tier" value="Tier" />
                            <select id="tier" name="tier" class="mt-1 ops-select">
                                @foreach ($tiers as $tier)
                                    <option value="{{ $tier->value }}" @selected(old('tier', $creator->tier->value) === $tier->value)>{{ $tier->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="music_policy" value="Music policy" />
                            <select id="music_policy" name="music_policy" class="mt-1 ops-select">
                                @foreach ($musicPolicies as $policy)
                                    <option value="{{ $policy->value }}" @selected(old('music_policy', $creator->music_policy->value) === $policy->value)>{{ $policy->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="last_run_date" value="Last batch run date" />
                        <x-text-input id="last_run_date" name="last_run_date" type="date" class="mt-1 block w-full" :value="old('last_run_date', $creator->last_run_date?->toDateString())" />
                    </div>

                    <div>
                        <x-input-label for="onboarding_notes" value="Onboarding notes" />
                        <textarea id="onboarding_notes" name="onboarding_notes" rows="3" class="mt-1 ops-input">{{ old('onboarding_notes', $creator->onboarding_notes) }}</textarea>
                    </div>

                    <x-primary-button>Update creator</x-primary-button>
                </form>
            </x-ops-panel>
        </div>
    </div>
</x-app-layout>
