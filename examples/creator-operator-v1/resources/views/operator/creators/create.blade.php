<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="ops-page-title">Onboard creator</h2>
            <p class="ops-page-subtitle">Week 0 onboarding · pilot checklist</p>
        </div>
    </x-slot>

    <div class="ops-page">
        <div class="ops-container-form">
            <x-ops-panel>
                <form method="POST" action="{{ route('operator.creators.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="handle" value="TikTok handle" />
                        <x-text-input id="handle" name="handle" class="mt-1 block w-full" :value="old('handle')" required placeholder="creatorhandle" />
                        <x-input-error :messages="$errors->get('handle')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="tiktok_url" value="TikTok profile URL" />
                        <x-text-input id="tiktok_url" name="tiktok_url" type="url" class="mt-1 block w-full" :value="old('tiktok_url')" required />
                        <x-input-error :messages="$errors->get('tiktok_url')" class="mt-2" />
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tier" value="Tier" />
                            <select id="tier" name="tier" class="mt-1 ops-select">
                                @foreach ($tiers as $tier)
                                    <option value="{{ $tier->value }}" @selected(old('tier') === $tier->value)>{{ $tier->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="music_policy" value="Music policy" />
                            <select id="music_policy" name="music_policy" class="mt-1 ops-select">
                                @foreach ($musicPolicies as $policy)
                                    <option value="{{ $policy->value }}" @selected(old('music_policy') === $policy->value)>{{ $policy->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="youtube_manager_email" value="YouTube manager email" />
                            <x-text-input id="youtube_manager_email" name="youtube_manager_email" type="email" class="mt-1 block w-full" :value="old('youtube_manager_email')" />
                        </div>
                        <div>
                            <x-input-label for="meta_manager_email" value="Meta manager email" />
                            <x-text-input id="meta_manager_email" name="meta_manager_email" type="email" class="mt-1 block w-full" :value="old('meta_manager_email')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="onboarding_notes" value="Onboarding notes" />
                        <textarea id="onboarding_notes" name="onboarding_notes" rows="3" class="mt-1 ops-input">{{ old('onboarding_notes') }}</textarea>
                    </div>

                    <fieldset class="ops-fieldset">
                        <legend class="ops-fieldset-legend">Optional creator login</legend>
                        <div class="space-y-3">
                            <div>
                                <x-input-label for="creator_name" value="Creator name" />
                                <x-text-input id="creator_name" name="creator_name" class="mt-1 block w-full" :value="old('creator_name')" />
                            </div>
                            <div>
                                <x-input-label for="creator_email" value="Creator email (password: password)" />
                                <x-text-input id="creator_email" name="creator_email" type="email" class="mt-1 block w-full" :value="old('creator_email')" />
                                <x-input-error :messages="$errors->get('creator_email')" class="mt-2" />
                            </div>
                        </div>
                    </fieldset>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <x-primary-button>Save creator</x-primary-button>
                        <a href="{{ route('operator.creators.index') }}" class="ops-link-muted self-center text-sm">Cancel</a>
                    </div>
                </form>
            </x-ops-panel>
        </div>
    </div>
</x-app-layout>
