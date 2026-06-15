<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="ops-page-title">Edit publish row</h2>
            <p class="ops-page-subtitle">{{ '@'.$creator->handle }} · steps 4–6</p>
        </div>
    </x-slot>

    <div class="ops-page">
        <div class="ops-container-form ops-stack">
            <x-flash />

            <x-batch-loop-rail :current="$entry->status->isPublishable() ? 6 : 4" />

            <x-ops-panel>
                <form method="POST" action="{{ route('operator.creators.publish-log.update', [$creator, $entry]) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center gap-3 pb-2 border-b border-stone-100">
                        <x-publish-status :status="$entry->status" />
                        <span class="text-sm text-stone-500">{{ '@'.$creator->handle }}</span>
                    </div>

                    <div>
                        <x-input-label for="logged_on" value="Log date" />
                        <x-text-input id="logged_on" name="logged_on" type="date" class="mt-1 block w-full" :value="old('logged_on', $entry->logged_on->toDateString())" required />
                    </div>

                    <div>
                        <x-input-label for="tiktok_url" value="TikTok URL" />
                        <x-text-input id="tiktok_url" name="tiktok_url" type="url" class="mt-1 block w-full" :value="old('tiktok_url', $entry->tiktok_url)" required />
                    </div>

                    <div>
                        <x-input-label for="title_variant" value="Title variant" />
                        <x-text-input id="title_variant" name="title_variant" class="mt-1 block w-full" :value="old('title_variant', $entry->title_variant)" />
                    </div>

                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 ops-select">
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $entry->status->value) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="yt_url" value="YouTube URL" />
                            <x-text-input id="yt_url" name="yt_url" type="url" class="mt-1 block w-full" :value="old('yt_url', $entry->yt_url)" />
                        </div>
                        <div>
                            <x-input-label for="ig_url" value="Instagram URL" />
                            <x-text-input id="ig_url" name="ig_url" type="url" class="mt-1 block w-full" :value="old('ig_url', $entry->ig_url)" />
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="yt_video_id" value="YouTube video ID" />
                            <x-text-input id="yt_video_id" name="yt_video_id" class="mt-1 block w-full" :value="old('yt_video_id', $entry->yt_video_id)" />
                        </div>
                        <div>
                            <x-input-label for="posted_time" value="Posted time" />
                            <x-text-input id="posted_time" name="posted_time" type="datetime-local" class="mt-1 block w-full" :value="old('posted_time', $entry->posted_time?->format('Y-m-d\TH:i'))" />
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="views_yt_7d" value="YouTube 7d views" />
                            <x-text-input id="views_yt_7d" name="views_yt_7d" type="number" min="0" class="mt-1 block w-full" :value="old('views_yt_7d', $entry->views_yt_7d)" />
                        </div>
                        <div>
                            <x-input-label for="views_ig_7d" value="Instagram 7d views" />
                            <x-text-input id="views_ig_7d" name="views_ig_7d" type="number" min="0" class="mt-1 block w-full" :value="old('views_ig_7d', $entry->views_ig_7d)" />
                        </div>
                    </div>

                    <fieldset class="ops-fieldset ops-fieldset--ig">
                        <legend class="ops-fieldset-legend">Instagram packaging (step 4)</legend>
                        <p class="text-xs text-pink-800/90">Use notes below for IG caption, hashtags, and cover frame checklist.</p>
                    </fieldset>

                    <div>
                        <x-input-label for="notes" value="Notes" />
                        <textarea id="notes" name="notes" rows="3" class="mt-1 ops-input">{{ old('notes', $entry->notes) }}</textarea>
                    </div>

                    <x-primary-button>Save changes</x-primary-button>
                </form>
            </x-ops-panel>

            @if ($entry->status->isPublishable())
                <x-ops-panel>
                    <form id="publish" method="POST" action="{{ route('operator.creators.publish-log.publish', [$creator, $entry]) }}" class="space-y-4">
                        @csrf
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4 -mx-1">
                            <h3 class="font-semibold text-emerald-900">Mark published (batch step 6)</h3>
                            <p class="text-xs text-emerald-800/80 mt-1">Sets status to published and updates last run date.</p>
                        </div>
                        <div>
                            <x-input-label for="publish_yt_url" value="YouTube URL (required)" />
                            <x-text-input id="publish_yt_url" name="yt_url" type="url" class="mt-1 block w-full" :value="old('yt_url', $entry->yt_url)" required />
                        </div>
                        <div>
                            <x-input-label for="publish_ig_url" value="Instagram URL" />
                            <x-text-input id="publish_ig_url" name="ig_url" type="url" class="mt-1 block w-full" :value="old('ig_url', $entry->ig_url)" />
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="publish_yt_video_id" value="YouTube video ID" />
                                <x-text-input id="publish_yt_video_id" name="yt_video_id" class="mt-1 block w-full" :value="old('yt_video_id', $entry->yt_video_id)" />
                            </div>
                            <div>
                                <x-input-label for="publish_posted_time" value="Posted time" />
                                <x-text-input id="publish_posted_time" name="posted_time" type="datetime-local" class="mt-1 block w-full" :value="old('posted_time', $entry->posted_time?->format('Y-m-d\TH:i'))" />
                            </div>
                        </div>
                        <button type="submit" class="ops-btn-success">Mark published</button>
                    </form>
                </x-ops-panel>
            @endif
        </div>
    </div>
</x-app-layout>
