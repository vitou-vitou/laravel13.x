<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="ops-page-title">Approval inbox</h2>
            <p class="ops-page-subtitle">{{ '@'.$creator->handle }} · review packaged videos before publish</p>
        </div>
    </x-slot>

    <div class="ops-page ops-page--approvals">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 ops-stack">
            <x-batch-loop-rail :current="5" />

            <x-flash />

            <x-ops-panel title="Pending your approval">
                @forelse ($pending as $entry)
                    <div class="ops-approval-card">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <x-tiktok-thumb
                                :url="$entry->tiktok_url"
                                :thumbnail="$entry->tiktok_thumbnail_url"
                                :title="$entry->title_variant"
                                size="lg"
                                class="shrink-0"
                            />
                            <div class="space-y-2 min-w-0 flex-1">
                                <div class="font-semibold text-stone-900">{{ $entry->title_variant ?? 'Untitled package' }}</div>
                                <div class="text-sm text-stone-500 tabular-nums">Logged {{ $entry->logged_on->toDateString() }}</div>
                                <a href="{{ $entry->tiktok_url }}" class="ops-link text-sm inline-flex items-center gap-1" target="_blank" rel="noopener">
                                    TikTok source
                                    <span aria-hidden="true">↗</span>
                                </a>
                                @if ($entry->notes)
                                    <p class="text-sm text-stone-600 mt-2 leading-relaxed rounded-lg bg-stone-50 border border-stone-100 p-3">{{ $entry->notes }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="ops-approval-actions">
                            <form method="POST" action="{{ route('creator.approvals.approve', $entry) }}" class="flex-1 sm:flex-none">
                                @csrf
                                <x-primary-button class="ops-btn-touch w-full sm:w-auto">Approve</x-primary-button>
                            </form>
                            <form method="POST" action="{{ route('creator.approvals.reject', $entry) }}" class="flex-1 sm:flex-none">
                                @csrf
                                <x-secondary-button class="ops-btn-touch w-full sm:w-auto">Skip</x-secondary-button>
                            </form>
                        </div>
                    </div>
                @empty
                    <x-empty-state title="Nothing waiting">
                        Your operator will notify you when the next batch is ready for review.
                    </x-empty-state>
                @endforelse
            </x-ops-panel>

            @if ($recent->isNotEmpty())
                <x-ops-panel title="Recent decisions">
                    <table class="ops-table">
                        <tbody>
                            @foreach ($recent as $entry)
                                <tr>
                                    <td class="w-14">
                                        <x-tiktok-thumb
                                            :url="$entry->tiktok_url"
                                            :thumbnail="$entry->tiktok_thumbnail_url"
                                            size="sm"
                                        />
                                    </td>
                                    <td class="font-medium">{{ $entry->title_variant ?? '—' }}</td>
                                    <td><x-publish-status :status="$entry->status" /></td>
                                    <td class="text-stone-500">{{ $entry->updated_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-ops-panel>
            @endif
        </div>
    </div>
</x-app-layout>
