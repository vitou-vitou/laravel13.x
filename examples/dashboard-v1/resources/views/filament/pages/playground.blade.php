<x-filament-panels::page>
    @php($snapshot = $this->getSnapshot())

    <div class="grid gap-6 lg:grid-cols-2">
        <x-filament::section heading="Environment" description="Local dev signals for this app.">
            <dl class="grid gap-3 text-sm">
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">APP_URL</dt>
                    <dd class="font-mono text-right">{{ $snapshot['app_url'] }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Vite dev</dt>
                    <dd>
                        @if ($snapshot['vite_dev'])
                            <x-filament::badge color="success">HMR active</x-filament::badge>
                            <div class="mt-1 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $snapshot['vite_origin'] }}</div>
                        @else
                            <x-filament::badge color="gray">Built assets</x-filament::badge>
                        @endif
                    </dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Broadcast</dt>
                    <dd class="font-mono">{{ $snapshot['broadcast'] }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Reverb</dt>
                    <dd class="font-mono">{{ $snapshot['reverb_host'] }}:{{ $snapshot['reverb_port'] }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Queue</dt>
                    <dd class="font-mono">{{ $snapshot['queue'] }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Tunnel admin</dt>
                    <dd>
                        <x-filament::badge :color="$snapshot['tunnel_admin'] ? 'success' : 'gray'">
                            {{ $snapshot['tunnel_admin'] ? 'Enabled' : 'Disabled' }}
                        </x-filament::badge>
                    </dd>
                </div>
            </dl>
        </x-filament::section>

        <x-filament::section heading="Quick links" description="Open the storefront and dashboard in another tab.">
            <ul class="space-y-4">
                @foreach ($this->getQuickLinks() as $link)
                    <li class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                        <a
                            href="{{ $link['url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="font-medium text-primary-600 hover:underline dark:text-primary-400"
                        >
                            {{ $link['label'] }}
                        </a>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $link['description'] }}</p>
                    </li>
                @endforeach
            </ul>
        </x-filament::section>

        <x-filament::section
            class="lg:col-span-2"
            heading="Echo broadcast demo"
            description="Keep /dashboard open in another tab, then use Fire test broadcast above. KPIs and the orders table should update without refresh when Reverb is running."
        >
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Requires <code class="rounded bg-gray-100 px-1 py-0.5 font-mono text-xs dark:bg-white/10">php artisan reverb:start</code>
                and a seeded order. Place new orders from <strong>Shop</strong> for end-to-end checkout + broadcast flow.
            </p>
        </x-filament::section>
    </div>
</x-filament-panels::page>
