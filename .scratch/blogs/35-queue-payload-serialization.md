# Queue Payload Serialization: Why Passing Eloquent Models Can Silently Fail

You create a user in a web controller, dispatch a welcome email job with `SendWelcomeEmailJob::dispatch($user)`, and return a redirect. In production, your logs immediately fill with `Illuminate\Database\Eloquent\ModelNotFoundException: No query results for model [App\Models\User] 42`. You query the database manually and confirm that User #42 exists and is completely intact. The job crashed because the background worker picked up the task and re-queried the database before your web request's transaction had committed.

Laravel's `SerializesModels` trait does not serialize your entire Eloquent model into the queue. It serializes only the model's class name, primary key, and database connection. When a worker executes the job, it re-hydrates the model fresh from the database. If you understand this re-hydration lifecycle and use the `afterCommit` dispatch option, you eliminate race conditions and keep your background workers reliable.

## How SerializesModels Actually Works

When you pass an Eloquent model into a queued job:

```php
class ProcessInvoiceJob implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice) {}
}
```

Laravel does not store the invoice's columns, line items, or attributes in Redis or your database queue table. The serialized JSON payload stored in the queue looks like this:

```json
{
  "displayName": "App\\Jobs\\ProcessInvoiceJob",
  "job": "Illuminate\\Queue\\CallQueuedHandler@call",
  "data": {
    "command": "O:26:\"App\\Jobs\\ProcessInvoiceJob\":1:{s:7:\"invoice\";O:45:\"Illuminate\\Contracts\\Database\\ModelIdentifier\":4:{s:5:\"class\";s:18:\"App\\Models\\Invoice\";s:2:\"id\";i:42;s:10:\"connection\";s:5:\"mysql\";s:15:\"relations\";a:0:{};}}"
  }
}
```

Notice `ModelIdentifier`: Laravel records only the class name `App\Models\Invoice` and the primary key `42`.

When the worker process pulls the job from Redis, the `SerializesModels` trait catches the identifier and executes:

```php
$this->invoice = Invoice::on('mysql')->findOrFail(42);
```

The worker always works with the latest, authoritative database state rather than stale data from the moment of dispatch.

## Trap #1: Dispatching Inside an Uncommitted Transaction

The most common reason for `ModelNotFoundException` in queue workers is race conditions caused by database transactions:

```php
// Anti-pattern: dispatching before the database transaction commits
DB::transaction(function () use ($request) {
    $order = Order::create($request->validated());

    // Pushed to Redis instantly!
    SendOrderConfirmationJob::dispatch($order);

    // This commit takes another 30ms to finish on disk
});
```

Here is the race condition:
1. The web process creates the order inside an open SQL transaction.
2. The job is dispatched immediately to Redis.
3. A fast worker container on the same network pulls the job from Redis within 5 milliseconds.
4. The worker runs `Order::findOrFail(42)`.
5. Because the web request's transaction has not yet committed to the database, User #42 is invisible to the worker!
6. The job immediately crashes with `ModelNotFoundException`.

### The Solution: Dispatch After Commit

Instruct Laravel to hold the queue dispatch until the wrapping database transaction has safely committed:

app/Jobs/SendOrderConfirmationJob.php:
```php
namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

class SendOrderConfirmationJob implements ShouldQueue
{
    use Queueable, SerializesModels;

    // Guaranteed: only pushed to Redis AFTER database transactions commit
    public bool $afterCommit = true;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        $this->order->customer->notify(new OrderConfirmedNotification($this->order));
    }
}
```

Alternatively, configure this globally in `config/queue.php`:

config/queue.php:
```php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'after_commit' => true, // Enforce across all Redis jobs by default
    ],
],
```

With `after_commit => true`, Laravel holds the Redis dispatch in memory until the SQL `COMMIT` succeeds. The record is guaranteed to exist before the worker touches the job.

## Trap #2: Handling Deleted and Soft-Deleted Records

If a record is deleted between the time a job is queued and the time a worker runs, `findOrFail()` will throw an exception and send the job to `failed_jobs`.

If your job should gracefully ignore deleted records, configure `deleteWhenMissingModels`:

app/Jobs/UpdateSearchIndexJob.php:
```php
namespace App\Jobs;

use App\Models\Article;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

class UpdateSearchIndexJob implements ShouldQueue
{
    use Queueable, SerializesModels;

    // Discard the job cleanly without error if the article was deleted
    public bool $deleteWhenMissingModels = true;

    public function __construct(public Article $article) {}

    public function handle(): void
    {
        SearchEngine::sync($this->article);
    }
}
```

When the worker discovers that Article #42 was deleted, it discards the job silently with zero error alerts.

## Passing Scalar IDs vs. Full Models

Passing full models is expressive, but if your job only needs an integer ID to perform a raw update or log an audit entry:

```php
// Pass scalar values when full model hydration is unnecessary
class LogDownloadJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $userId, public int $fileId) {}

    public function handle(): void
    {
        DB::table('downloads')->insert([
            'user_id' => $this->userId,
            'file_id' => $this->fileId,
            'downloaded_at' => now(),
        ]);
    }
}
```

Passing primitive integers avoids the overhead of Eloquent re-querying and model hydration altogether.

## What Can Go Wrong

A major performance problem occurs when you load heavy relationships on a model before dispatching:

```php
// Anti-pattern: passing models with deeply loaded relationships
$order->load(['customer', 'items.product', 'shippingAddress']);
ProcessOrderJob::dispatch($order);
```

When Laravel serializes loaded relations, the worker must re-query every single one of those relations upon hydration. If you dispatch 500 jobs, your worker will execute thousands of re-hydration queries against your database.

Only load relationships inside the worker's `handle()` method where they are actually needed.

## Summary

Remember that Laravel queue workers do not serialize model data; they re-query models from the database using their primary key.

Always configure `$afterCommit = true` on queued jobs that depend on newly created records to avoid `ModelNotFoundException` race conditions. Use `deleteWhenMissingModels = true` when records might be deleted before processing, and pass primitive scalar IDs when full model hydration is unnecessary.

Understanding how Eloquent models travel across the queue wire ensures your background jobs execute reliably without mysterious missing record errors.

## Further Reading

- [Laravel Queues: Handling Model Serialization](https://laravel.com/docs/queues#ignoring-missing-models)
- [Laravel Queues: Jobs and Database Transactions](https://laravel.com/docs/queues#jobs-and-database-transactions)
- [Database Transaction Isolation Levels Explained](https://dev.mysql.com/doc/refman/8.0/en/innodb-transaction-isolation-levels.html)
- [Clean Architecture: Avoiding Leaky Entity Abstractions](https://martinfowler.com/bliki/EvansClassification.html)

Have you encountered race conditions between database transactions and queue workers? Share how you solved them in the comments below.
