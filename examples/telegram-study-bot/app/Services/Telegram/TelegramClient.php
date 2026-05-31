<?php

namespace App\Services\Telegram;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TelegramClient
{
    public function __construct(
        private readonly ?string $botToken,
    ) {}

    public function isConfigured(): bool
    {
        return filled($this->botToken);
    }

    /**
     * @return array<string, mixed>
     */
    public function getUpdates(int $offset = 0, int $timeout = 30, int $limit = 50): array
    {
        return $this->request('getUpdates', [
            'offset' => $offset,
            'timeout' => $timeout,
            'limit' => $limit,
            'allowed_updates' => ['message'],
        ])->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function sendMessage(int $chatId, string $text): array
    {
        return $this->request('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'disable_web_page_preview' => true,
        ])->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function sendDocument(int $chatId, string $absolutePath, ?string $caption = null): array
    {
        if (! is_readable($absolutePath)) {
            throw new RuntimeException("Study packet not readable: {$absolutePath}");
        }

        $request = $this->http()->attach(
            'document',
            file_get_contents($absolutePath),
            basename($absolutePath),
        );

        $payload = ['chat_id' => $chatId];

        if ($caption !== null && $caption !== '') {
            $payload['caption'] = $caption;
        }

        $response = $request->post($this->methodUrl('sendDocument'), $payload);

        $this->ensureOk($response);

        return $response->json();
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function request(string $method, array $parameters = []): Response
    {
        $response = $this->http()->post($this->methodUrl($method), $parameters);

        $this->ensureOk($response);

        return $response;
    }

    private function http(): PendingRequest
    {
        return Http::timeout(120)->acceptJson();
    }

    private function methodUrl(string $method): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('TELEGRAM_BOT_TOKEN is not set.');
        }

        return "https://api.telegram.org/bot{$this->botToken}/{$method}";
    }

    private function ensureOk(Response $response): void
    {
        $body = $response->json();

        if ($response->successful() && ($body['ok'] ?? false) === true) {
            return;
        }

        $description = is_array($body) ? ($body['description'] ?? $response->body()) : $response->body();

        throw new RuntimeException("Telegram API error: {$description}");
    }
}
