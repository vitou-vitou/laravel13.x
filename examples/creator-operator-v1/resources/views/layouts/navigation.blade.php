<nav x-data="{ open: false }" class="ops-nav">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-14">
            <div class="flex items-center gap-8">
                <a href="{{ route('dashboard') }}" class="ops-nav-brand shrink-0">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-stone-900 text-[11px] font-bold text-white">CO</span>
                    <span class="hidden sm:inline">{{ config('app.name') }}</span>
                </a>

                <div class="hidden sm:flex sm:items-center sm:gap-1">
                    @if (Auth::user()->isOperator())
                        <span class="ops-nav-badge mr-2">Operator</span>
                        <x-nav-link :href="route('operator.dashboard')" :active="request()->routeIs('operator.dashboard')">
                            Batch queue
                        </x-nav-link>
                        <x-nav-link :href="route('operator.creators.index')" :active="request()->routeIs('operator.creators.*')">
                            Creators
                        </x-nav-link>
                        <x-nav-link :href="route('operator.billing.index')" :active="request()->routeIs('operator.billing.*')">
                            Billing
                        </x-nav-link>
                        <x-nav-link :href="route('operator.integrations.index')" :active="request()->routeIs('operator.integrations.*')">
                            Integrations
                        </x-nav-link>
                    @else
                        <span class="ops-nav-badge mr-2">Creator</span>
                        <x-nav-link :href="route('creator.approvals.index')" :active="request()->routeIs('creator.approvals.*')">
                            Approvals
                        </x-nav-link>
                        <x-nav-link :href="route('creator.reports.index')" :active="request()->routeIs('creator.reports.*')">
                            Reports
                        </x-nav-link>
                        <x-nav-link :href="route('creator.settlement.index')" :active="request()->routeIs('creator.settlement.*')">
                            Settlement
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium text-stone-600 hover:bg-stone-100 hover:text-stone-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 transition">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-stone-200 text-xs font-semibold text-stone-700">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                            <span>{{ Auth::user()->name }}</span>
                            <svg class="h-4 w-4 text-stone-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-lg text-stone-500 hover:text-stone-700 hover:bg-stone-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-stone-100 bg-white">
        <div class="pt-2 pb-3 px-2 space-y-0.5">
            @if (Auth::user()->isOperator())
                <x-responsive-nav-link :href="route('operator.dashboard')" :active="request()->routeIs('operator.dashboard')">
                    Batch queue
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('operator.creators.index')" :active="request()->routeIs('operator.creators.*')">
                    Creators
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('operator.billing.index')" :active="request()->routeIs('operator.billing.*')">
                    Billing
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('operator.integrations.index')" :active="request()->routeIs('operator.integrations.*')">
                    Integrations
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('creator.approvals.index')" :active="request()->routeIs('creator.approvals.*')">
                    Approvals
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('creator.reports.index')" :active="request()->routeIs('creator.reports.*')">
                    Reports
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('creator.settlement.index')" :active="request()->routeIs('creator.settlement.*')">
                    Settlement
                </x-responsive-nav-link>
            @endif
        </div>

        <div class="pt-3 pb-4 border-t border-stone-100 px-4">
            <div class="font-medium text-sm text-stone-900">{{ Auth::user()->name }}</div>
            <div class="text-xs text-stone-500">{{ Auth::user()->email }}</div>

            <div class="mt-3 space-y-0.5">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
