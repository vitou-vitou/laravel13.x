<?php

namespace App\Http\Controllers;

use App\Services\Telegram\TelegramStudyBot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request, TelegramStudyBot $bot): JsonResponse
    {
        $secret = config('telegram.webhook_secret');

        if (filled($secret) && $request->header('X-Telegram-Bot-Api-Secret-Token') !== $secret) {
            return response()->json(['ok' => false], Response::HTTP_FORBIDDEN);
        }

        $update = $request->all();

        if ($update !== []) {
            $bot->handleUpdate($update);
        }

        return response()->json(['ok' => true]);
    }
}
