<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\AttachmentMail;
use App\Mail\AttachmentMailQueued;
use App\Mail\WelcomeMailQueued;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MailControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        Storage::fake('local');
    }

    // ─── POST /mail/send ──────────────────────────────────────────────────────

    public function test_send_returns_200_with_valid_payload(): void
    {
        $this->postJson('/mail/send', [
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ])->assertOk()
          ->assertExactJson(['message' => 'Email sent via Laravel mailer.']);
    }

    public function test_send_rejects_missing_name_and_email(): void
    {
        $this->postJson('/mail/send', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_send_rejects_missing_email(): void
    {
        $this->postJson('/mail/send', ['name' => 'Test User'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email'])
            ->assertJsonMissingValidationErrors(['name']);
    }

    public function test_send_rejects_invalid_email(): void
    {
        $this->postJson('/mail/send', ['name' => 'Test User', 'email' => 'not-an-email'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_send_rejects_name_exceeding_100_characters(): void
    {
        $this->postJson('/mail/send', [
            'name'  => str_repeat('a', 101),
            'email' => 'test@example.com',
        ])->assertUnprocessable()
          ->assertJsonValidationErrors(['name']);
    }

    // ─── POST /mail/sandbox ───────────────────────────────────────────────────

    public function test_sandbox_rejects_missing_name_and_email(): void
    {
        $this->postJson('/mail/sandbox', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_sandbox_rejects_invalid_email(): void
    {
        $this->postJson('/mail/sandbox', ['name' => 'Test', 'email' => 'bad'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    // ─── POST /mail/send-api ──────────────────────────────────────────────────

    public function test_send_api_rejects_missing_name_and_email(): void
    {
        $this->postJson('/mail/send-api', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_send_api_rejects_invalid_email(): void
    {
        $this->postJson('/mail/send-api', ['name' => 'Test', 'email' => 'bad'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    // ─── POST /mail/send-queued ───────────────────────────────────────────────

    public function test_send_queued_dispatches_mailable_to_queue(): void
    {
        $this->postJson('/mail/send-queued', [
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ])->assertOk()
          ->assertExactJson(['message' => 'Email queued for delivery.']);

        Mail::assertQueued(WelcomeMailQueued::class, function (WelcomeMailQueued $mail): bool {
            return $mail->userName === 'Test User'
                && $mail->userEmail === 'test@example.com';
        });
    }

    public function test_send_queued_rejects_missing_fields(): void
    {
        $this->postJson('/mail/send-queued', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_send_queued_rejects_invalid_email(): void
    {
        $this->postJson('/mail/send-queued', ['name' => 'Test', 'email' => 'bad'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    // ─── POST /mail/send-attachment ───────────────────────────────────────────

    public function test_send_attachment_sends_email_with_file(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->post('/mail/send-attachment', [
            'name'       => 'Test User',
            'email'      => 'test@example.com',
            'attachment' => $file,
        ])->assertOk()
          ->assertExactJson(['message' => 'Email with attachment sent.']);

        Mail::assertSent(AttachmentMail::class, function (AttachmentMail $mail): bool {
            return $mail->userName === 'Test User';
        });
    }

    public function test_send_attachment_rejects_missing_fields(): void
    {
        // Test field validation without a file (JSON path — faster and reliable)
        $this->postJson('/mail/send-attachment', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'attachment']);
    }

    public function test_send_attachment_rejects_invalid_email(): void
    {
        $this->postJson('/mail/send-attachment', [
            'name'  => 'Test',
            'email' => 'not-valid',
        ])->assertUnprocessable()
          ->assertJsonValidationErrors(['email']);
    }

    // ─── POST /mail/send-queued-attachment ───────────────────────────────────

    public function test_send_queued_attachment_dispatches_to_queue(): void
    {
        $file = UploadedFile::fake()->create('report.pdf', 100, 'application/pdf');

        $this->post('/mail/send-queued-attachment', [
            'name'       => 'Test User',
            'email'      => 'test@example.com',
            'attachment' => $file,
        ])->assertOk()
          ->assertExactJson(['message' => 'Email with attachment queued for delivery.']);

        Mail::assertQueued(AttachmentMailQueued::class, function (AttachmentMailQueued $mail): bool {
            return $mail->userName === 'Test User'
                && $mail->userEmail === 'test@example.com'
                && $mail->attachmentName === 'report.pdf'
                && str_starts_with($mail->storagePath, 'mail-attachments/');
        });
    }

    public function test_send_queued_attachment_rejects_missing_fields(): void
    {
        $this->postJson('/mail/send-queued-attachment', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'attachment']);
    }

    public function test_send_queued_attachment_rejects_invalid_email(): void
    {
        $this->postJson('/mail/send-queued-attachment', [
            'name'  => 'Test',
            'email' => 'not-valid',
        ])->assertUnprocessable()
          ->assertJsonValidationErrors(['email']);
    }

    // ─── POST /mail/notify ────────────────────────────────────────────────────

    public function test_notify_dispatches_welcome_notification(): void
    {
        $this->postJson('/mail/notify', [
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ])->assertOk()
          ->assertExactJson(['message' => 'Notification dispatched via mail channel.']);

        Notification::assertSentOnDemand(
            WelcomeNotification::class,
            function (WelcomeNotification $notification, array $channels, object $notifiable): bool {
                return $notifiable->routes['mail'] === 'test@example.com';
            }
        );
    }

    public function test_notify_rejects_missing_fields(): void
    {
        $this->postJson('/mail/notify', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_notify_rejects_invalid_email(): void
    {
        $this->postJson('/mail/notify', ['name' => 'Test', 'email' => 'bad'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
