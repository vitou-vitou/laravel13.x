# When Jobs Fail: Design a Dead-Letter Alert and Recovery Runbook That Works

A critical queue job responsible for generating tax invoices throws an unhandled exception due to a missing nullable database column. The job retries three times, exhausts its attempts, and lands silently in the `failed_jobs` table. Nobody notices for three weeks until accounting discovers that 4,000 customers never received their receipts. A panicking engineer runs `php artisan queue:retry all` without diagnosing the issue, triggering a cascade of duplicate emails and crashing the mail server.

A dead-letter queue that nobody monitors is not a safety net; it is a cemetery for silent outages. When background tasks fail permanently, your engineering team needs immediate notifications with diagnostic context and an explicit recovery runbook. If you automate failure alerts via Laravel's queue events and establish a disciplined retry workflow, you can diagnose, fix, and safely replay failed jobs in minutes.

## The Silent Failure Trap

Laravel automatically records permanently failed jobs in the `failed_jobs` table if you configured a failed queue database driver.

However, having records in `failed_jobs` does nothing if no human is notified when they occur:

```bash
# What you see when you check the database three weeks too late:
+-------+--------------------+------------------------+---------------------+
| id    | queue              | payload                | failed_at           |
+-------+--------------------+------------------------+---------------------+
| 1042  | invoices           | {"job":"Generate...    | 2026-09-01 10:14:02 |
| 1043  | invoices           | {"job":"Generate...    | 2026-09-01 10:14:05 |
| ...   | ...                | ...                    | ...                 |
| 5042  | invoices           | {"job":"Generate...    | 2026-09-22 18:30:10 |
+-------+--------------------+------------------------+---------------------+
```

Four thousand customers were left in limbo because the error happened in a detached background worker process outside your standard HTTP web request logs.

## Real-Time Failure Alerting with Queue Events

Hook into Laravel's `Queue::failing()` event to notify your on-call engineering team the second a job exhausts its retries:

app/Providers/AppServiceProvider.php:
```php
namespace App\Providers;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use App\Notifications\CriticalJobFailedNotification;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Queue::failing(function (JobFailed $event) {
            // Extract diagnostic context from the failed job
            $jobName = $event->job->resolveName();
            $exception = $event->exception;
            $queueName = $event->job->getQueue();

            Log::critical("Queue Job Permanently Failed: {$jobName} on queue '{$queueName}'", [
                'job_name' => $jobName,
                'queue' => $queueName,
                'connection' => $event->connectionName,
                'exception_message' => $exception->getMessage(),
                'exception_file' => "{$exception->getFile()}:{$exception->getLine()}",
            ]);

            // Dispatch instant alert to team Slack or incident channel
            Notification::route('slack', config('services.slack.alerts_webhook'))
                ->notify(new CriticalJobFailedNotification($jobName, $queueName, $exception));
        });
    }
}
```

The moment a job fails its final attempt, an alert lands in your Slack incident channel containing the job class, queue name, and exact exception stack trace.

## The 4-Step Recovery Runbook

When an alert triggers, never jump directly to `queue:retry all`. Follow this structured four-step runbook:

### Step 1: Inspect the Failed Job Context

Inspect the failing job's details from the command line:

```bash
# View list of recent failed jobs
php artisan queue:failed

# View full stack trace and serialized payload for a specific failure
php artisan queue:failed --id=1042
```

Identify the root cause: Was it an expired third-party API token? A missing database column? An unhandled null value in the customer's address?

### Step 2: Deploy the Fix or Resolve the External Dependency

Never replay a failed job until the underlying problem is resolved:
- If the failure was caused by a code bug or missing migration, deploy the fix to production first.
- If caused by an upstream service outage (such as Stripe or SendGrid), verify that the upstream status page is green before retrying.

### Step 3: Test Replay with a Single Record

Before retrying thousands of jobs, replay exactly one failed record to prove the fix works in production:

```bash
# Retry only job ID 1042
php artisan queue:retry 1042
```

Monitor your logs and queue dashboard. Did job #1042 complete successfully? Did it generate the invoice? Did it send the receipt without throwing an error?

### Step 4: Safely Replay or Prune the Remaining Backlog

Once the single-record test succeeds, replay the remaining failed jobs:

```bash
# Replay all remaining failed jobs across the system
php artisan queue:retry all
```

If certain jobs failed due to permanent bad input (such as an invalid test email from a spam bot) that should never be reprocessed, delete them explicitly:

```bash
# Delete a specific corrupted job from the failed queue
php artisan queue:forget 1045

# Or prune failed jobs older than 7 days
php artisan queue:prune-failed --hours=168
```

## What Can Go Wrong

A major hazard during a large batch retry is overwhelming your database or mail provider.

If you run `queue:retry all` on 10,000 failed email jobs simultaneously, your queue workers will attempt to send 10,000 emails in sixty seconds, potentially triggering outbound rate limits or landing your domain on spam blacklists.

If you must replay thousands of jobs, retry them in batches or scale your worker concurrency deliberately to pace the outbound load.

## Summary

A dead-letter queue is only valuable if failures are detected and resolved quickly.

Automate real-time alerts via `Queue::failing()` to ensure failures are visible immediately. Follow a disciplined recovery runbook: diagnose the stack trace, deploy the fix, test a single retry, and only then replay the full backlog.

Treating background worker failures with the same urgency as web 500 errors ensures that your asynchronous architecture remains trustworthy and resilient.

## Further Reading

- [Laravel Queues: Dealing With Failed Jobs](https://laravel.com/docs/queues#dealing-with-failed-jobs)
- [Laravel Queue Events Documentation](https://laravel.com/docs/queues#queue-events)
- [Site Reliability Engineering: Incident Response Playbooks](https://sre.google/sre-book/incident-management/)
- [Designing Dead-Letter Exchanges in Distributed Systems](https://aws.amazon.com/what-is/dead-letter-queue/)

What does your team's runbook look like when background jobs fail in production? Tell us about your monitoring setup in the comments below.
