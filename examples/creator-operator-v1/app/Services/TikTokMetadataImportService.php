<?php

namespace App\Services;

use App\Models\Creator;
use Illuminate\Support\Collection;

class TikTokMetadataImportService
{
    /**
     * @return array<int, array{video_id: string, caption: ?string, views: ?int, posted_date: ?string, video_url: string, music_title: ?string, status: string}>
     */
    public function parseJsonl(string $jsonl): array
    {
        $rows = [];

        foreach (preg_split('/\r\n|\r|\n/', trim($jsonl)) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $decoded = json_decode($line, true);
            if (! is_array($decoded) || empty($decoded['video_url'])) {
                continue;
            }

            $rows[] = [
                'video_id' => (string) ($decoded['video_id'] ?? ''),
                'caption' => $decoded['caption'] ?? null,
                'views' => isset($decoded['views']) ? (int) $decoded['views'] : null,
                'posted_date' => $decoded['posted_date'] ?? null,
                'video_url' => (string) $decoded['video_url'],
                'music_title' => $decoded['music_title'] ?? null,
                'status' => (string) ($decoded['status'] ?? 'ok'),
            ];
        }

        return $rows;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    public function candidatesForCreator(Creator $creator, array $rows): Collection
    {
        $existingUrls = $creator->publishLogEntries()
            ->pluck('tiktok_url')
            ->map(fn (string $url) => $this->normalizeUrl($url))
            ->all();

        return collect($rows)
            ->filter(fn (array $row) => in_array($row['status'], ['ok', 'downloaded'], true))
            ->filter(function (array $row) use ($creator, $existingUrls) {
                if (in_array($this->normalizeUrl($row['video_url']), $existingUrls, true)) {
                    return false;
                }

                if ($creator->last_run_date && ! empty($row['posted_date'])) {
                    return $row['posted_date'] >= $creator->last_run_date->toDateString();
                }

                return true;
            })
            ->values();
    }

    public function normalizeUrl(string $url): string
    {
        return rtrim(strtolower($url), '/');
    }
}
