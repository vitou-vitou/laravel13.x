<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit publish row</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-batch-loop-rail :current="$entry->status->isPublishable() ? 6 : 4" />

            <form method="POST" action="{{ route('operator.creators.publish-log.update', [$creator, $entry]) }}" class="bg-white shadow-sm rounded-lg p-6 space-y-4 border border-stone-100">
                @csrf
                @method('PUT')

                <div class="flex items-center gap-2">
                    <x-publish-status :status="$entry->status" />
                    <span class="text-sm text-gray-500">{{ '@'.$creator->handle }}</span>
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
                    <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
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

                <fieldset class="rounded-lg border border-pink-100 bg-pink-50/50 p-4 space-y-2">
                    <legend class="text-sm font-medium text-pink-900 px-1">Instagram packaging (step 4)</legend>
                    <p class="text-xs text-pink-800">Use notes below for IG caption, hashtags, and cover frame checklist — paste from your packaging doc.</p>
                </fieldset>

                <div>
                    <x-input-label for="notes" value="Notes" />
                    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('notes', $entry->notes) }}</textarea>
                </div>

                <x-primary-button>Save changes</x-primary-button>
            </form>

            @if ($entry->status->isPublishable())
                <form id="publish" method="POST" action="{{ route('operator.creators.publish-log.publish', [$creator, $entry]) }}" class="bg-emerald-50 border border-emerald-200 rounded-lg p-6 space-y-4">
                    @csrf
                    <h3 class="font-medium text-emerald-900">Mark published (batch step 6)</h3>
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
                    <x-primary-button class="bg-emerald-700 hover:bg-emerald-800">Mark published</x-primary-button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
