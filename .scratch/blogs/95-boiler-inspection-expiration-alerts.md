---
title: "How to Build Automated Boiler Statutory Inspection Alert Workflows"
published: true
description: "Schedule automated notifications and policy warning flags when commercial pressure vessel statutory test certificates approach expiration in Laravel."
tags: "laravel, queues, notifications, scheduling, insurance"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/95-boiler-inspection-expiration-alerts.md"
---

Boiler and Pressure Vessel insurance policies carry an essential statutory warranty: coverage remains valid only while equipment maintains active, state-certified inspection certificates. When an industrial client allows an annual hydrostatic test or vessel fitness certificate to expire, the policy enters a legally vulnerable breach of warranty.

Building an automated inspection alert pipeline in Laravel detects upcoming certificate expirations, notifies underwriting managers and plant safety officers, and prevents uncertified machinery from operating under an active policy. Let's see how.

## The Problem: The Lapsed Certificate Blind Spot

Underwriters cannot manually monitor thousands of individual vessel inspection dates scattered across hundreds of manufacturing policies. 

When an uninspected pressure receiver ruptures:
- Insurers face legal disputes over whether the breach of warranty was properly communicated.
- Plant operators claim they never received renewal notices from the carrier.
- Claims adjusters discover certificate lapses only after millions in factory damage have occurred.

A proactive daily queue job ensures both the insurer and the client receive timely warnings 60, 30, and 7 days prior to certificate expiration.

## Step 1: Create the Inspection Alert Notification

Build a multi-channel notification for underwriters and plant administrators:

`app/Notifications/BoilerCertificateExpiringNotification.php:`
```php
<?php

namespace App\Notifications;

use App\Models\BoilerPressureVessel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BoilerCertificateExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public BoilerPressureVessel $vessel,
        public int $daysRemaining
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject("URGENT: Statutory Inspection Certificate Expiring in {$this->daysRemaining} Days - Vessel {$this->vessel->tag_number}")
            ->line("Pressure unit {$this->vessel->tag_number} ({$this->vessel->vessel_type}) at {$this->vessel->policy->insured_name} is due for statutory inspection.")
            ->line("Certificate Expiry Date: {$this->vessel->statutory_certificate_expiry->toFormattedDateString()}")
            ->line("Operating pressure vessels without certified inspection violates policy warranty conditions.")
            ->action('View Vessel Record', url("/policies/{$this->vessel->policy_id}"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'vessel_id' => $this->vessel->id,
            'tag_number' => $this->vessel->tag_number,
            'days_remaining' => $this->daysRemaining,
            'expiry_date' => $this->vessel->statutory_certificate_expiry->toDateString(),
        ];
    }
}
```

This notification queues asynchronously and delivers both email notices and in-app database notifications.

## Step 2: Build the Daily Inspection Monitor Command

Create an Artisan console command that scans active policies for vessels approaching expiration:

`app/Console/Commands/CheckBoilerCertificateExpirations.php:`
```php
<?php

namespace App\Console\Commands;

use App\Models\BoilerPressureVessel;
use App\Notifications\BoilerCertificateExpiringNotification;
use Illuminate\Console\Command;

class CheckBoilerCertificateExpirations extends Command
{
    protected $signature = 'boiler:check-expirations';
    protected $description = 'Scan pressure vessels and dispatch alerts for expiring inspection certificates';

    public function handle(): int
    {
        $thresholds = [60, 30, 7];

        foreach ($thresholds as $days) {
            $targetDate = now()->addDays($days)->toDateString();

            $vessels = BoilerPressureVessel::query()
                ->whereDate('statutory_certificate_expiry', $targetDate)
                ->whereHas('policy', fn($q) => $q->where('status', 'active'))
                ->with(['policy.underwriter'])
                ->get();

            foreach ($vessels as $vessel) {
                if ($vessel->policy->underwriter) {
                    $vessel->policy->underwriter->notify(
                        new BoilerCertificateExpiringNotification($vessel, $days)
                    );
                }
            }

            $this->info("Dispatched alerts for {$vessels->count()} vessels expiring in {$days} days.");
        }

        return Command::SUCCESS;
    }
}
```

The command checks exact day boundaries (60, 30, and 7 days) to ensure clients are warned progressively as the deadline nears.

## Step 3: Register in the Laravel Scheduler

Add the command to your application console schedule:

`routes/console.php:`
```php
<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('boiler:check-expirations')
    ->dailyAt('06:00')
    ->runInBackground();
```

The check runs automatically every morning at 06:00 UTC without blocking other scheduled maintenance tasks.

## What Can Go Wrong

- **Notification Spam from Daily Range Queries:** If you query using `<= now()->addDays(30)` without deduplicating sent notifications, clients will receive an alert every single morning for 30 consecutive days. Always query for exact target milestones (`whereDate('...', $targetDate)`) or record a sent log table.
- **Timezone Skew on Certificate Expiration:** Operating plants across multiple timezones might cross expiry dates before headquarters checks run. Store expiration dates in normalized UTC or evaluate based on the plant's local timezone.

## Summary

Automating statutory inspection alerts transforms passive policy administration into active risk management, protecting underwriters from uninspected machinery losses and keeping industrial clients compliant.

## Further Reading

- [Laravel Task Scheduling Documentation](https://laravel.com/docs/scheduling)
- [Laravel Queueable Notifications](https://laravel.com/docs/notifications)
- [Industrial Plant Safety Statutory Inspection Guides](https://www.hse.gov.uk/pressure-systems/)

Next step: run `php artisan boiler:check-expirations` in staging to verify notifications.
