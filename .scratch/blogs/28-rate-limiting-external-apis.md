# Respect Third-Party API Limits in Laravel: Redis Throttles for Outbound Requests

Your background workers need to synchronize 5,000 products with Shopify or push delivery updates to a third-party shipping API. You spin up ten parallel queue workers to process the backlog quickly. Within twenty seconds, all ten workers crash with `429 Too Many Requests`. The third-party API provider flags your application for abuse, temporarily revokes your API key, and your entire inventory sync goes dark for the rest of the afternoon.

Putting `sleep(1)` inside your queue workers does not solve the problem. If you run multiple server instances or multiple worker processes, they cannot coordinate sleep timers with each other. By using Laravel's Redis-backed job throttles and releasing jobs back to the queue when limits are approached, you can process high-volume background queues at maximum permissible speed without ever violating upstream API rate limits.

## The Flaw of sleep() in Multi-Worker Environments

When developers hit rate limits, their initial response is often to insert a sleep call:

```php
// Anti-pattern: sleeps block the worker and fail across distributed processes
public function handle(): void
{
    sleep(1); // Freezes the worker process for 1 second!
    Http::post('https://api.thirdparty.com/sync', $this->payload);
}
```

This approach has two fatal flaws:
1. **Wasted Worker Compute:** While sleeping, the worker process is completely blocked. It consumes server memory while doing zero useful work.
2. **Zero Distributed Coordination:** If you run ten parallel queue workers across two servers, all ten workers can wake up at the exact same millisecond and fire ten simultaneous requests, immediately blowing past the third-party per-second rate limit.

## Redis-Backed Job Throttling

Laravel provides a built-in `Redis::throttle()` helper specifically designed to coordinate outbound rate limits across any number of distributed worker processes.

If a third-party API permits a maximum of 10 requests every 2 seconds, wrap your API call in a throttle block:

app/Jobs/SyncProductToShopifyJob.php:
```php
namespace App\Jobs;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

class SyncProductToShopifyJob implements ShouldQueue
{
    use Queueable;

    // Set maximum execution attempts before the job fails permanently
    public int $tries = 10;

    public function __construct(public Product $product) {}

    public function handle(): void
    {
        // Allow a maximum of 10 requests every 2 seconds across all workers
        Redis::throttle('shopify-api-limiter')
            ->allow(10)
            ->every(2)
            ->then(function () {
                // Throttle acquired: safe to execute outbound HTTP call
                Http::withToken(config('services.shopify.token'))
                    ->post("https://store.myshopify.com/admin/api/products", [
                        'sku' => $this->product->sku,
                        'price' => $this->product->price,
                    ])
                    ->throw();
            }, function () {
                // Limit reached: release the job back to the queue with a 3-second delay
                return $this->release(3);
            });
    }
}
```

Notice what happens when the rate limit is reached:
- Laravel does **not** fail the job.
- Laravel does **not** block or sleep the PHP worker process.
- The callback calls `$this->release(3)`, which puts the job back onto the queue to be picked up again in 3 seconds. The worker process is instantly freed to process an unrelated job from another queue.

All distributed workers share the same Redis rate limit key, ensuring that the aggregate traffic across your entire server fleet never exceeds 10 requests every 2 seconds.

## Handle HTTP 429 Status Codes Dynamically

Even with strict proactive rate limiting, third-party services can dynamically adjust limits based on server load. If an API returns an HTTP 429 status code with a `Retry-After` header, parse it and release the job accordingly:

app/Jobs/SyncProductToShopifyJob.php:
```php
use Illuminate\Http\Client\RequestException;

public function handle(): void
{
    Redis::throttle('shopify-api-limiter')
        ->allow(10)
        ->every(2)
        ->then(function () {
            $response = Http::withToken(config('services.shopify.token'))
                ->post('https://store.myshopify.com/admin/api/products', $this->payload);

            if ($response->status() === 429) {
                // Read upstream Retry-After header or default to 10 seconds
                $retryAfter = (int) $response->header('Retry-After', 10);
                return $this->release($retryAfter);
            }

            $response->throw();
        }, function () {
            return $this->release(3);
        });
}
```

When upstream limits drop unexpectedly, your queue slows down gracefully and resumes at full speed once the upstream throttle window resets.

## What Can Go Wrong

A major hazard when releasing throttled jobs is hitting the job's retry cap (`$tries`).

If you configure `public int $tries = 3;` and your worker encounters a busy rate limit three times in a row, Laravel will mark the job as permanently failed even though no real error occurred.

To prevent throttled releases from counting as failed attempts:
1. Increase `$tries` to a generous limit (e.g., `public int $tries = 20;`).
2. Alternatively, use `$this->retryUntil()` to give the job a time-based deadline rather than a count-based attempt cap:

```php
public function retryUntil(): \DateTimeInterface
{
    // Allow the job to be released and retried for up to 30 minutes
    return now()->addMinutes(30);
}
```

With `retryUntil()`, the job can release itself back to the queue dozens of times without failing until thirty minutes have elapsed.

## Summary

Never attempt to manage external API rate limits with `sleep()` statements inside your queue workers.

Coordinate outbound traffic across all workers using Laravel's `Redis::throttle()`. When the throttle ceiling is approached, release the job back to the queue with a delay using `$this->release()`, and configure time-based expiration via `retryUntil()`.

Your applications can process millions of external integrations at maximum speed while maintaining perfect compliance with third-party platform policies.

## Further Reading

- [Laravel Queues: Job Middleware & Throttling](https://laravel.com/docs/queues#job-middleware)
- [Laravel HTTP Client: Error Handling](https://laravel.com/docs/http-client#error-handling)
- [Shopify API Rate Limits and Call Limits](https://shopify.dev/docs/api/usage/rate-limits)
- [Redis Token Bucket and Leaky Bucket Algorithms](https://redis.io/glossary/rate-limiting/)

How do you throttle high-frequency outbound webhooks and API calls? Share your queue architectures in the comments below.
