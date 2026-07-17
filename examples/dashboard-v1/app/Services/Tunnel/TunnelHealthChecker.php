<?php

namespace App\Services\Tunnel;

use App\Models\Tunnel;
use Illuminate\Support\Facades\Http;

class TunnelHealthChecker
{
    /**
     * @return array{status: string, message: string, http_code: int|null}
     */
    public function check(Tunnel $tunnel): array
    {
        $url = $tunnel->publicBaseUrl().'/login';

        try {
            $response = Http::withHeaders([
                'ngrok-skip-browser-warning' => 'true',
            ])->timeout(5)->get($url);

            $code = $response->status();

            if ($response->successful()) {
                return [
                    'status' => 'ok',
                    'message' => 'Login page returned HTTP '.$code,
                    'http_code' => $code,
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Login page returned HTTP '.$code,
                'http_code' => $code,
            ];
        } catch (\Throwable $exception) {
            return [
                'status' => 'error',
                'message' => $exception->getMessage(),
                'http_code' => null,
            ];
        }
    }

    public function verifyAndStore(Tunnel $tunnel): array
    {
        $result = $this->check($tunnel);

        $tunnel->update([
            'last_verified_at' => now(),
            'last_verified_status' => $result['status'],
        ]);

        return $result;
    }
}
