<?php

namespace App\Services;

use App\Models\Creator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;

class TikTokMetadataCliRunner
{
    public function isConfigured(): bool
    {
        return is_file(config('tiktok-import.script'));
    }

    public function fetchJsonl(Creator $creator, ?int $limit = null): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'TikTok metadata CLI not found at '.config('tiktok-import.script').'. Install tools/tiktok-metadata or set TIKTOK_METADATA_SCRIPT.'
            );
        }

        $outputDir = storage_path('app/tiktok-import/'.Str::uuid());
        File::ensureDirectoryExists($outputDir);

        $handle = ltrim($creator->handle, '@');
        $args = [
            config('tiktok-import.python'),
            config('tiktok-import.script'),
            '--username',
            $handle,
            '--metadata-only',
            '--output-dir',
            $outputDir,
            '--no-skip-logged',
        ];

        if ($creator->last_run_date) {
            $args[] = '--since-date';
            $args[] = $creator->last_run_date->toDateString();
        }

        if ($limit !== null && $limit > 0) {
            $args[] = '--limit';
            $args[] = (string) $limit;
        }

        try {
            $result = Process::timeout((int) config('tiktok-import.timeout', 120))->run($args);

            if (! $result->successful()) {
                $message = trim($result->errorOutput() ?: $result->output());
                throw new RuntimeException($message !== '' ? $message : 'TikTok metadata CLI failed (exit '.$result->exitCode().').');
            }

            $jsonlPath = $outputDir.'/'.$handle.'/metadata.jsonl';

            if (! is_file($jsonlPath)) {
                return '';
            }

            return (string) file_get_contents($jsonlPath);
        } finally {
            File::deleteDirectory($outputDir);
        }
    }
}
