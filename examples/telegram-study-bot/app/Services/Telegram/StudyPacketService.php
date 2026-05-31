<?php

namespace App\Services\Telegram;

use RuntimeException;

class StudyPacketService
{
    public function absolutePath(): string
    {
        $path = config('telegram.study_packet_path');

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('telegram.study_packet_path is not configured.');
        }

        if (! is_file($path)) {
            throw new RuntimeException("Study packet file not found: {$path}");
        }

        return $path;
    }

    public function caption(): string
    {
        return (string) config('telegram.study_packet_caption', '');
    }
}
