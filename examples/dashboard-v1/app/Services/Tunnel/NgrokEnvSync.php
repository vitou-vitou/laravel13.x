<?php

namespace App\Services\Tunnel;

use App\Models\Tunnel;
use Illuminate\Support\Facades\Artisan;

class NgrokEnvSync
{
    public function __construct(
        private readonly ?string $envPath = null,
    ) {}

    /**
     * @return array{domain: string, base_url: string, oauth_urls: array<string, string>}
     */
    public function sync(string $domain): array
    {
        $domain = Tunnel::normalizeDomain($domain);
        Tunnel::validateDomain($domain);

        $writer = new EnvFileWriter($this->envPath ?? base_path('.env'));
        $base = 'https://'.$domain;

        $writer->set('NGROK_DEV_DOMAIN', $domain);

        $oauthUrls = [];

        foreach (config('tunnel.oauth_callbacks', []) as $envKey => $path) {
            $url = $base.$path;
            $writer->set($envKey, $url);
            $oauthUrls[$envKey] = $url;
        }

        Artisan::call('config:clear');

        return [
            'domain' => $domain,
            'base_url' => $base,
            'oauth_urls' => $oauthUrls,
        ];
    }
}
