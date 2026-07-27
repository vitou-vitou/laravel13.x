<?php

namespace App\Enums;

enum ClaimStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case NeedsMoreInfo = 'needs_more_info';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Paid = 'paid';

    public function canTransitionTo(self $status): bool
    {
        return match ($this) {
            self::Draft => $status === self::Submitted,
            self::Submitted => in_array($status, [self::UnderReview, self::NeedsMoreInfo, self::Approved, self::Rejected], true),
            self::UnderReview => in_array($status, [self::NeedsMoreInfo, self::Approved, self::Rejected], true),
            self::NeedsMoreInfo => $status === self::Submitted,
            self::Approved => $status === self::Paid,
            self::Rejected, self::Paid => false,
        };
    }
}
