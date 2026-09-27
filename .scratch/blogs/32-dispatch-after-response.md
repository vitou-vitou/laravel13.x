# Fast Web Responses: When to Use dispatchAfterResponse vs Background Queues

You have a web route that logs an internal audit event and warms a cache tag after a user updates their profile. The database write takes ten milliseconds, but the audit call and cache tags add another two hundred milliseconds. The user stares at a loading spinner waiting for work they do not need to see. You consider pushing the task to your Redis queue, but adding serialization, queue connection overhead, and worker infrastructure for a five-millisecond task feels like unnecessary complexity.

Laravel provides a middle ground between synchronous execution and dedicated queue workers: `dispatchAfterResponse()`. By taking advantage of PHP-FPM's `fastcgi_finish_request()` capability, you can deliver an instantaneous HTTP 200 response to the user's browser and execute cleanup tasks immediately afterward in the same process.

## How dispatchAfterResponse Works

In traditional PHP web requests, the browser holds the connection open and displays a loading indicator until the entire PHP script finishes executing and sends the response headers.

`dispatchAfterResponse()` changes this lifecycle:

```php
// Finishes sending the HTTP response to the browser FIRST, then executes this job
AuditProfileUpdateJob::dispatchAfterResponse($user);
```

Under the hood, Laravel calls `fastcgi_finish_request()`. This sends all HTTP response headers, flushes the output buffer to the user's browser, and cleanly terminates the client connection. To the user, the page load is complete.

Laravel then executes the job closure within the existing PHP-FPM worker before releasing that worker back to the pool.

## A Real-World Example: Fast Profile Updates

Here is how you can use `dispatchAfterResponse` to eliminate user-facing latency on an update action:

app/Http/Controllers/ProfileController.php:
```php
namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Jobs\LogUserActivityJob;
use App\Jobs\WarmUserDashboardCacheJob;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        // 1. Critical synchronous write
        $user->update($request->validated());

        // 2. Non-critical tasks deferred until AFTER the user receives the redirect
        LogUserActivityJob::dispatchAfterResponse($user, 'profile_updated');
        WarmUserDashboardCacheJob::dispatchAfterResponse($user);

        // 3. User receives the redirect immediately
        return redirect()->route('profile.show')
            ->with('status', 'Profile updated successfully.');
    }
}
```

The user receives the redirect within twenty milliseconds. The browser closes its network request, and the PHP process spends the next thirty milliseconds logging the audit trail and updating cache keys.

## dispatchAfterResponse vs. Dedicated Queue Workers

When should you use `dispatchAfterResponse()`, and when should you reach for a standard background queue (`ShouldQueue`)?

| Feature | `dispatchAfterResponse()` | Dedicated Queue (`ShouldQueue`) |
|---|---|---|
| Infrastructure Needed | None (built into PHP-FPM) | Redis, MySQL, or SQS + Worker Daemons |
| Latency to User | Instant (after output flush) | Instant (queued in background) |
| Max Task Duration | Under 200 milliseconds | Minutes or hours |
| Retry on Failure? | No (if it fails, it is lost) | Yes (automatic retries & dead-letter queue) |
| Worker Process Impact | Holds PHP-FPM worker briefly | Handled by dedicated CLI workers |

### When to use `dispatchAfterResponse`:
- Lightweight audit logging (< 50ms)
- Clearing or warming local cache tags
- Incrementing internal metrics or page view counters
- Triggering lightweight non-critical notifications

### When to use a dedicated background queue:
- Calling third-party APIs (Stripe, SendGrid, Shopify)
- Generating PDFs or manipulating images
- Processing data that must be retried if it fails
- Tasks lasting longer than half a second

## What Can Go Wrong

The most dangerous mistake with `dispatchAfterResponse()` is running slow, long-running tasks:

```php
// Anti-pattern: keeping PHP-FPM workers busy with slow tasks degrades server capacity!
GenerateHeavyPdfCatalogJob::dispatchAfterResponse($user);
```

While the client browser disconnected, that PHP-FPM worker process is still actively busy running your job for five seconds. If twenty users perform this action concurrently, you tie up twenty PHP-FPM processes, quickly exhausting your web server's available worker pool and causing incoming web requests to queue up in Nginx with 504 Gateway Timeouts.

Keep tasks inside `dispatchAfterResponse()` strictly under a couple of hundred milliseconds. If a task involves external network requests or disk-heavy processing, push it to a real background queue worker.

## Summary

You do not need to push every small asynchronous task to a dedicated Redis queue.

Use `dispatchAfterResponse()` for lightweight, non-critical follow-up tasks like cache warming and audit logging. The user experiences an instantaneous response, and you avoid the overhead of queue serialization and worker management.

Reserve your dedicated queue workers for the heavy, long-running, and retryable workloads where they truly belong.

## Further Reading

- [Laravel Queues: Dispatching After Response](https://laravel.com/docs/queues#dispatching-after-response)
- [PHP Manual: fastcgi_finish_request](https://www.php.net/manual/en/function.fastcgi-finish-request.php)
- [Optimizing PHP-FPM Worker Pools](https://serverpilot.io/docs/how-to-tune-php-fpm-pool-settings/)
- [Web Vitals: Interaction to Next Paint (INP)](https://web.dev/explore/inp)

Do you use `dispatchAfterResponse()` for lightweight logging or do you queue everything through Redis? Tell us about your approach in the comments below.
