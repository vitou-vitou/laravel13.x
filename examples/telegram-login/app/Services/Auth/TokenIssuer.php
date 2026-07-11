<?php

namespace App\Services\Auth;

use App\Models\Application;
use App\Models\EndUser;
use Illuminate\Support\Str;

class TokenIssuer
{
    public function issueAccessToken(Application $application, EndUser $endUser): string
    {
        $header = $this->base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => config('app.url'),
            'sub' => (string) $endUser->telegram_id,
            'aud' => $application->client_id,
            'iat' => time(),
            'exp' => time() + (int) config('telegramauth.access_token_ttl', 3600),
            'user' => $endUser->toProfileArray(),
        ]));

        $signature = $this->base64UrlEncode(
            hash_hmac('sha256', $header.'.'.$payload, $this->signingKey(), true)
        );

        return $header.'.'.$payload.'.'.$signature;
    }

    public function issueRefreshToken(): string
    {
        return Str::random(64);
    }

    public function decodeAccessToken(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;

        $expectedSignature = $this->base64UrlEncode(
            hash_hmac('sha256', $header.'.'.$payload, $this->signingKey(), true)
        );

        if (! hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $claims = json_decode($this->base64UrlDecode($payload), true);

        if (! is_array($claims) || ($claims['exp'] ?? 0) < time()) {
            return null;
        }

        return $claims;
    }

    private function signingKey(): string
    {
        return config('app.key');
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;

        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }
}
