<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Log;

class TelegramStudyBot
{
    public function __construct(
        private readonly TelegramClient $telegram,
        private readonly UpdateRouter $router,
        private readonly StudyPacketService $studyPacket,
    ) {}

    /**
     * @param  array<string, mixed>  $update
     */
    public function handleUpdate(array $update): void
    {
        $route = $this->router->route($update);
        $chatId = $route['chat_id'];

        if ($chatId === null) {
            return;
        }

        match ($route['action']) {
            UpdateRouter::ACTION_WELCOME => $this->telegram->sendMessage(
                $chatId,
                $this->welcomeText(),
            ),
            UpdateRouter::ACTION_HELP => $this->telegram->sendMessage(
                $chatId,
                $this->helpText(),
            ),
            UpdateRouter::ACTION_SEND_PACKET => $this->sendStudyPacket($chatId),
            UpdateRouter::ACTION_DENIED => $this->telegram->sendMessage(
                $chatId,
                'This bot is restricted. Your chat ID is not on the allow list.',
            ),
            default => $this->telegram->sendMessage(
                $chatId,
                "Unknown command. Send /help to see available commands.\n\nTip: /study sends the full Markdown study packet as a file.",
            ),
        };
    }

    private function sendStudyPacket(int $chatId): void
    {
        $path = $this->studyPacket->absolutePath();

        $this->telegram->sendDocument(
            $chatId,
            $path,
            $this->studyPacket->caption(),
        );

        $this->telegram->sendMessage(
            $chatId,
            'Study packet sent. Open the `.md` file in any Markdown viewer or editor.',
        );

        Log::info('telegram.study_packet.sent', [
            'chat_id' => $chatId,
            'path' => $path,
        ]);
    }

    private function welcomeText(): string
    {
        return <<<'TEXT'
Welcome to the 8-Principle Study Bot.

This bot delivers the Laravel "180+ Buildable Project Types" study packet (Markdown).

Commands:
/study — send the study document
/help — command list

Built for spaced-repetition learning (map, quiz, flashcards, review schedule).
TEXT;
    }

    private function helpText(): string
    {
        return <<<'TEXT'
Commands:
/start — welcome message
/study — send 180-laravel-project-types-study-packet.md
/packet — alias for /study
/doc — alias for /study
/help — this message

The packet is ~27 KB Markdown (too long for a single chat message), so it is sent as a document.
TEXT;
    }
}
