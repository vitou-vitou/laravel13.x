<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="ops-page-title">Add publish row</h2>
            <p class="ops-page-subtitle">{{ '@'.$creator->handle }} · step 4 packaging</p>
        </div>
    </x-slot>

    <div class="ops-page">
        <div class="ops-container-form ops-stack">
            <x-batch-loop-rail :current="4" />

            <x-ops-panel>
                <form method="POST" action="{{ route('operator.creators.publish-log.store', $creator) }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="logged_on" value="Log date" />
                        <x-text-input id="logged_on" name="logged_on" type="date" class="mt-1 block w-full" :value="old('logged_on', now()->toDateString())" required />
                    </div>

                    <div>
                        <x-input-label for="tiktok_url" value="TikTok video URL" />
                        <x-text-input id="tiktok_url" name="tiktok_url" type="url" class="mt-1 block w-full" :value="old('tiktok_url')" required placeholder="https://www.tiktok.com/..." />
                    </div>

                    <div>
                        <x-input-label for="title_variant" value="Proposed title (YT / SEO)" />
                        <x-text-input id="title_variant" name="title_variant" class="mt-1 block w-full" :value="old('title_variant')" />
                    </div>

                    <div>
                        <x-input-label for="notes" value="Packaging notes (YT description, IG caption, tags)" />
                        <textarea id="notes" name="notes" rows="4" class="mt-1 ops-input" placeholder="IG: hook line 1…&#10;YT tags: …">{{ old('notes') }}</textarea>
                    </div>

                    <p class="text-sm text-stone-500">
                        Saved as <x-publish-status :status="\App\Enums\PublishStatus::PendingApproval" class="inline-flex" /> — creator sees this in step 5 (approval inbox).
                    </p>

                    <x-primary-button>Save row</x-primary-button>
                </form>
            </x-ops-panel>
        </div>
    </div>
</x-app-layout>
