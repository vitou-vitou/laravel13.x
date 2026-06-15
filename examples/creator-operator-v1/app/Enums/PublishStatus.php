<?php

namespace App\Enums;

enum PublishStatus: string
{
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Published = 'published';
    case SkippedMusic = 'skipped_music';
    case SkippedCreator = 'skipped_creator';
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::PendingApproval => 'Pending approval',
            self::Approved => 'Approved',
            self::Published => 'Published',
            self::SkippedMusic => 'Skipped (music)',
            self::SkippedCreator => 'Skipped (creator)',
            self::Error => 'Error',
        };
    }

    public function isApprovable(): bool
    {
        return $this === self::PendingApproval;
    }

    public function isPublishable(): bool
    {
        return $this === self::Approved;
    }
}
