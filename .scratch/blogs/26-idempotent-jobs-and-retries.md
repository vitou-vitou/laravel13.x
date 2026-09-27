# Idempotent Laravel Jobs: Prevent Duplicate Charges When Workers Retry

Your payment processing job throws a transient network timeout while talking to Stripe. Laravel's queue worker does exactly what it was configured to do: it catches the failure, increments the attempt count, and retries the job two minutes later. But the initial charge had actually succeeded on Stripe's servers right before the connection dropped, so the retry charges the customer a second time, triggering an angry email and an expensive chargeback dispute.

Queue workers operate on an **at-least-once** delivery guarantee, not an exactly-once guarantee. Network drops, worker process kills (`SIGKILL`), and database timeouts mean any background job can be executed more than once. If you design your jobs to be idempotent using unique idempotency keys, atomic state checks, and database transaction locks, your workers can retry safely without causing duplicate billing or corrupted records.

## The Flaw of Non-Idempotent Jobs

Consider a typical order payment job written without idempotency in mind:

```php
// Anti-pattern: retrying this job will charge the customer multiple times
class ChargeOrderPayment implements ShouldQueue
{
    public int $tries = 3;

    public function handle(): void
    {
        // If Stripe charges successfully but the network times out before this returns,
        // the worker retries the whole method from line 1!
        $charge = Stripe::charges()->create([
            'amount' => $this->order->total_cents,
            'currency' => 'usd',
            'customer' => $this->order->customer->stripe_id,
        ]);

        $this->order->update([
            'status' => 'paid',
            'stripe_charge_id' => $charge->id,
        ]);
    }
}
```

If the server loses power or drops the TCP connection after Stripe captures the money but before `$this->order->update()` executes, Laravel pushes the job back onto the queue. When worker process #2 picks it up, it fires another charge to Stripe.

## Pass Idempotency Keys to External APIs

Most payment gateways and financial APIs (including Stripe, Adyen, and PayPal) accept an `Idempotency-Key` HTTP header. If the gateway receives a request with a key it has seen within the last 24 hours, it returns the original successful charge response without processing a second transaction.

Generate a deterministic idempotency key derived from the entity's primary key:

app/Jobs/ProcessOrderPaymentJob.php:
```php
namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Stripe\StripeClient;

class ProcessOrderPaymentJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;
    public array $backoff = [10, 30, 60];

    public function __construct(public Order $order) {}

    public function handle(StripeClient $stripe): void
    {
        // 1. Guard against local re-runs if already completed
        if ($this->order->isPaid()) {
            return;
        }

        // 2. Deterministic idempotency key per order
        $idempotencyKey = "order-charge-{$this->order->id}";

        $charge = $stripe->paymentIntents->create([
            'amount' => $this->order->total_cents,
            'currency' => 'usd',
            'customer' => $this->order->customer->stripe_customer_id,
        ], [
            'idempotency_key' => $idempotencyKey,
        ]);

        $this->order->markAsPaid($charge->id);
    }
}
```

If attempt #1 times out after Stripe captures the charge, attempt #2 sends the exact same `order-charge-42` idempotency key. Stripe recognizes the key, suppresses the second charge, and returns the existing payment intent, allowing your job to complete cleanly.

## Database-Level Idempotency with Atomic State Transitions

For background jobs that update internal database balances or send inventory, enforce idempotency directly in SQL using conditional updates:

app/Jobs/FulfillInventoryJob.php:
```php
namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class FulfillInventoryJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        DB::transaction(function () {
            // Atomic conditional update: only decrement if status is still 'paid'
            $affectedRows = DB::table('orders')
                ->where('id', $this->order->id)
                ->where('status', 'paid')
                ->update([
                    'status' => 'fulfilled',
                    'fulfilled_at' => now(),
                ]);

            // If affectedRows is 0, another worker already processed this job
            if ($affectedRows === 0) {
                return;
            }

            foreach ($this->order->items as $item) {
                DB::table('inventory')
                    ->where('product_id', $item->product_id)
                    ->decrement('stock_count', $item->quantity);
            }
        });
    }
}
```

The condition `WHERE status = 'paid'` acts as an atomic compare-and-swap lock. If a retried job runs a second time, `$affectedRows` evaluates to zero and the job exits safely without double-decrementing stock.

## What Can Go Wrong

A dangerous mistake is using random UUIDs for idempotency keys generated inside the `handle()` method:

```php
// Dangerous: generating a random UUID on each attempt breaks idempotency!
public function handle(StripeClient $stripe): void
{
    $idempotencyKey = Str::uuid()->toString(); // Generates a new key on every retry!
    // ...
}
```

Because `Str::uuid()` generates a new random string on every execution, retry #2 sends a different idempotency key, completely defeating the gateway's duplicate protection.

Always base your idempotency keys on stable database entity attributes (such as `"order-{$order->id}-attempt-1"` or the payment record's UUID).

## Summary

Never assume a queued job will execute only once. Network timeouts, process signals, and queue retries guarantee that duplicate executions will happen in production.

Guard against duplicates by checking model status before executing, passing deterministic idempotency keys to external payment APIs, and using atomic SQL condition checks for internal inventory updates.

When every background job in your system is idempotent, automatic retries become a superpower that heals transient outages rather than a hazard that creates duplicate charges.

## Further Reading

- [Laravel Queues: Retrying Failed Jobs](https://laravel.com/docs/queues#dealing-with-failed-jobs)
- [Stripe API Reference: Idempotent Requests](https://stripe.com/docs/api/idempotent_requests)
- [Enterprise Integration Patterns: Idempotent Receiver](https://www.enterpriseintegrationpatterns.com/patterns/messaging/IdempotentReceiver.html)
- [Database Concurrency Control and Compare-and-Swap](https://en.wikipedia.org/wiki/Compare-and-swap)

How does your team protect background workers from duplicate executions? Share your patterns in the comments below.
