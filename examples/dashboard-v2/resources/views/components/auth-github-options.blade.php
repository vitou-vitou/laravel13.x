@php($githubEnabled = app(\App\Services\GitHubAuthenticator::class)->isEnabled())

@if ($githubEnabled)
    <div class="mb-4" role="group" aria-label="{{ __('Sign-in options') }}">
        <x-github-login-button :href="route('github.redirect')" />
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
