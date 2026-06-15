<?php

namespace App\Services;

use App\Models\PublishLogEntry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TikTokThumbnailService
{
    public function resolve(string $tiktokUrl): ?string
    {
        if (! $this->isTikTokVideoUrl($tiktokUrl)) {
            return null;
        }

        $cacheKey = 'tiktok_thumb:'.sha1($tiktokUrl);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($tiktokUrl): ?string {
            try {
                $response = Http::timeout(5)
                    ->acceptJson()
                    ->get('https://www.tiktok.com/oembed', ['url' => $tiktokUrl]);

                if (! $response->successful()) {
                    return null;
                }

                $thumbnail = $response->json('thumbnail_url');

                return is_string($thumbnail) && $thumbnail !== '' ? $thumbnail : null;
            } catch (\Throwable) {
                return null;
            }
        });
    }

    public function hydrateEntry(PublishLogEntry $entry): void
    {
        if ($entry->tiktok_thumbnail_url !== null || blank($entry->tiktok_url)) {
            return;
        }

        $thumbnail = $this->resolve($entry->tiktok_url);

        if ($thumbnail !== null) {
            $entry->forceFill(['tiktok_thumbnail_url' => $thumbnail])->saveQuietly();
        } elseif ($this->placeholderThumbnailUrl() !== null) {
            $entry->forceFill(['tiktok_thumbnail_url' => $this->placeholderThumbnailUrl()])->saveQuietly();
        }
    }

    public function placeholderThumbnailUrl(): ?string
    {
        $path = config('tiktok-import.placeholder_thumbnail_url');

        if (! is_string($path) || $path === '') {
            return null;
        }

        return str_starts_with($path, 'http') ? $path : url($path);
    }

    private function isTikTokVideoUrl(string $url): bool
    {
        return Str::contains($url, ['tiktok.com', 'vm.tiktok.com']);
    }
}
