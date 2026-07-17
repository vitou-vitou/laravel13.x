<div
    x-data
    class="inline-flex items-center gap-0.5 rounded-lg bg-gray-200 p-1 ring-1 ring-inset ring-gray-300/80 dark:bg-gray-800 dark:ring-gray-600/60"
    role="group"
    aria-label="{{ __('Theme') }}"
>
    <button
        type="button"
        @click="$store.theme.set('light')"
        :class="$store.theme.mode === 'light'
            ? 'bg-white font-semibold text-gray-950 shadow-sm ring-1 ring-inset ring-gray-300 dark:bg-gray-600 dark:text-white dark:ring-gray-500 dark:shadow-md'
            : 'font-normal text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
        class="rounded-md px-3 py-1.5 text-center text-xs transition-all duration-150 ease-out focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900"
        :aria-pressed="$store.theme.mode === 'light'"
    >
        {{ __('Light') }}
    </button>

    <button
        type="button"
        @click="$store.theme.set('dark')"
        :class="$store.theme.mode === 'dark'
            ? 'bg-white font-semibold text-gray-950 shadow-sm ring-1 ring-inset ring-gray-300 dark:bg-gray-600 dark:text-white dark:ring-gray-500 dark:shadow-md'
            : 'font-normal text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
        class="rounded-md px-3 py-1.5 text-center text-xs transition-all duration-150 ease-out focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900"
        :aria-pressed="$store.theme.mode === 'dark'"
    >
        {{ __('Dark') }}
    </button>

    <button
        type="button"
        @click="$store.theme.set('system')"
        :class="$store.theme.mode === 'system'
            ? 'bg-white font-semibold text-gray-950 shadow-sm ring-1 ring-inset ring-gray-300 dark:bg-gray-600 dark:text-white dark:ring-gray-500 dark:shadow-md'
            : 'font-normal text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
        class="rounded-md px-3 py-1.5 text-center text-xs transition-all duration-150 ease-out focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900"
        :aria-pressed="$store.theme.mode === 'system'"
    >
        {{ __('Auto') }}
    </button>
</div>
