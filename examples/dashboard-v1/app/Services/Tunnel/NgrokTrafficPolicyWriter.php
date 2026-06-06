<?php

namespace App\Services\Tunnel;

use App\Models\Tunnel;
use RuntimeException;

class NgrokTrafficPolicyWriter
{
    public function __construct(
        private readonly ?string $policyPath = null,
    ) {}

    public function path(): string
    {
        return $this->policyPath ?? (string) config('tunnel.traffic_policy_path');
    }

    public function sync(string $herdHost): void
    {
        Tunnel::validateHerdHost($herdHost);

        $path = $this->path();

        if (! is_file($path)) {
            throw new RuntimeException("Traffic policy file not found: {$path}");
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Unable to read traffic policy file: {$path}");
        }

        if (! preg_match('/^\s*host:\s*.+$/m', $contents)) {
            throw new RuntimeException("Traffic policy file has no host header entry: {$path}");
        }

        $updated = preg_replace(
            '/^(\s*host:\s*).+$/m',
            '$1'.$herdHost,
            $contents,
            1,
        );

        if ($updated === null) {
            throw new RuntimeException("Unable to update traffic policy file: {$path}");
        }

        $updated = preg_replace(
            '/^# Herd routes by Host; ngrok must send .+ upstream\.$/m',
            "# Herd routes by Host; ngrok must send {$herdHost} upstream.",
            $updated,
            1,
        ) ?? $updated;

        $handle = fopen($path, 'c+');

        if ($handle === false) {
            throw new RuntimeException("Unable to open traffic policy file: {$path}");
        }

        try {
            if (! flock($handle, LOCK_EX)) {
                throw new RuntimeException("Unable to lock traffic policy file: {$path}");
            }

            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, $updated);
            fflush($handle);
            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }
    }
}
