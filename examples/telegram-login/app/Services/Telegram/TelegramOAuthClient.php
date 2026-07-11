<?php

namespace App\Services\Telegram;

use App\Models\TelegramBot;

class TelegramOAuthClient
{
    public function buildAuthorizationUrl(TelegramBot $bot, string $returnTo): string
    {
        $botId = $this->extractBotId($bot->bot_token);

        $params = http_build_query([
            'bot_id' => $botId,
            'origin' => $this->resolveOrigin($bot),
            'request_access' => 'write',
            'return_to' => $returnTo,
        ]);

        return 'https://oauth.telegram.org/auth?'.$params;
    }

    private function resolveOrigin(TelegramBot $bot): string
    {
        $domains = $bot->domains ?? [];

        if ($domains !== []) {
            $host = $this->normalizeDomain((string) $domains[0]);
            $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';

            return $scheme.'://'.$host;
        }

        return rtrim((string) config('app.url'), '/');
    }

    private function normalizeDomain(string $domain): string
    {
        $domain = trim($domain);
        $domain = preg_replace('#^https?://#i', '', $domain) ?? $domain;

        return rtrim($domain, '/');
    }

    public function extractBotId(string $botToken): string
    {
        $botId = explode(':', $botToken, 2)[0] ?? '';

        if ($botId === '' || ! ctype_digit($botId)) {
            throw new \InvalidArgumentException('Invalid Telegram bot token format.');
        }

        return $botId;
    }
}
