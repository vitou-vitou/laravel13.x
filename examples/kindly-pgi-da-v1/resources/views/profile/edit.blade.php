<x-app-layout>
    <x-store-page title="{{ __('Profile') }}" max="max-w-3xl">
        <div class="mt-6 space-y-6">
            <div class="store-panel">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="store-panel">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="store-panel">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </x-store-page>
</x-app-layout>
