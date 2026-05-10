@php
    $consent = request()->cookie('cookie_consent');
@endphp

@if (! $consent)
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-zinc-200 bg-white p-4 shadow-lg">
        <div class="mx-auto flex max-w-5xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-zinc-700">
                {{ __('cookie.message') }}
                <a href="{{ route('privacy') }}" class="font-medium underline">{{ __('nav.privacy') }}</a>
            </p>
            <form method="POST" action="{{ route('cookie.consent') }}" class="flex shrink-0 gap-2">
                @csrf
                <button type="submit" class="rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800">
                    {{ __('cookie.accept') }}
                </button>
            </form>
        </div>
    </div>
@endif
