<?php

namespace App\Enums;

enum DisputeStatus: string
{
    case Opened = 'opened';
    case UnderReview = 'under_review';
    case ResolvedBuyer = 'resolved_buyer';
    case ResolvedVendor = 'resolved_vendor';
    case Escalated = 'escalated';
}
