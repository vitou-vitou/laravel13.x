<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class AttachmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $userName,
        private readonly string $attachmentPath,
        private readonly string $attachmentName,
    ) {}

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
            Attachment::fromPath($this->attachmentPath)
                ->as($this->attachmentName),
        ];
    }
}
