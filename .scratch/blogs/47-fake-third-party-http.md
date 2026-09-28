# Test Payment and Shipping Integrations: Reliable HTTP Fakes in Laravel

Your CI test suite runs on every GitHub pull request. Suddenly, builds start failing intermittently. A developer checks the error logs and discovers that a test called Stripe's live API using a test key, but Stripe had a brief DNS hiccup. Even worse, another test sent a real test order to FedEx, and someone's test credit card was actually charged $45 because an integration test executed live HTTP calls against a staging endpoint.

Automated test suites must never make outbound HTTP calls to third-party APIs. Live API calls introduce external network latency, cause flaky CI test runs, and risk real financial charges. Laravel's built-in `Http::fake()` utility provides a fast, deterministic mocking engine for outbound HTTP client calls. If you mock external API endpoints, assert sent payloads, and simulate error codes, you can test your payment and shipping pipelines thoroughly without making a single network call.

## The Danger of Live Third-Party Calls in Tests

When tests communicate with external APIs over the internet:
- **Flaky CI Runs:** A brief network timeout or upstream maintenance window on Stripe, Twilio, or SendGrid breaks your CI pipeline, blocking deployments.
- **Slow Test Suites:** Each external network round-trip adds 200 to 1,500 milliseconds. Fifty external tests add over a minute of pure waiting time to every commit.
- **Accidental Side Effects:** Testing a refund flow might accidentally trigger real refund webhooks, send test SMS messages to real phone numbers, or pollute third-party sandbox accounts with thousands of test records.

## Mock Outbound Requests with Http::fake()

Laravel's HTTP client provides `Http::fake()` to intercept outbound network requests and return immediate, in-memory responses.

Here is how to test a service that creates a shipping label via an external shipping API:

tests/Feature/ShippingLabelTest.php:
```php
use App\Models\Order;
use App\Services\ShippingService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('it requests a shipping label and saves tracking details on the order', function () {
    // 1. Intercept outbound HTTP requests to the shipping provider
    Http::fake([
        'api.shippingservice.com/v1/shipments' => Http::response([
            'tracking_number' => 'TRACK-987654321',
            'label_url' => 'https://cdn.shippingservice.com/labels/1042.pdf',
            'cost' => 14.50,
            'status' => 'created',
        ], 201),
    ]);

    $order = Order::factory()->create(['status' => 'processing']);

    // 2. Execute application code
    $shippingService = app(ShippingService::class);
    $shippingService->createShipment($order);

    // 3. Assert database state was updated
    expect($order->fresh())
        ->tracking_number->toBe('TRACK-987654321')
        ->status->toBe('shipped');

    // 4. Assert that the exact required JSON payload was sent to the provider
    Http::assertSent(function (Request $request) use ($order) {
        return $request->url() === 'https://api.shippingservice.com/v1/shipments'
            && $request->method() === 'POST'
            && $request['recipient_name'] === $order->shipping_name
            && $request['package_weight_grams'] === $order->total_weight_grams;
    });
});
```

The test runs in under ten milliseconds. Zero bytes are transmitted across the internet, the database updates cleanly, and `Http::assertSent()` verifies that your service formatted the JSON payload exactly as the carrier requires.

## Simulate Third-Party Outages and Error Codes

One of the greatest advantages of `Http::fake()` is the ability to test how your application handles third-party failures without waiting for a real outage:

tests/Feature/PaymentFailureTest.php:
```php
test('it records a payment failure when the payment gateway returns a 402 card declined', function () {
    Http::fake([
        'api.paymentgateway.com/v1/charges' => Http::response([
            'error' => [
                'code' => 'card_declined',
                'message' => 'Your card has insufficient funds.',
            ],
        ], 402),
    ]);

    $customer = Customer::factory()->create();

    $response = $this->actingAs($customer)
        ->post(route('checkout.charge'), [
            'payment_method_id' => 'pm_fake_insufficient_funds',
            'amount_cents' => 10000,
        ]);

    $response->assertRedirect(route('checkout.payment-error'));
    $response->assertSessionHas('error', 'Your card has insufficient funds.');

    $this->assertDatabaseHas('payment_attempts', [
        'customer_id' => $customer->id,
        'status' => 'failed',
        'failure_reason' => 'card_declined',
    ]);
});
```

You can test gateway timeouts, rate limits (HTTP 429), and internal server errors (HTTP 500) deterministically in automated tests.

## Prevent Accidental Live Calls with Http::preventStrayRequests()

To guarantee that no rogue developer code makes accidental live HTTP calls during tests, enable `preventStrayRequests()` globally:

tests/TestCase.php:
```php
namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Enforce: Throw an exception if ANY HTTP request is made without a matching fake!
        Http::preventStrayRequests();
    }
}
```

If any test or application service attempts to make an un-mocked outbound HTTP request, Laravel immediately fails the test with `RuntimeException: Attempted request to [https://api.example.com] without a matching fake.`

## What Can Go Wrong

A frequent trap with API mocking is mock drift: testing against fake JSON payloads that no longer match the third-party provider's live response structure.

If Stripe changes a response field from `charge_id` to `id`, your faked tests will stay green while production crashes.

To prevent mock drift:
- Store realistic JSON response fixtures in `tests/Fixtures/` copied directly from official API docs or recorded sandbox runs.
- Run a scheduled, isolated "contract smoke test" once a week against live sandbox environments to verify that your fixtures still match upstream schema contracts.

## Summary

Never let automated test suites communicate with real external APIs over the internet.

Use Laravel's `Http::fake()` to mock responses in memory with zero network latency. Assert outbound payloads with `Http::assertSent()`, test upstream error codes and timeouts deterministically, and enforce `Http::preventStrayRequests()` to block accidental live network calls.

Your CI test runs will execute in seconds and remain completely reliable.

## Further Reading

- [Laravel HTTP Client: Testing Documentation](https://laravel.com/docs/http-client#testing)
- [Testing External APIs with HTTP Fakes (Laravel News)](https://laravel-news.com/testing-http-client-in-laravel)
- [Martin Fowler: Mocks Aren't Stubs](https://martinfowler.com/articles/mocksArentStubs.html)
- [Contract Testing vs Mocking for Third-Party APIs](https://pact.io/)

How do you maintain and update your API response fixtures when third-party providers release breaking API versions? Share your workflow in the comments below.
