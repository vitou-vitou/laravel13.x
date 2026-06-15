<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="ops-page-title">Add weekly metrics</h2>
            <p class="ops-page-subtitle">{{ '@'.$creator->handle }} · step 7 REPORT</p>
        </div>
    </x-slot>

    <div class="ops-page">
        <div class="ops-container-form ops-stack">
            <x-creator-hub-nav :creator="$creator" />

            <x-ops-panel>
                <form method="POST" action="{{ route('operator.creators.metrics.store', $creator) }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="week_start" value="Week start" />
                        <x-text-input id="week_start" name="week_start" type="date" class="mt-1 block w-full" :value="old('week_start', now()->startOfWeek()->toDateString())" required />
                    </div>

                    <div>
                        <x-input-label for="videos_published" value="Videos published" />
                        <x-text-input id="videos_published" name="videos_published" type="number" min="0" class="mt-1 block w-full" :value="old('videos_published', 0)" required />
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="best_video_url" value="Best video URL" />
                            <x-text-input id="best_video_url" name="best_video_url" type="url" class="mt-1 block w-full" :value="old('best_video_url')" />
                        </div>
                        <div>
                            <x-input-label for="best_video_views" value="Best video views" />
                            <x-text-input id="best_video_views" name="best_video_views" type="number" min="0" class="mt-1 block w-full" :value="old('best_video_views')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="experiment" value="Experiment" />
                        <x-text-input id="experiment" name="experiment" class="mt-1 block w-full" :value="old('experiment')" />
                    </div>

                    <div>
                        <x-input-label for="experiment_result" value="Experiment result" />
                        <x-text-input id="experiment_result" name="experiment_result" class="mt-1 block w-full" :value="old('experiment_result')" />
                    </div>

                    <div>
                        <x-input-label for="operator_notes" value="Operator notes" />
                        <textarea id="operator_notes" name="operator_notes" rows="3" class="mt-1 ops-input">{{ old('operator_notes') }}</textarea>
                    </div>

                    <x-primary-button>Save metrics</x-primary-button>
                </form>
            </x-ops-panel>
        </div>
    </div>
</x-app-layout>
