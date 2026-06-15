<x-app-layout>
    <x-slot name="header">
        <h2 class="ops-page-title">{{ __('Profile') }}</h2>
    </x-slot>

    <div class="ops-page">
        <div class="ops-container-narrow ops-stack">
            <x-ops-panel>
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </x-ops-panel>

            <x-ops-panel>
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </x-ops-panel>

            <x-ops-panel>
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </x-ops-panel>
        </div>
    </div>
</x-app-layout>
