<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Telegram Bot API
    |--------------------------------------------------------------------------
    |
    | Create a bot via @BotFather and paste the token here.
    |
    */

    'bot_token' => env('TELEGRAM_BOT_TOKEN'),

    'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Study packet file
    |--------------------------------------------------------------------------
    |
    | Default: bundled copy under storage/app/study-packets/.
    | Override to point at repo root docs/study/... if preferred.
    |
    */

    'study_packet_path' => env(
        'STUDY_PACKET_PATH',
        resource_path('study-packets/180-laravel-project-types-study-packet.md'),
    ),

    'study_packet_caption' => env(
        'STUDY_PACKET_CAPTION',
        '8-Principle Study Packet — Laravel 180+ project types (Markdown)',
    ),

    /*
    |--------------------------------------------------------------------------
    | Access control (optional)
    |--------------------------------------------------------------------------
    |
    | Comma-separated Telegram chat IDs. Empty = allow all chats.
    |
    */

    'allowed_chat_ids' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TELEGRAM_ALLOWED_CHAT_IDS', '')),
    ))),

    /*
    |--------------------------------------------------------------------------
    | Long polling
    |--------------------------------------------------------------------------
    */

    'poll_timeout_seconds' => (int) env('TELEGRAM_POLL_TIMEOUT', 30),

    'poll_limit' => (int) env('TELEGRAM_POLL_LIMIT', 50),

];
