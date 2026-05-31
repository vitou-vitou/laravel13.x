<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramClient;
use App\Services\Telegram\TelegramStudyBot;
use Illuminate\Console\Command;
use Throwable;

class TelegramPollCommand extends Command
{
    protected $signature = 'telegram:poll {--once : Process one batch of updates and exit}';

    protected $description = 'Run Telegram long polling for the 8-principle study packet bot';

    public function handle(TelegramClient $telegram, TelegramStudyBot $bot): int
    {
        if (! $telegram->isConfigured()) {
            $this->error('Set TELEGRAM_BOT_TOKEN in .env (from @BotFather).');

            return self::FAILURE;
        }

        $offset = 0;
        $timeout = (int) config('telegram.poll_timeout_seconds', 30);
        $limit = (int) config('telegram.poll_limit', 50);
        $once = (bool) $this->option('once');

        $this->info('Telegram study bot polling started. Press Ctrl+C to stop.');

        do {
            try {
                $payload = $telegram->getUpdates($offset, $timeout, $limit);
            } catch (Throwable $exception) {
                $this->error($exception->getMessage());
                sleep(3);

                continue;
            }

            $updates = $payload['result'] ?? [];

            if (! is_array($updates)) {
                continue;
            }

            foreach ($updates as $update) {
                if (! is_array($update)) {
                    continue;
                }

                $updateId = $update['update_id'] ?? null;

                if (is_int($updateId)) {
                    $offset = $updateId + 1;
                }

                try {
                    $bot->handleUpdate($update);
                } catch (Throwable $exception) {
                    $this->error("Update failed: {$exception->getMessage()}");
                }
            }
        } while (! $once);

        return self::SUCCESS;
    }
}
