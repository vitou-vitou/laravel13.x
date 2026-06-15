<?php

namespace App\Enums;

enum MusicPolicy: string
{
    case Skip = 'skip';
    case Replace = 'replace';
    case CreatorExport = 'creator_export';

    public function label(): string
    {
        return match ($this) {
            self::Skip => 'Skip on conflict',
            self::Replace => 'Replace audio',
            self::CreatorExport => 'Creator export only',
        };
    }
}
