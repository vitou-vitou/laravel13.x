# Test Asynchronous Architecture: Verify Queued Jobs, Events, and Notifications

Your application triggers a welcome email, updates an analytics pipeline, and dispatches a background job when an order is placed. When you test this workflow, your test runner attempts to connect to a real Redis server, writes real rows to a `jobs` table, or actually attempts to send transactional emails. The test suite slows down to a crawl, and tests fail whenever local Docker services are stopped.

Testing asynchronous architecture does not require running real background queue workers or third-party email transports. Laravel provides dedicated testing fakes for all asynchronous primitives: `Queue::fake()`, `Event::fake()`, and `Notification::fake()`. If you use these fakes in your feature tests, you can assert that the correct jobs and events were pushed with the exact expected data while keeping your test suite running entirely in memory in single-digit milliseconds.

## Why Real Asynchronous Execution Belongs Outside Feature Tests

When a test triggers an event that dispatches a queued job:
- **Unnecessary Overhead:** Connecting to Redis or inserting into database queue tables adds unnecessary I/O.
- **Cascading Side Effects:** If an event listener sends an email, your test might fail due to an unrelated mail configuration issue rather than an order placement bug.
- **Process Separation:** A test process cannot easily wait for a detached background worker process to finish without introducing fragile `sleep()` statements.

Instead, test in two distinct phases:
1. **Phase 1 (The Trigger):** Test that the web controller or action successfully dispatched the expected job or event.
2. **Phase 2 (The Handler):** Test the job's `handle()` method independently in its own isolated test.

## Verify Dispatched Queued Jobs with Queue::fake()

Use `Queue::fake()` to intercept job dispatches:

tests/Feature/OrderPlacementJobTest.php:
```php
use App\Jobs\ProcessOrderPaymentJob;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\Queue;

test('placing an order dispatches the payment processing job with correct payload', function () {
    // 1. Intercept job dispatches
    Queue::fake([
        ProcessOrderPaymentJob::class,
    ]);

    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['price_cents' => 4500]);

    // 2. Perform the user action
    $this->actingAs($customer)
        ->post(route('checkout.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])
        ->assertRedirect();

    // 3. Assert that the specific job was pushed to the queue
    Queue::assertPushed(function (ProcessOrderPaymentJob $job) use ($customer) {
        return $job->order->customer_id === $customer->id
            && $job->order->total_cents === 4500
            && $job->queue === 'high';
    });

    // 4. Assert that unrelated jobs were NOT pushed
    Queue::assertNotPushed(\App\Jobs\RefundOrderJob::class);
});
```

`Queue::fake()` captures all dispatched jobs in an in-memory array. The callback inside `Queue::assertPushed()` allows you to inspect the job's public properties, verifying that the order and queue name were configured properly.

## Verify Fired Domain Events with Event::fake()

When an action fires domain events to decouple side effects:

tests/Feature/CustomerRegistrationEventTest.php:
```php
use App\Events\CustomerRegisteredEvent;
use Illuminate\Support\Facades\Event;

test('new registration fires CustomerRegisteredEvent', function () {
    Event::fake([
        CustomerRegisteredEvent::class,
    ]);

    $this->post(route('register'), [
        'name' => 'Jordan Doe',
        'email' => 'jordan@example.com',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
    ]);

    Event::assertDispatched(function (CustomerRegisteredEvent $event) {
        return $event->customer->email === 'jordan@example.com';
    });
});
```

Passing `[CustomerRegisteredEvent::class]` to `Event::fake()` ensures that only this specific event is intercepted. Other internal Laravel framework events (like database transaction events) continue operating normally.

## Verify Sent Notifications with Notification::fake()

Test that transactional emails, SMS messages, or Slack alerts are sent to the correct recipients:

tests/Feature/OrderShippedNotificationTest.php:
```php
use App\Models\Order;
use App\Notifications\OrderShippedNotification;
use Illuminate\Support\Facades\Notification;

test('marking an order as shipped sends a notification to the customer', function () {
    Notification::fake();

    $order = Order::factory()->create(['status' => 'processing']);

    // Admin marks order as shipped
    $this->actingAs($order->team->admin)
        ->put(route('orders.ship', $order), [
            'tracking_number' => 'TRACK-12345',
        ]);

    // Assert that the notification was sent to this specific customer
    Notification::assertSentTo(
        [$order->customer],
        function (OrderShippedNotification $notification, array $channels) use ($order) {
            return $notification->order->id === $order->id
                && in_array('mail', $channels);
        }
    );

    // Assert that notifications were NOT sent to random users
    Notification::assertNotSentTo(
        $order->team->admin,
        OrderShippedNotification::class
    );
});
```

`Notification::fake()` intercepts all notification channels (Mail, Database, Broadcast, SMS) and records them in memory.

## Test the Job Handler in Isolation

Once you have verified that the job is dispatched, write an independent test that tests the job's `handle()` method directly:

tests/Unit/ProcessOrderPaymentJobTest.php:
```php
use App\Jobs\ProcessOrderPaymentJob;
use App\Models\Order;
use Stripe\StripeClient;

test('job charges order and marks it as paid', function () {
    $order = Order::factory()->create(['total_cents' => 3000, 'status' => 'pending']);

    // Mock the external Stripe dependency
    $stripeMock = Mockery::mock(StripeClient::class);
    $stripeMock->paymentIntents = Mockery::mock();
    $stripeMock->paymentIntents
        ->shouldReceive('create')
        ->once()
        ->andReturn((object) ['id' => 'pi_test_123']);

    // Instantiate and execute the job directly in memory
    $job = new ProcessOrderPaymentJob($order);
    $job->handle($stripeMock);

    expect($order->fresh())
        ->status->toBe('paid')
        ->stripe_charge_id->toBe('pi_test_123');
});
```

You test the job's business logic directly without running a background queue daemon.

## What Can Go Wrong

A frequent pitfall with `Event::fake()` is faking all events blindly without specifying the target event class:

```php
// Dangerous: faking ALL events disables Eloquent model events!
Event::fake();
```

When you call `Event::fake()` without arguments, Laravel intercepts *all* events, including Eloquent model lifecycle events (`creating`, `saved`, `deleted`). Any model logic, UUID generation, or audit trailing that relies on model events will silently stop executing during the test.

Always pass an explicit array of event classes to `Event::fake([OrderCreated::class])`.

## Summary

Do not let asynchronous background architecture slow down your test suite.

Use `Queue::fake()`, `Event::fake()`, and `Notification::fake()` to verify that your HTTP endpoints trigger the correct background jobs and notifications with the expected payloads. Test job `handle()` methods independently in dedicated unit tests.

Your tests execute in milliseconds, remain fully deterministic, and provide complete confidence in your asynchronous workflows.

## Further Reading

- [Laravel Documentation: Mocking Queues](https://laravel.com/docs/mocking#queue-fake)
- [Laravel Documentation: Mocking Events](https://laravel.com/docs/mocking#event-fake)
- [Laravel Documentation: Mocking Notifications](https://laravel.com/docs/mocking#notification-fake)
- [Testing Event-Driven Systems](https://martinfowler.com/articles/201701-event-driven.html)

How do you organize your test suite between event dispatch verification and listener execution? Share your testing setup in the comments below.
