# Prevent Duplicate Queue Dispatches: Leverage Laravel's ShouldBeUnique Contract

A user clicks a "Sync Inventory" button three times in rapid succession, or an hourly scheduler triggers a data aggregation job before the previous run finished. Suddenly, five identical jobs are queued for the exact same resource. Multiple worker processes wake up simultaneously, compete for the same database rows, waste CPU cycles, and hammer third-party APIs with duplicate requests.

While idempotency protects your code during retries, preventing duplicate jobs from entering the queue in the first place saves significant compute and infrastructure costs. Laravel provides first-class support for unique jobs through the `ShouldBeUnique` interface. By defining custom unique identifiers and lock timeouts, you can ensure that only one instance of a specific job exists in your queue at any given time.

## The Problem of Redundant Queue Flooding

Consider a job that synchronizes stock counts for a product:

```php
// Standard job: multiple instances can flood the queue simultaneously
class SyncProductStockJob implements ShouldQueue
{
    public function __construct(public Product $product) {}
}
```

If five inventory update webhooks arrive within three seconds for Product #42, Laravel dispatches five instances of `SyncProductStockJob` for the same record. Five workers pull Product #42, execute the exact same queries, and write the exact same values five times.

## Implementing ShouldBeUnique

To prevent duplicates, implement the `ShouldBeUnique` contract on your job class:

app/Jobs/SyncProductStockJob.php:
```php
namespace App\Jobs;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncProductStockJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    // The lock expires automatically after 5 minutes if the worker crashes
    public int $uniqueFor = 300;

    public function __construct(public Product $product) {}

    /**
     * Define the unique identifier for the job lock.
     */
    public function uniqueId(): string
    {
        return (string) $this->product->id;
    }

    public function handle(): void
    {
        // Executes only if no other job with uniqueId() is currently queued or running
        $this->product->syncStockWithWarehouse();
    }
}
```

When you call `SyncProductStockJob::dispatch($product)`, Laravel checks whether an atomic cache lock exists for `laravel_unique_job:App\Jobs\SyncProductStockJob:42`.
- If no lock exists, Laravel acquires the lock and pushes the job onto the queue.
- If a lock already exists, Laravel silently discards the dispatch attempt.

The lock remains active until the job completes its execution in the worker, ensuring that only one instance of that job runs at a time.

## ShouldBeUnique vs. ShouldBeUniqueUntilProcessing

Depending on your requirements, you might want to allow a new job to be queued as soon as the current one starts executing:

1. **`ShouldBeUnique`:** The lock is held while the job is in the queue **AND** while it is being processed by the worker. Use this for operations that must never run concurrently (e.g. compiling a heavy video or running financial reconciliations).
2. **`ShouldBeUniqueUntilProcessing`:** The lock is released the moment a worker picks up the job and begins execution. This allows a new job to be queued for future updates while the current job finishes.

app/Jobs/RebuildSearchIndexJob.php:
```php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RebuildSearchIndexJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Queueable;

    public function uniqueId(): string
    {
        return 'global-search-reindex';
    }

    public function handle(): void
    {
        // Lock was released as soon as handle() was called
        // If another event dispatches RebuildSearchIndexJob now, it will queue cleanly for the NEXT run
        SearchIndexer::rebuildAll();
    }
}
```

This ensures that any changes occurring while the indexer is actively running will trigger a fresh rebuild afterward.

## What Can Go Wrong

The most frequent bug with unique jobs is relying on a cache driver that does not support atomic locks.

If your `.env` is configured with `CACHE_STORE=database` on an older database without atomic locking or `CACHE_STORE=file` across multiple servers, Laravel cannot guarantee atomicity. Two dispatch calls running simultaneously on different web servers can both pass the uniqueness check and queue duplicate jobs.

To guarantee true uniqueness, ensure your default cache driver is backed by Redis or Memcached:

.env:
```ini
CACHE_STORE=redis
```

Also, always configure `$uniqueFor`. If your worker server experiences a sudden power loss or kernel panic while holding a unique lock, an unconfigured lock could remain permanently active, blocking that entity from ever being queued again.

## Summary

Do not waste server CPU and database connections processing duplicate background jobs.

Implement Laravel's `ShouldBeUnique` contract to prevent duplicate dispatches for the same resource. Define a specific `uniqueId()`, set a reasonable `$uniqueFor` lock duration, and choose `ShouldBeUniqueUntilProcessing` when subsequent updates need to be queued immediately.

Your queue stays clean, your background workers stay efficient, and duplicate operations are stopped before they ever enter the pipeline.

## Further Reading

- [Laravel Queues: Unique Jobs](https://laravel.com/docs/queues#unique-jobs)
- [Laravel Cache: Atomic Locks](https://laravel.com/docs/cache#atomic-locks)
- [Distributed Locks with Redis](https://redis.io/docs/manual/patterns/distributed-locks/)
- [Preventing Concurrency Issues in Asynchronous Systems](https://aws.amazon.com/builders-library/reliable-concurrency/)

Have you used `ShouldBeUnique` to prevent queue duplication in your apps? Tell us about your use case in the comments below.
