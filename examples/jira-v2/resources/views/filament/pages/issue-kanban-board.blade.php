<x-filament-panels::page>
    <div class="mb-6 flex flex-wrap items-end gap-4">
        <div class="min-w-[16rem]">
            <label for="projectId" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Project
            </label>
            <select
                id="projectId"
                wire:model.live="projectId"
                class="fi-input block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
            >
                <option value="">All projects</option>
                @foreach ($this->projects as $project)
                    <option value="{{ $project->id }}">{{ $project->icon }} {{ $project->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-4">
        @foreach ($columns as $status => $column)
            <div class="flex min-h-[24rem] flex-col rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40">
                <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $column['label'] }}
                        </h3>
                        <span class="rounded-full bg-gray-200 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            {{ $this->issuesByStatus[$status]->count() }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-1 flex-col gap-3 p-3">
                    @forelse ($this->issuesByStatus[$status] as $issue)
                        <article
                            wire:key="issue-{{ $issue->id }}"
                            class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-950"
                        >
                            <div class="mb-2 flex items-start justify-between gap-2">
                                <a
                                    href="{{ \App\Filament\Resources\IssueResource::getUrl('view', ['record' => $issue]) }}"
                                    class="text-xs font-semibold text-primary-600 hover:underline dark:text-primary-400"
                                >
                                    {{ $issue->key }}
                                </a>
                                <span @class([
                                    'rounded px-1.5 py-0.5 text-[10px] font-medium uppercase tracking-wide',
                                    'bg-danger-100 text-danger-700 dark:bg-danger-500/20 dark:text-danger-300' => $issue->type === 'bug',
                                    'bg-primary-100 text-primary-700 dark:bg-primary-500/20 dark:text-primary-300' => $issue->type === 'story',
                                    'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' => ! in_array($issue->type, ['bug', 'story']),
                                ])>
                                    {{ $issue->type }}
                                </span>
                            </div>

                            <p class="mb-3 text-sm font-medium text-gray-900 dark:text-white">
                                {{ $issue->title }}
                            </p>

                            <div class="mb-3 flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                                @if ($issue->assignee)
                                    <span>{{ $issue->assignee }}</span>
                                @endif
                                @if ($issue->story_points)
                                    <span>{{ $issue->story_points }} pts</span>
                                @endif
                                <span class="capitalize">{{ str_replace('_', ' ', $issue->priority) }}</span>
                            </div>

                            <div class="flex flex-wrap gap-1">
                                @foreach ($columns as $targetStatus => $targetColumn)
                                    @if ($targetStatus !== $status)
                                        <button
                                            type="button"
                                            wire:click="moveIssue({{ $issue->id }}, '{{ $targetStatus }}')"
                                            class="rounded-md border border-gray-200 px-2 py-1 text-[11px] font-medium text-gray-600 transition hover:border-primary-300 hover:text-primary-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-primary-500 dark:hover:text-primary-400"
                                        >
                                            → {{ $targetColumn['label'] }}
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        </article>
                    @empty
                        <p class="py-8 text-center text-sm text-gray-400 dark:text-gray-500">
                            No issues
                        </p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
