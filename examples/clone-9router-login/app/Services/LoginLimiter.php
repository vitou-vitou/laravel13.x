<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class LoginLimiter
{
    private const MAX_FAILS_BEFORE_LOCK = 5;
    private const LOCK_STEPS_SEC = [30, 120, 600, 1800];
    private const FAIL_WINDOW_SEC = 3600;

    // ponytail: cache storage ceiling; use redis driver when scaling across cluster.
    public function checkLock(string $ip): array
    {
        $entry = $this->getEntry($ip);
        if (! $entry || empty($entry['lockUntil'])) {
            return ['locked' => false, 'retryAfter' => 0];
        }

        $remaining = $entry['lockUntil'] - time();
        if ($remaining <= 0) {
            return ['locked' => false, 'retryAfter' => 0];
        }

        return ['locked' => true, 'retryAfter' => $remaining];
    }

    public function recordFail(string $ip): array
    {
        $now = time();
        $entry = $this->getEntry($ip) ?? [
            'fails' => 0,
            'lockUntil' => 0,
            'lockLevel' => 0,
            'lastFailAt' => 0,
        ];

        $entry['fails']++;
        $entry['lastFailAt'] = $now;

        if ($entry['fails'] >= self::MAX_FAILS_BEFORE_LOCK) {
            $stepIndex = min($entry['lockLevel'], count(self::LOCK_STEPS_SEC) - 1);
            $entry['lockUntil'] = $now + self::LOCK_STEPS_SEC[$stepIndex];
            $entry['lockLevel']++;
            $entry['fails'] = 0;
        }

        Cache::put($this->cacheKey($ip), $entry, self::FAIL_WINDOW_SEC);

        return [
            'remainingBeforeLock' => max(0, self::MAX_FAILS_BEFORE_LOCK - $entry['fails']),
        ];
    }

    public function recordSuccess(string $ip): void
    {
        Cache::forget($this->cacheKey($ip));
    }

    public function reset(string $ip): void
    {
        Cache::forget($this->cacheKey($ip));
    }

    private function getEntry(string $ip): ?array
    {
        $entry = Cache::get($this->cacheKey($ip));
        if (! $entry) {
            return null;
        }

        if (! empty($entry['lastFailAt']) && (time() - $entry['lastFailAt'] > self::FAIL_WINDOW_SEC)) {
            if (empty($entry['lockUntil']) || time() >= $entry['lockUntil']) {
                $this->reset($ip);
                return null;
            }
        }

        return $entry;
    }

    private function cacheKey(string $ip): string
    {
        return 'dashboard_login_attempts:'.md5($ip);
    }
}
