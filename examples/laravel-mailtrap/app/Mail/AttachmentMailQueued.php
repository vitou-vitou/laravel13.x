<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class AttachmentMailQueued extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly string $userName,
        public readonly string $userEmail,
        // Stored path relative to 'local' disk (e.g. mail-attachments/file.pdf)
        public readonly string $storagePath,
        public readonly string $attachmentName,
    ) {
        $this->onQueue('mail');
    }

    public function backoff(): array
    {
        return [30, 60, 120];
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your document from ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.attachment',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('local', $this->storagePath)
                ->as($this->attachmentName),
        ];
    }

    public function failed(\Throwable $e): void
    {
        logger()->error('AttachmentMailQueued failed', [
            'recipient_email' => $this->userEmail,
            'recipient_name'  => $this->userName,
            'storage_path'    => $this->storagePath,
            'error'           => $e->getMessage(),
        ]);
    }
}
