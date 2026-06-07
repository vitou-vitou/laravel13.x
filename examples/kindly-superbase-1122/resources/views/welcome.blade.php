<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased" data-tab-persist="localStorage">
        <main class="mx-auto flex min-h-screen max-w-2xl flex-col justify-center px-6 py-12">
            <div class="rounded-xl border border-zinc-200 bg-white shadow-sm">
                <div
                    class="sticky top-0 z-10 flex shrink-0 border-b border-zinc-200 overflow-x-auto bg-white"
                    role="tablist"
                    aria-label="Content tabs"
                >
                    <button
                        type="button"
                        role="tab"
                        id="tab-tab1"
                        aria-selected="true"
                        aria-controls="panel-tab1"
                        data-tab="tab1"
                        class="tab-trigger shrink-0 border-b-2 border-zinc-900 px-6 py-3 text-sm font-medium text-zinc-900"
                    >
                        tab1
                    </button>
                    <button
                        type="button"
                        role="tab"
                        id="tab-tab2"
                        aria-selected="false"
                        aria-controls="panel-tab2"
                        data-tab="tab2"
                        class="tab-trigger shrink-0 border-b-2 border-transparent px-6 py-3 text-sm font-medium text-zinc-500 hover:text-zinc-700"
                    >
                        tab2
                    </button>
                    <button
                        type="button"
                        role="tab"
                        id="tab-supabase"
                        aria-selected="false"
                        aria-controls="panel-supabase"
                        data-tab="supabase"
                        class="tab-trigger shrink-0 border-b-2 border-transparent px-6 py-3 text-sm font-medium text-zinc-500 hover:text-zinc-700"
                        data-supabase-tab
                    >
                        <span>supabase</span>
                        <span data-supabase-tab-loading class="ml-1 hidden text-xs font-normal text-sky-600">checking…</span>
                    </button>
                    <button
                        type="button"
                        role="tab"
                        id="tab-logs"
                        aria-selected="false"
                        aria-controls="panel-logs"
                        data-tab="logs"
                        class="tab-trigger shrink-0 border-b-2 border-transparent px-6 py-3 text-sm font-medium text-zinc-500 hover:text-zinc-700"
                    >
                        logs
                    </button>
                </div>

                <div class="p-6">
                    <div class="min-h-[30rem] overflow-y-auto" data-tab-panel-scroll>
                    <section
                        id="panel-tab1"
                        role="tabpanel"
                        aria-labelledby="tab-tab1"
                        class="tab-panel text-sm leading-7 text-zinc-700"
                    >
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam. Sed nisi. Nulla quis sem at nibh elementum imperdiet.
                    </section>
                    <section
                        id="panel-tab2"
                        role="tabpanel"
                        aria-labelledby="tab-tab2"
                        class="tab-panel hidden text-sm leading-7 text-zinc-700"
                    >
                        Sed ut perspiciatis unde omnis iste natus error sit voluptatem. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.
                    </section>
                    <section
                        id="panel-supabase"
                        role="tabpanel"
                        aria-labelledby="tab-supabase"
                        class="tab-panel hidden space-y-4 text-sm text-zinc-700"
                        data-supabase-panel
                    >
                        <div class="flex gap-2 border-b border-zinc-200 pb-3" role="tablist" aria-label="Supabase features">
                            <button
                                type="button"
                                role="tab"
                                id="supabase-sub-health"
                                aria-selected="true"
                                aria-controls="supabase-panel-health"
                                data-supabase-sub="health"
                                class="supabase-sub-trigger rounded-md bg-zinc-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-zinc-800"
                            >
                                health
                            </button>
                        </div>

                        <div
                            id="supabase-panel-health"
                            role="tabpanel"
                            aria-labelledby="supabase-sub-health"
                            class="supabase-sub-panel"
                        >
                            @include('partials.supabase-health')
                        </div>
                    </section>
                    <section
                        id="panel-logs"
                        role="tabpanel"
                        aria-labelledby="tab-logs"
                        class="tab-panel hidden space-y-4 text-sm text-zinc-700"
                        data-logs-panel
                        data-activity-logs-url="{{ route('activity-logs.index') }}"
                        data-log-page-limit="{{ \App\Services\ActivityLogBoard::SHOW_MORE_LIMIT }}"
                        data-log-default-limit="{{ \App\Services\ActivityLogBoard::DEFAULT_LIMIT }}"
                    >
                        @include('partials.activity-logs-board', ['activityLogs' => $activityLogs, 'logBoard' => $logBoard])
                    </section>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
