<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SupabaseHealthCheck
{
    public function __construct(
        private SupabaseActionLogger $logger,
    ) {}

    /**
     * @return array{
     *     status: string,
     *     message: string,
     *     endpoint: ?string,
     *     checked_at: string,
     *     http_status?: int,
     *     details?: array<string, mixed>
     * }
     */
    public function check(string $trigger = SupabaseActionLogger::TRIGGER_TEST_CONNECTION): array
    {
        $result = $this->performCheck();

        $log = $this->logger->logHealthCheck($result, $trigger);
        $result['transaction_id'] = $log->transaction_id;

        return $result;
    }

    /**
     * @return array{
     *     status: string,
     *     message: string,
     *     endpoint: ?string,
     *     checked_at: string,
     *     http_status?: int,
     *     details?: array<string, mixed>
     * }
     */
    private function performCheck(): array
    {
        $url = config('supabase.url');
        $key = config('supabase.anon_key');
        $checkedAt = now()->toIso8601String();

        if (blank($url) || blank($key)) {
            return [
                'status' => 'not_configured',
                'message' => 'Set SUPABASE_URL and SUPABASE_ANON_KEY in .env',
                'endpoint' => null,
                'checked_at' => $checkedAt,
            ];
        }

        $endpoint = rtrim($url, '/').'/auth/v1/health';

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'apikey' => $key,
                    'Authorization' => 'Bearer '.$key,
                ])
                ->get($endpoint);

            if ($response->successful()) {
                return [
                    'status' => 'healthy',
                    'message' => 'Supabase auth service is reachable.',
                    'endpoint' => $endpoint,
                    'checked_at' => $checkedAt,
                    'http_status' => $response->status(),
                    'details' => $response->json() ?? [],
                ];
            }

            return [
                'status' => 'unhealthy',
                'message' => 'Supabase returned HTTP '.$response->status().'.',
                'endpoint' => $endpoint,
                'checked_at' => $checkedAt,
                'http_status' => $response->status(),
                'details' => [
                    'http_status' => $response->status(),
                ],
            ];
        } catch (\Throwable $exception) {
            return [
                'status' => 'unhealthy',
                'message' => $exception->getMessage(),
                'endpoint' => $endpoint,
                'checked_at' => $checkedAt,
            ];
        }
    }
}
