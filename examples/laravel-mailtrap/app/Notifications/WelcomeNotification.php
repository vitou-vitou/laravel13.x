<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        private readonly string $userName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Welcome to ' . config('app.name'))
            ->greeting("Hello, {$this->userName}!")
            ->line('Thank you for joining ' . config('app.name') . '.')
            ->line('We are excited to have you on board.')
            ->action('Visit Dashboard', url('/'))
            ->line('If you have any questions, feel free to reach out.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'user_name' => $this->userName,
        ];
    }
}
