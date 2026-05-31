<?php

namespace App\Services\Telegram;

class UpdateRouter
{
    public const ACTION_WELCOME = 'welcome';

    public const ACTION_HELP = 'help';

    public const ACTION_SEND_PACKET = 'send_packet';

    public const ACTION_DENIED = 'denied';

    public const ACTION_UNKNOWN = 'unknown';

    /**
     * @param  array<string, mixed>  $update
     * @return array{action: string, chat_id: int|null, text: string|null}
     */
    public function route(array $update): array
    {
        $message = $update['message'] ?? null;

        if (! is_array($message)) {
            return $this->result(self::ACTION_UNKNOWN);
        }

        $chat = $message['chat'] ?? null;
        $chatId = is_array($chat) ? ($chat['id'] ?? null) : null;

        if (! is_int($chatId)) {
            return $this->result(self::ACTION_UNKNOWN);
        }

        if (! $this->chatIsAllowed((string) $chatId)) {
            return $this->result(self::ACTION_DENIED, $chatId);
        }

        $text = isset($message['text']) && is_string($message['text'])
            ? trim($message['text'])
            : '';

        $command = $this->normalizeCommand($text);

        return match ($command) {
            '/start' => $this->result(self::ACTION_WELCOME, $chatId),
            '/help' => $this->result(self::ACTION_HELP, $chatId),
            '/study', '/packet', '/doc' => $this->result(self::ACTION_SEND_PACKET, $chatId),
            default => $this->result(self::ACTION_UNKNOWN, $chatId, $text),
        };
    }

    private function chatIsAllowed(string $chatId): bool
    {
        $allowed = config('telegram.allowed_chat_ids', []);

        if ($allowed === []) {
            return true;
        }

        return in_array($chatId, $allowed, true);
    }

    private function normalizeCommand(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $first = explode(' ', $text, 2)[0];
        $command = strtolower(explode('@', $first, 2)[0]);

        return $command;
    }

    /**
     * @return array{action: string, chat_id: int|null, text: string|null}
     */
    private function result(string $action, ?int $chatId = null, ?string $text = null): array
    {
        return [
            'action' => $action,
            'chat_id' => $chatId,
            'text' => $text,
        ];
    }
}
