@props(['active' => null])

@php
    $tabs = config('fb-nav.primary_tabs');
    $activeId = $active ?? request()->route()?->getName();
@endphp

<header role="banner" class="sticky top-0 z-50 border-b border-black/20 bg-fb-nav text-fb-icon shadow-sm">
    <div class="mx-auto grid h-14 max-w-[1260px] grid-cols-[auto_1fr_auto] items-center gap-2 px-2 sm:px-4">
        {{-- Left: logo + search --}}
        <div class="flex items-center gap-2">
            <a
                href="{{ route('home') }}"
                aria-label="Facebook"
                class="flex size-10 shrink-0 items-center justify-center rounded-full bg-fb-blue text-lg font-bold text-white"
            >
                f
            </a>
            <button
                type="button"
                aria-label="Search Facebook"
                class="flex size-10 items-center justify-center rounded-full bg-fb-icon-btn transition-colors hover:bg-[#4e4f50]"
            >
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" />
                    <path d="M20 20l-3.5-3.5" stroke-linecap="round" />
                </svg>
            </button>
        </div>

        {{-- Center: primary tabs --}}
        <nav aria-label="Facebook" class="flex h-14 min-w-0 items-stretch justify-center gap-0.5 sm:gap-1">
            @foreach ($tabs as $tab)
                @php
                    $isActive = $activeId === $tab['route'];
                @endphp
                <a
                    href="{{ route($tab['route']) }}"
                    data-nav-tab="{{ $tab['id'] }}"
                    data-active="{{ $isActive ? 'true' : 'false' }}"
                    @if ($isActive) aria-current="page" @endif
                    class="group relative flex min-w-0 flex-1 max-w-[112px] flex-col items-center justify-center px-1 sm:max-w-[140px] sm:px-3"
                >
                    <span @class([
                        'flex size-7 items-center justify-center sm:size-8',
                        'text-fb-blue' => $isActive,
                        'text-fb-icon group-hover:bg-white/5 rounded-lg' => ! $isActive,
                    ])>
                        @include('components.fb-nav-icons.'.$tab['id'], ['active' => $isActive])
                    </span>
                    <span @class([
                        'mt-0.5 hidden text-[11px] font-medium leading-none sm:block',
                        'text-fb-blue' => $isActive,
                        'text-fb-icon' => ! $isActive,
                    ])>{{ $tab['label'] }}</span>
                    @if ($isActive)
                        <span class="absolute inset-x-2 bottom-0 h-[3px] rounded-t-sm bg-fb-blue" aria-hidden="true"></span>
                    @endif
                </a>
            @endforeach
        </nav>

        {{-- Right: utilities + profile --}}
        <div class="flex items-center justify-end gap-1 sm:gap-2">
            <button type="button" aria-label="Menu" class="flex size-10 items-center justify-center rounded-full bg-fb-icon-btn text-fb-icon transition-colors hover:bg-[#4e4f50]">
                <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <circle cx="5" cy="5" r="1.5" /><circle cx="12" cy="5" r="1.5" /><circle cx="19" cy="5" r="1.5" />
                    <circle cx="5" cy="12" r="1.5" /><circle cx="12" cy="12" r="1.5" /><circle cx="19" cy="12" r="1.5" />
                    <circle cx="5" cy="19" r="1.5" /><circle cx="12" cy="19" r="1.5" /><circle cx="19" cy="19" r="1.5" />
                </svg>
            </button>
            <button type="button" aria-label="Messenger" class="flex size-10 items-center justify-center rounded-full bg-fb-icon-btn text-fb-icon transition-colors hover:bg-[#4e4f50]">
                <svg class="size-6" viewBox="0 0 36 36" fill="currentColor" aria-hidden="true">
                    <path d="M12 4c-4.4 0-8 3.1-8 7.5 0 2.3 1.1 4.4 2.9 5.8L5 24l5.8-2.2c1 .3 2.1.5 3.2.5 4.4 0 8-3.1 8-7.5S16.4 4 12 4zm12 6c-4.4 0-8 3.1-8 7.5 0 2.3 1.1 4.4 2.9 5.8L17 30l5.8-2.2c1 .3 2.1.5 3.2.5 4.4 0 8-3.1 8-7.5S28.4 10 24 10z"/>
                </svg>
            </button>
            <button type="button" aria-label="Notifications" class="flex size-10 items-center justify-center rounded-full bg-fb-icon-btn text-fb-icon transition-colors hover:bg-[#4e4f50]">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M15 17H9c-3 0-5-2-5-5V9a5 5 0 0 1 10 0v3c0 3-2 5-5 5z" stroke-linecap="round" />
                    <path d="M9.5 17.5a2.5 2.5 0 0 0 5 0" stroke-linecap="round" />
                </svg>
            </button>
            <button type="button" aria-label="Account menu" class="relative flex shrink-0 items-center">
                <img
                    src="https://api.dicebear.com/7.x/avataaars/svg?seed=luffy"
                    alt=""
                    class="size-9 rounded-full border border-white/10 object-cover"
                />
                <span class="absolute -bottom-0.5 -right-0.5 flex size-4 items-center justify-center rounded-full bg-fb-icon-btn ring-2 ring-fb-nav" aria-hidden="true">
                    <svg class="size-2.5 text-fb-icon" viewBox="0 0 12 12" fill="currentColor">
                        <path d="M2 4.5 6 8.5l4-4" />
                    </svg>
                </span>
            </button>
        </div>
    </div>
</header>
