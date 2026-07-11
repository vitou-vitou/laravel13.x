<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OidcClient
{
    public function buildAuthorizationUrl(
        string $clientId,
        string $redirectUri,
        string $state,
        string $codeChallenge,
        string $scope = 'openid profile'
    ): string {
        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        return config('telegramauth.oidc.authorization_url').'?'.$params;
    }

    public function exchangeCode(
        string $code,
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        string $codeVerifier
    ): ?array {
        $response = Http::asForm()
            ->withBasicAuth($clientId, $clientSecret)
            ->post(config('telegramauth.oidc.token_url'), [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri,
                'client_id' => $clientId,
                'code_verifier' => $codeVerifier,
            ]);

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    public static function generateCodeVerifier(): string
    {
        return Str::random(64);
    }

    public static function generateCodeChallenge(string $codeVerifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }

    public static function verifyCodeChallenge(string $codeVerifier, string $codeChallenge): bool
    {
        return hash_equals(static::generateCodeChallenge($codeVerifier), $codeChallenge);
    }
}
