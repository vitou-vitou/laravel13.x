<?php

namespace App\Services;

use App\Mail\ApprovalBatchReadyMail;
use App\Models\Creator;
use Illuminate\Support\Facades\Mail;

class ApprovalBatchNotifier
{
    public function notifyIfNeeded(Creator $creator, int $newPendingRows = 1): void
    {
        if ($newPendingRows < 1) {
            return;
        }

        $creator->loadMissing('user');

        $email = $creator->user?->email;

        if ($email === null || $email === '') {
            return;
        }

        $pendingCount = $creator->pendingApprovalsCount();

        Mail::to($email)->send(new ApprovalBatchReadyMail($creator, $pendingCount));
    }
}
