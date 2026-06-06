<?php

namespace App\Services\Tunnel;

use App\Models\Tunnel;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TunnelActivator
{
    public function __construct(
        private readonly NgrokEnvSync $ngrokEnvSync,
        private readonly NgrokTrafficPolicyWriter $trafficPolicyWriter,
    ) {}

    /**
     * @return array{domain: string, base_url: string, herd_host: string, oauth_urls: array<string, string>}
     */
    public function activate(Tunnel $tunnel): array
    {
        if (! config('tunnel.enabled')) {
            throw new RuntimeException('Tunnel admin is disabled.');
        }

        return DB::transaction(function () use ($tunnel): array {
            Tunnel::query()->update(['is_active' => false]);
            $tunnel->update(['is_active' => true]);

            $envResult = $this->ngrokEnvSync->sync($tunnel->domain);
            $this->trafficPolicyWriter->sync($tunnel->herd_host);

            return [
                ...$envResult,
                'herd_host' => $tunnel->herd_host,
            ];
        });
    }
}
