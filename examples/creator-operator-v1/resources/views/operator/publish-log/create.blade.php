<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add publish row — {{ '@'.$creator->handle }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-batch-loop-rail :current="4" />

            <form method="POST" action="{{ route('operator.creators.publish-log.store', $creator) }}" class="bg-white shadow-sm rounded-lg p-6 space-y-4 border border-stone-100">
                @csrf

                <div>
                    <x-input-label for="logged_on" value="Log date" />
                    <x-text-input id="logged_on" name="logged_on" type="date" class="mt-1 block w-full" :value="old('logged_on', now()->toDateString())" required />
                </div>

                <div>
                    <x-input-label for="tiktok_url" value="TikTok video URL" />
                    <x-text-input id="tiktok_url" name="tiktok_url" type="url" class="mt-1 block w-full" :value="old('tiktok_url')" required />
                </div>

                <div>
                    <x-input-label for="title_variant" value="Proposed title (YT / SEO)" />
                    <x-text-input id="title_variant" name="title_variant" class="mt-1 block w-full" :value="old('title_variant')" />
                </div>

                <div>
                    <x-input-label for="notes" value="Packaging notes (YT description, IG caption, tags)" />
                    <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="IG: hook line 1…&#10;YT tags: …">{{ old('notes') }}</textarea>
                </div>

                <p class="text-sm text-stone-500">Saved as <x-publish-status :status="\App\Enums\PublishStatus::PendingApproval" class="inline-flex" /> — creator sees this in step 5 (approval inbox).</p>

                <x-primary-button>Save row</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
