<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta
            name="description"
            content="Laravel starter with Filament: landing page before admin panels and routes are connected."
        >
        <meta name="theme-color" content="#141311">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link
            href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700&display=swap"
            rel="stylesheet"
        >
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        class="font-outfit relative min-h-dvh overflow-x-hidden bg-welcome-bg text-welcome-body antialiased selection:bg-welcome-selection selection:text-welcome-fg"
    >
        <div
            class="pointer-events-none fixed inset-0 z-0 mix-blend-soft-light opacity-[0.04]"
            style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22n%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.85%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23n)%22/%3E%3C/svg%3E');"
            aria-hidden="true"
        ></div>

        <a
            href="#main"
            class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:inline-flex focus:min-h-11 focus:items-center focus:rounded-md focus:bg-welcome-elevated focus:px-4 focus:py-2.5 focus:text-sm focus:font-medium focus:text-welcome-fg focus:outline-none focus:ring-2 focus:ring-welcome-accent/50 focus:ring-offset-2 focus:ring-offset-welcome-bg"
        >Skip to content</a>

        <div
            class="relative z-10 flex min-h-dvh flex-col justify-center px-6 py-[4.25rem] sm:px-10 sm:py-20 md:px-16 md:py-24 lg:px-24 lg:py-[6.5rem]"
        >
            <main id="main" class="max-w-[42rem]">
                <p
                    class="mb-[1.125rem] text-[0.8125rem] font-medium uppercase tracking-[0.2em] text-welcome-subtle"
                >
                    {{ config('app.name', 'Laravel') }}
                </p>
                <h1
                    class="text-balance text-4xl font-semibold leading-[1.05] tracking-[-0.038em] text-welcome-fg motion-reduce:animate-none motion-reduce:opacity-100 sm:text-5xl lg:text-[3.5rem] lg:leading-[1.04] animate-welcome"
                >
                    welcome to basic laravel filamentphp
                </h1>
                <p
                    class="mt-9 max-w-[34rem] text-pretty text-lg font-normal leading-[1.65] text-welcome-muted motion-reduce:animate-none motion-reduce:opacity-100 sm:mt-10 sm:max-w-[36rem] animate-welcome-delay"
                >
                    Install and configure panels using
                    <a
                        href="https://filamentphp.com/docs/panels/installation"
                        class="relative z-10 inline-block py-1.5 font-medium text-welcome-accent underline decoration-welcome-accent/40 underline-offset-[0.22em] transition-colors duration-200 ease-out [-webkit-tap-highlight-color:transparent] hover:text-welcome-accent-hover hover:decoration-welcome-accent/60 active:translate-y-px motion-reduce:transition-none focus:outline-none focus-visible:rounded-sm focus-visible:ring-2 focus-visible:ring-welcome-accent/50 focus-visible:ring-offset-2 focus-visible:ring-offset-welcome-bg"
                    >the Filament panel docs</a>.
                    Add your first admin route when the app is ready.
                </p>
                @if (Route::has('filament.admin.pages.dashboard'))
                    <p
                        class="mt-8 max-w-[36rem] motion-reduce:animate-none motion-reduce:opacity-100 sm:mt-9 animate-welcome-delay-2"
                    >
                        <a
                            href="{{ route('filament.admin.pages.dashboard') }}"
                            class="inline-flex min-h-11 items-center justify-center rounded-md bg-welcome-accent px-6 py-3 text-sm font-semibold text-welcome-bg [-webkit-tap-highlight-color:transparent] transition-colors duration-200 ease-out hover:bg-welcome-accent-hover active:translate-y-px motion-reduce:transition-none focus:outline-none focus-visible:ring-2 focus-visible:ring-welcome-accent/50 focus-visible:ring-offset-2 focus-visible:ring-offset-welcome-bg"
                        >Open admin</a>
                    </p>
                @endif
            </main>

            <footer
                class="mt-[4.5rem] max-w-[42rem] border-t border-welcome-border pt-7 text-xs font-medium text-welcome-footer motion-reduce:animate-none motion-reduce:opacity-100 sm:pt-8 md:mt-28 animate-welcome-delay-3"
            >
                <span class="tabular-nums">PHP {{ PHP_VERSION }}</span>
                <span class="mx-2 text-welcome-divider" aria-hidden="true">·</span>
                <span class="tabular-nums">Laravel v{{ app()->version() }}</span>
            </footer>
        </div>
    </body>
</html>
