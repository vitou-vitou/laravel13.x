# Laravel Horizon in Production: Prioritize Interactive Jobs Over Nightly Exports

An administrator initiates an export of 100,000 customer orders to generate a quarterly accounting report. A few seconds later, an active customer clicks "Forgot Password" on your login page. Instead of receiving their password reset link in three seconds, the customer waits forty-five minutes because their verification email was pushed to the exact same `default` queue behind 100,000 export jobs.

Pushing all background tasks to a single queue creates catastrophic head-of-line blocking. A spike in low-priority background exports will completely starve critical, time-sensitive user actions. If you separate your background workload into priority queues and configure Laravel Horizon with dedicated supervisors and balancing rules, you can process massive batch jobs while guaranteeing sub-second response times for interactive user requests.

## The Danger of a Single Queue

When you run queue workers without specifying distinct queues, all jobs enter a single FIFO (First-In, First-Out) pipeline:

```php
// Anti-pattern: all jobs share the same 'default' queue
PasswordResetJob::dispatch($user);     // High priority: user is waiting at their screen!
ExportAllOrdersJob::dispatch($filter); // Low priority: can take 30 minutes without issue
```

If an export job splits into 10,000 sub-jobs, your queue workers will spend the next twenty minutes processing rows. Any password reset, two-factor code, or payment confirmation queued during that window is trapped at the back of the line.

## Separate Workloads into Distinct Queues

Categorize your background tasks into at least three explicit tiers:

1. **`high`:** Interactive tasks where a human is waiting at a screen (password resets, OTP codes, payment confirmations, synchronous webhook deliveries).
2. **`default`:** Normal background work (welcome emails, order shipped notifications, activity logging).
3. **`low`:** Asynchronous batch jobs (CSV exports, PDF generation, data warehouse syncing, nightly cleanup).

Dispatch jobs directly to their respective queue:

app/Jobs/SendPasswordResetNotificationJob.php:
```php
namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPasswordResetNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $user, public string $token)
    {
        // Enforce execution on the high priority queue
        $this->onQueue('high');
    }

    public function handle(): void
    {
        $this->user->sendPasswordResetNotification($this->token);
    }
}
```

For lower priority tasks, assign them to `low`:

```php
GenerateAnnualCsvExportJob::dispatch($reportId)->onQueue('low');
```

## Configure Strict Priority with Artisan Workers

If you run queue workers using standard Artisan commands, specify the queue priority using a comma-separated list:

```bash
php artisan queue:work --queue=high,default,low
```

The order of arguments defines strict priority:
1. The worker checks the `high` queue. If a job exists, it executes it immediately.
2. Only when `high` is completely empty will the worker process a job from `default`.
3. Only when both `high` and `default` are empty will it touch `low`.

Even if the `low` queue contains 200,000 export jobs, the moment a password reset enters `high`, the next available worker picks it up in milliseconds.

## Enterprise Balancing with Laravel Horizon

In large-scale production environments running on Redis, Laravel Horizon provides dashboard visibility and dynamic process balancing.

Configure dedicated supervisors in `config/horizon.php` to prevent queue starvation:

config/horizon.php:
```php
'environments' => [
    'production' => [
        // Dedicated supervisor for high-priority interactive jobs
        'supervisor-interactive' => [
            'connection' => 'redis',
            'queue' => ['high'],
            'balance' => 'simple',
            'processes' => 10,
            'maxProcesses' => 15,
            'tries' => 3,
        ],

        // Dynamic supervisor for general and batch jobs
        'supervisor-default' => [
            'connection' => 'redis',
            'queue' => ['default', 'low'],
            'balance' => 'auto',
            'autoScalingStrategy' => 'time',
            'minProcesses' => 5,
            'maxProcesses' => 20,
            'balanceMaxShift' => 2,
            'balanceCooldown' => 3,
            'tries' => 3,
        ],
    ],
],
```

Notice the architecture:
- `supervisor-interactive` maintains dedicated worker processes that **only** listen to the `high` queue. Even if a massive data import floods the system, these workers never touch export jobs.
- `supervisor-default` uses `'balance' => 'auto'`, which dynamically shifts worker processes between `default` and `low` based on which queue has the longest wait time.

## What Can Go Wrong

A major risk with strict priority (`--queue=high,default,low`) is **queue starvation**.

If your application experiences sustained traffic spikes on the `high` and `default` queues, the `low` queue may never be processed. An overnight export scheduled at 2:00 AM might sit untouched until 8:00 AM because continuous high-priority traffic kept workers occupied.

To prevent starvation:
1. Allocate at least one dedicated worker process exclusively to the `low` queue.
2. In Horizon, use separate supervisor pools so that background batch processing always has guaranteed compute capacity.

## Summary

Never dump all background work into a single `default` queue. Classify background tasks into `high`, `default`, and `low` tiers.

Dispatch interactive, user-facing notifications to the `high` queue, configure queue worker priority order, and use Laravel Horizon supervisors to isolate critical workflows from background bulk exports.

Your background exports can run for hours without ever delaying a customer's login or payment confirmation.

## Further Reading

- [Laravel Queues: Queue Priorities](https://laravel.com/docs/queues#queue-priorities)
- [Laravel Horizon Documentation](https://laravel.com/docs/horizon)
- [Head-of-Line Blocking in Queueing Systems](https://en.wikipedia.org/wiki/Head-of-line_blocking)
- [Redis Queue Performance Tuning](https://redis.io/topics/memory-optimization)

How do you organize your queue worker pools in production? Share your supervisor setups in the comments below.
