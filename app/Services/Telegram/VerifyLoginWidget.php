<?php

namespace App\Services\Telegram;

class VerifyLoginWidget
{
    public function verify(array $data, string $botToken): bool
    {
        if (! isset($data['hash'], $data['auth_date'], $data['id'])) {
            return false;
        }

        $maxAge = (int) config('telegramauth.auth_date_max_age', 300);

        if (time() - (int) $data['auth_date'] > $maxAge) {
            return false;
        }

        $hash = $data['hash'];
        unset($data['hash']);

        $dataCheckString = collect($data)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->sortKeys()
            ->map(fn ($value, $key) => $key.'='.$value)
            ->implode("\n");

        $secretKey = hash('sha256', $botToken, true);
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($calculatedHash, $hash);
    }

    public function extractProfile(array $data): array
    {
        return [
            'telegram_id' => (int) $data['id'],
            'username' => $data['username'] ?? null,
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'photo_url' => $data['photo_url'] ?? null,
            'phone' => $data['phone'] ?? null,
        ];
    }
}
