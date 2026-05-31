<?php

use App\Http\Controllers\TelegramWebhookController;
use App\Services\Telegram\StudyPacketService;
use App\Services\Telegram\TelegramClient;
use Illuminate\Support\Facades\Route;

Route::get('/', function (StudyPacketService $studyPacket, TelegramClient $telegram) {
    return response()->json([
        'service' => 'telegram-study-bot',
        'telegram_configured' => $telegram->isConfigured(),
        'study_packet_exists' => is_file($studyPacket->absolutePath()),
        'commands' => ['/start', '/study', '/help'],
        'poll' => 'php artisan telegram:poll',
        'webhook' => url('/telegram/webhook'),
    ]);
});

Route::post('/telegram/webhook', TelegramWebhookController::class);
