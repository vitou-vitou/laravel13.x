# 11 — Queues, Jobs, Scheduling

> If it takes more than 100ms, queue it. If it depends on time, schedule it. Async or die.

---

## The Senior's Queue Doctrine

1. **Web requests should return in < 200ms.** Anything slower → queue.
2. **Jobs are idempotent.** Re-running is safe.
3. **Jobs are retry-able.** Transient failures self-heal.
4. **Jobs are observable.** Horizon shows everything.
5. **Jobs have timeouts.** Never wait forever.
6. **Jobs are versioned with code.** Old jobs work with new code.

---

## Queue Backend: Redis on Render

`.env`:
```env
QUEUE_CONNECTION=redis
REDIS_URL=redis://default:password@host:6379
```

`config/queue.php`:
```php
'redis' => [
    'driver' => 'redis',
    'connection' => 'default',
    'queue' => env('REDIS_QUEUE', 'default'),
    'retry_after' => 90,
    'block_for' => null,
    'after_commit' => true,    // CRITICAL: dispatch only after DB commit
],
```

`after_commit => true` prevents the #1 queue bug: job fires before DB transaction commits, fails to find the record.

---

## Horizon (The Senior's Choice)

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```

`config/horizon.php`:
```php
'environments' => [
    'production' => [
        'supervisor-default' => [
            'connection' => 'redis',
            'queue' => ['default'],
            'balance' => 'auto',
            'maxProcesses' => 10,
            'minProcesses' => 1,
            'memory' => 128,
            'tries' => 3,
            'timeout' => 60,
        ],
        'supervisor-mail' => [
            'connection' => 'redis',
            'queue' => ['mail'],
            'balance' => 'auto',
            'maxProcesses' => 5,
            'memory' => 64,
            'tries' => 5,
            'timeout' => 30,
        ],
        'supervisor-heavy' => [
            'connection' => 'redis',
            'queue' => ['heavy', 'reports'],
            'balance' => 'simple',
            'maxProcesses' => 3,
            'memory' => 512,
            'tries' => 2,
            'timeout' => 900,
        ],
    ],
],
```

Auto-scales workers based on queue load. Per-queue tuning. Memory caps.

### Horizon Dashboard

Visit `/horizon` (auth required):
- Live job throughput
- Failed jobs (retry/forget)
- Job runtime distribution
- Worker metrics

**Hide in production:**
```php
// app/Providers/HorizonServiceProvider.php
protected function gate(): void
{
    Gate::define('viewHorizon', fn($user = null) =>
        $user && $user->hasRole('super-admin')
    );
}
```

### Render Worker Service

`render.yaml`:
```yaml
  - type: worker
    name: my-saas-horizon
    runtime: docker
    dockerCommand: php artisan horizon
    autoDeploy: true
    plan: starter
    envVars:
      - fromGroup: shared
```

Horizon supervises all queue workers. One Render service = many internal workers.

---

## Writing a Job (Senior Standard)

```php
namespace App\Jobs;

use App\Models\Order;
use App\Services\Billing\StripeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ChargeCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;        // 60, 120, 180 between retries
    public int $timeout = 30;
    public int $maxExceptions = 3;
    public string $queue = 'billing';

    public function __construct(
        public readonly Order $order,
    ) {}

    public function handle(StripeService $stripe): void
    {
        if ($this->order->isPaid()) {
            return;  // idempotent
        }

        $charge = $stripe->charge($this->order);

        $this->order->markAsPaid($charge->id);

        dispatch(new SendReceiptEmail($this->order));
    }

    public function failed(\Throwable $e): void
    {
        $this->order->markAsPaymentFailed($e->getMessage());

        Notification::route('slack', config('app.alert_webhook'))
            ->notify(new PaymentFailedNotification($this->order, $e));
    }

    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(10);  // give up after 10 min total
    }
}
```

Every job has:
- `tries` / `backoff` (retry strategy)
- `timeout` (kill if hangs)
- Idempotency check (safe to re-run)
- `failed()` (cleanup on terminal failure)
- Constructor for state (auto-serialized)

---

## Dispatching Jobs

```php
// Basic
ChargeCustomer::dispatch($order);

// On specific queue
ChargeCustomer::dispatch($order)->onQueue('billing');

// Delayed
ChargeCustomer::dispatch($order)->delay(now()->addMinutes(10));

// After commit (default if after_commit=true)
DB::transaction(function () use ($order) {
    $order->save();
    ChargeCustomer::dispatch($order);  // fires after commit
});

// Sync (testing only — don't in production code)
ChargeCustomer::dispatchSync($order);

// To same worker (chained)
Bus::chain([
    new ChargeCustomer($order),
    new SendReceipt($order),
    new UpdateInventory($order),
])->dispatch();

// Batched
Bus::batch([
    new SendEmail($u1),
    new SendEmail($u2),
    new SendEmail($u3),
])
->name('newsletter-blast')
->onQueue('mail')
->dispatch();
```

---

## Job Batching (Senior Power Tool)

```php
$batch = Bus::batch([])
    ->name('user-import')
    ->onQueue('imports')
    ->then(fn(Batch $b) => Mail::to($admin)->send(new ImportComplete($b)))
    ->catch(fn(Batch $b, Throwable $e) => Log::error($e))
    ->finally(fn(Batch $b) => Cache::forget('import:lock'))
    ->dispatch();

foreach ($csv as $row) {
    $batch->add(new ImportUserRow($row));
}
```

Process 100k jobs. Track progress. Hook into completion. All in one batch.

```php
$batch->totalJobs;       // total
$batch->processedJobs(); // done
$batch->progress();      // 0-100%
$batch->failedJobs;      // failed
```

---

## Job Middleware

```php
public function middleware(): array
{
    return [
        new RateLimited('stripe-api'),    // throttle by Redis
        new WithoutOverlapping($this->order->id),  // 1 concurrent per order
        new ThrottlesExceptions(10, 5),   // back off if 10 failures in 5 min
    ];
}
```

Lock per resource. Throttle external API. Backoff during outages.

---

## Long-Running Jobs

For jobs > 5 min:

```php
public int $timeout = 1800;  // 30 minutes
public int $tries = 1;       // don't retry hour-long jobs

public function uniqueId(): string
{
    return "report:{$this->reportId}";
}

public int $uniqueFor = 3600;   // lock for 1 hour
```

`ShouldBeUnique` prevents two of the same job running.

```php
class GenerateMonthlyReport implements ShouldQueue, ShouldBeUnique
{
    public function uniqueFor(): int
    {
        return 3600;
    }
    // ...
}
```

---

## Failed Job Handling

Failed jobs land in `failed_jobs` table. View in Horizon.

### Retry Manually

```bash
php artisan queue:retry all
php artisan queue:retry 5  # specific job ID
```

### Auto-Prune Old Failures

```php
// app/Console/Kernel.php
$schedule->command('queue:prune-failed --hours=720')->daily();   // 30 days
$schedule->command('queue:prune-batches --hours=720')->daily();
```

### Alert on Failure

Listen to `JobFailed`:
```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    JobFailed::class => [
        NotifySlackOfFailedJob::class,
    ],
];
```

```php
class NotifySlackOfFailedJob
{
    public function handle(JobFailed $event): void
    {
        Notification::route('slack', config('app.alert_webhook'))
            ->notify(new JobFailedNotification($event));
    }
}
```

---

## Queue Priorities

```php
'queue' => ['critical', 'high', 'default', 'low']
```

Horizon workers process `critical` first, then `high`, then `default`, then `low`.

```php
SendOtpSms::dispatch($user)->onQueue('critical');     // sub-second
ChargePayment::dispatch($order)->onQueue('high');      // minute
SendNewsletter::dispatch()->onQueue('low');             // hour
```

---

## Scheduled Tasks

`app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule): void
{
    // Frequencies
    $schedule->command('cleanup:expired')->everyMinute();
    $schedule->command('reports:hourly')->hourly();
    $schedule->command('reports:daily')->dailyAt('03:00');
    $schedule->command('reports:weekly')->weeklyOn(1, '04:00');  // Mon 4am
    $schedule->command('reports:monthly')->monthlyOn(1, '05:00');
    $schedule->command('backup:run')->dailyAt('02:00');

    // Closures
    $schedule->call(function () {
        DB::table('users')->where('last_login_at', '<', now()->subYear())->delete();
    })->yearly();

    // Jobs
    $schedule->job(new SyncFromStripe)->everyFifteenMinutes();

    // Modifiers
    $schedule->command('reports:heavy')
        ->dailyAt('03:00')
        ->withoutOverlapping(60)   // skip if previous still running
        ->onOneServer()             // only run on one instance (Redis lock)
        ->runInBackground()         // don't block scheduler
        ->sendOutputTo(storage_path('logs/heavy-report.log'))
        ->emailOutputOnFailure('alerts@example.com');
}
```

### Render Cron Setup

ONE cron service runs `php artisan schedule:run` every minute. Laravel decides what to do.

`render.yaml`:
```yaml
  - type: cron
    name: my-saas-scheduler
    runtime: docker
    schedule: "* * * * *"
    dockerCommand: php artisan schedule:run
```

---

## Scheduler vs Job Queue

| Use Case | Use |
|----------|-----|
| Run daily at 3am | `$schedule->command()` |
| Process 10k emails right now | Queue jobs |
| Re-process after failure | Queue retries |
| Backup every night | Scheduler |
| User clicks "send" → send | Dispatch job |
| Cleanup orphaned files | Scheduler (calls job) |

Pattern: scheduler **dispatches** jobs. Workers **process** them.

```php
$schedule->call(function () {
    User::active()->lazyById()->each(fn($u) =>
        SendDailyDigest::dispatch($u)
    );
})->dailyAt('07:00');
```

---

## Queue Monitoring

### Horizon Dashboard

Built-in. Free. See:
- Active jobs
- Failed jobs
- Throughput per minute
- Per-queue load

### Alert on Queue Backlog

```php
// app/Console/Kernel.php
$schedule->call(function () {
    $size = Redis::connection()->llen('queues:default');
    if ($size > 1000) {
        Notification::route('slack', config('app.alert_webhook'))
            ->notify(new QueueBacklog($size));
    }
})->everyFiveMinutes();
```

### Alert on Long-Running Jobs

```php
// Horizon's metrics
$schedule->call(function () {
    $runtime = Horizon::longestWaitAndQueue();
    if ($runtime['wait'] > 60) {
        Log::warning('Queue backlog exceeds 60s', $runtime);
    }
})->everyMinute();
```

---

## Job Serialization Gotchas

### `SerializesModels` Re-Fetches

```php
public function __construct(public Order $order) {}

// Job runs later. Eloquent re-fetches the Order from DB.
// If the row was deleted, you get ModelNotFoundException.
```

Defense:
```php
public function handle(): void
{
    if (!$this->order->exists) {
        return;  // model gone, skip
    }
    // ...
}
```

### Large Payloads

```php
public function __construct(public array $millionRows) {}
```

Serializes 100MB into Redis. Disaster.

Better:
```php
public function __construct(public int $batchId) {}

public function handle(): void
{
    $rows = Batch::find($this->batchId)->rows;
}
```

Store in DB, pass ID.

### Closures Don't Serialize

```php
dispatch(function () {
    // Closure jobs
});
```

Works but tricky. Use `Bus::queue()` or proper class jobs.

---

## Idempotency Patterns

```php
public function handle(): void
{
    // Pattern 1: check state
    if ($this->order->status === 'paid') {
        return;
    }

    // Pattern 2: idempotency key with cache
    $key = "charge:{$this->order->id}";
    if (Cache::has($key)) {
        return;
    }
    Cache::put($key, true, 3600);

    // Pattern 3: external API idempotency
    $stripe->charges->create([
        'amount' => $this->order->total,
    ], [
        'idempotency_key' => "order-{$this->order->id}",
    ]);

    // ...
}
```

The cost of an idempotent job: 1 line of code.
The cost of double-charging a customer: your business.

---

## Database Transactions in Jobs

```php
public function handle(): void
{
    DB::transaction(function () {
        $this->order->markAsPaid();
        $this->order->user->incrementCredits(100);
        $this->order->items->each->reserve();
    });
}
```

ACID. If any step fails, everything rolls back. Re-run safe.

---

## Job Dependencies

When one job needs another's result:

```php
Bus::chain([
    new ExportData,        // 5 min
    new UploadToS3,        // 1 min
    new NotifyUser,        // instant
])
->onQueue('reports')
->dispatch();
```

If `ExportData` fails, `UploadToS3` and `NotifyUser` don't run.

---

## Webhook Handling (Inbound)

```php
// routes/api.php
Route::post('/webhooks/stripe', function (Request $request) {
    // Verify signature FAST
    $payload = $request->getContent();
    $sig = $request->header('Stripe-Signature');

    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload, $sig, config('services.stripe.webhook_secret')
        );
    } catch (\Exception $e) {
        return response('Invalid signature', 400);
    }

    // Queue for processing
    ProcessStripeWebhook::dispatch($event->toArray())->onQueue('webhooks');

    // Respond fast (Stripe expects < 5s)
    return response('ok', 200);
});
```

The webhook handler ONLY:
1. Verifies signature
2. Dispatches a job
3. Returns 200

Job does the real work.

---

## Rate Limiting External APIs

```php
public function middleware(): array
{
    return [(new RateLimited('stripe'))->dontRelease()];
}
```

`AppServiceProvider`:
```php
RateLimiter::for('stripe', fn() => Limit::perMinute(100));
```

If you hit limit, job waits. No 429s leak to users.

---

## Long-Polling vs Short-Polling

In `config/queue.php`:
```php
'block_for' => 5,  // long-poll Redis for 5s
```

Workers block on Redis for 5s waiting for jobs instead of busy-polling. Reduces CPU.

---

## Queue Worker Memory Leaks

Workers are long-lived. Memory accumulates. Workarounds:

`config/horizon.php`:
```php
'memory' => 128,  // restart at 128MB
```

Or:
```bash
php artisan queue:work --max-jobs=1000 --max-time=3600
```

Worker exits after 1000 jobs OR 1 hour. Supervisor restarts it. Fresh memory.

---

## Testing Jobs

```php
it('dispatches a charge job on order placement', function () {
    Queue::fake();

    $this->actingAs(User::factory()->create())
         ->post('/orders', [...])
         ->assertOk();

    Queue::assertPushed(ChargeCustomer::class);
});

it('charge job marks order paid', function () {
    Http::fake([
        'api.stripe.com/*' => Http::response(['id' => 'ch_123']),
    ]);

    $order = Order::factory()->create(['status' => 'pending']);

    (new ChargeCustomer($order))->handle(app(StripeService::class));

    expect($order->fresh()->status)->toBe('paid');
});

it('charge job is idempotent', function () {
    $order = Order::factory()->paid()->create();

    (new ChargeCustomer($order))->handle(app(StripeService::class));

    Http::assertNothingSent();  // no double charge
});
```

---

## Anti-Patterns to Refuse

1. **Synchronous external API calls in HTTP requests.** Queue them.
2. **Long-running jobs without unique lock.** Cron triggers two = chaos.
3. **Jobs that update many tables without transactions.** Half-committed state.
4. **Jobs with no `failed()` method on critical paths.** Silent loss.
5. **Catching all exceptions in jobs.** Hides bugs from Horizon.
6. **Jobs that depend on `auth()`.** No user in queue context.
7. **`->dispatchSync()` in production.** Defeats the queue.
8. **Storing big arrays in job payload.** Use IDs.
9. **Workers without memory caps.** OOM kills.
10. **One queue for everything.** Critical work blocks behind newsletters.

---

## The Senior's Queue Discipline

| Cadence | Action |
|---------|--------|
| Daily | Glance at Horizon, watch for backlog |
| Weekly | Review failed jobs, fix root causes |
| Monthly | Tune queue priorities, worker counts |
| Quarterly | Load test queue throughput |

Queues are infrastructure. Treat them with the same respect as DB.
