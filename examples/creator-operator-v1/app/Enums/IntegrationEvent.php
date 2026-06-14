<?php

namespace App\Enums;

enum IntegrationEvent: string
{
    case PublishLogApproved = 'publish_log.approved';
    case PublishLogPublished = 'publish_log.published';

    public function label(): string
    {
        return match ($this) {
            self::PublishLogApproved => 'Publish log approved',
            self::PublishLogPublished => 'Publish log published',
        };
    }
}
