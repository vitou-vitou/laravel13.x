@php($enabledSso = app(\App\Services\SsoAuthenticator::class)->enabledProviders())

@if (count($enabledSso) > 0)
    <div class="mb-4 flex flex-col gap-3" role="group" aria-label="{{ __('Sign-in options') }}">
        @foreach ($enabledSso as $provider)
            <x-sso-button
                :provider="$provider"
                :href="route('sso.redirect', ['provider' => $provider])"
            />
        @endforeach
    </div>

    <div class="relative mb-4">
        <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-gray-300"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="bg-white px-2 text-gray-500">{{ __('or') }}</span>
        </div>
    </div>
@endif
