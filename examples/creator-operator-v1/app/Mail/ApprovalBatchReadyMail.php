<?php

namespace App\Mail;

use App\Models\Creator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApprovalBatchReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Creator $creator,
        public int $pendingCount,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Videos ready for your approval',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.approval-batch-ready',
        );
    }
}
