# The Ten-Test Smoke Suite: Prove Login, Checkout, and Webhooks Work in CI

Your continuous integration pipeline runs a massive suite of 1,200 unit and integration tests. Every single test passes with flying colors. You trigger a deployment to production, and within ten minutes your customer support phone lines light up. The homepage returns a blank white screen because an asset manifest failed to compile, the login page crashes with a 500 error due to a missing environment variable, and the checkout webhook returns a 419 CSRF error.

A passing unit test suite does not prove that an application is deployable. Unit tests test isolated code logic; they do not prove that your routing, database connections, environment variables, authentication cookies, and compiled assets work together in an integrated environment. If you create a dedicated **Ten-Test Smoke Suite** that exercises the critical user paths from HTTP request to database side effect, you can verify deployment readiness in under five seconds.

## What Is a Smoke Suite?

A smoke suite is a deliberately small, ultra-fast test suite that verifies the core operational pillars of your business:

1. **Can a user load the public homepage and health check?**
2. **Can a user register a new account?**
3. **Can an existing user authenticate and receive a valid session?**
4. **Can a customer view a product and add it to their cart?**
5. **Can a customer complete a purchase checkout?**
6. **Can the application receive and process a signed payment webhook?**
7. **Can a user upload a file to storage and retrieve it?**
8. **Can an authenticated user trigger a background queued job?**
9. **Can an administrative user access the back-office panel?**
10. **Does an invalid request return a structured, graceful error instead of a crash?**

If all ten tests pass, your application's fundamental mechanics—routing, database, sessions, queues, storage, and middleware—are fully functional.

## The Ten Critical Tests in Pest

Create a dedicated test file in `tests/Feature/SmokeTest.php`:

tests/Feature/SmokeTest.php:
```php
use App\Models\Customer;
use App\Models\Product;
use App\Jobs\ProcessOrderJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

// 1. Healthcheck & Public Landing
test('1. health check endpoint and homepage return HTTP 200', function () {
    $this->get('/up')->assertOk();
    $this->get('/')->assertOk();
});

// 2. Customer Registration
test('2. new users can register an account', function () {
    $response = $this->post(route('register'), [
        'name' => 'Alex Smoke',
        'email' => 'alex.smoke@example.com',
        'password' => 'SecurePassword123!',
        'password_confirmation' => 'SecurePassword123!',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'alex.smoke@example.com']);
});

// 3. User Authentication
test('3. existing users can authenticate and receive session', function () {
    $user = Customer::factory()->create(['password' => bcrypt('password123')]);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password123',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user);
});

// 4. Catalog Browsing & Cart
test('4. customers can browse catalog and add items to cart', function () {
    $product = Product::factory()->create(['is_active' => true]);

    $this->get(route('products.show', $product))->assertOk();

    $this->post(route('cart.items.store'), [
        'product_id' => $product->id,
        'quantity' => 1,
    ])->assertSessionHas('cart');
});

// 5. End-to-End Checkout
test('5. customers can complete checkout and create an order', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['price_cents' => 2000, 'stock_count' => 10]);

    $this->actingAs($customer)
        ->post(route('checkout.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect(route('checkout.success'));

    $this->assertDatabaseHas('orders', [
        'customer_id' => $customer->id,
        'status' => 'paid',
    ]);
});

// 6. Signed Inbound Webhook
test('6. application accepts and verifies incoming payment webhooks', function () {
    $payload = json_encode(['event' => 'payment.success', 'order_reference' => 'ORD-1001']);
    $secret = config('services.webhooks.secret');
    $signature = hash_hmac('sha256', $payload, $secret);

    $this->call('POST', '/api/webhooks/payments', [], [], [], [
        'HTTP_X_WEBHOOK_SIGNATURE' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $payload)->assertOk();
});

// 7. Secure File Storage
test('7. users can upload and retrieve private documents', function () {
    Storage::fake('private_documents');
    $user = Customer::factory()->create();
    $file = UploadedFile::fake()->create('tax_form.pdf', 500, 'application/pdf');

    $this->actingAs($user)
        ->post(route('documents.store'), ['document' => $file])
        ->assertRedirect();

    Storage::disk('private_documents')->assertExists("documents/{$file->hashName()}");
});

// 8. Asynchronous Queue Dispatching
test('8. order placement dispatches background processing job', function () {
    Bus::fake([ProcessOrderJob::class]);
    $user = Customer::factory()->create();

    $this->actingAs($user)->post(route('orders.dispatch-job'), ['order_id' => 1]);

    Bus::assertDispatched(ProcessOrderJob::class);
});

// 9. Administrative Role Protection
test('9. admin dashboard restricts access to authorized staff only', function () {
    $regularUser = Customer::factory()->create();
    $adminUser = Customer::factory()->create(['is_admin' => true]);

    $this->actingAs($regularUser)->get('/admin')->assertForbidden();
    $this->actingAs($adminUser)->get('/admin')->assertOk();
});

// 10. Graceful Error Handling
test('10. missing resources return clean 404 rather than 500 crash', function () {
    $this->get('/products/99999999')->assertNotFound();
});
```

These ten tests execute in less than three seconds.

## Run Smoke Tests as a Pre-Deploy Gate in CI

In your GitHub Actions or deployment workflow, run the smoke suite as the final gating check before deploying to production:

.github/workflows/deploy.yml:
```yaml
name: Production Deployment Gate

on:
  push:
    branches: [main]

jobs:
  smoke-test-gate:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, pdo, pdo_mysql, redis
      - run: composer install --prefer-dist --no-progress
      - run: cp .env.example .env && php artisan key:generate
      - name: Run Ten-Test Smoke Suite
        run: ./vendor/bin/pest tests/Feature/SmokeTest.php
```

If any foundational integration fails—whether an unmigrated database column, an un-mocked storage path, or an authentication break—the deployment halts instantly before touching production servers.

## What Can Go Wrong

A frequent problem with smoke tests is overcomplicating them with edge-case test matrices.

Do not test twenty different discount coupon combinations or twelve form validation errors in your smoke suite. Those edge cases belong in detailed feature test files.

Keep the smoke suite strictly focused on proving the **happy path** and basic security boundaries. A smoke test should test whether the house is on fire, not whether the curtains match the wallpaper.

## Summary

Never deploy to production based solely on passing unit tests.

Establish a Ten-Test Smoke Suite that tests the full lifecycle of your business: health check, registration, login, cart, checkout, webhooks, file uploads, background queues, administrative gates, and error handling.

Running this three-second test gate before every deployment guarantees that your core revenue and user flows remain rock-solid.

## Further Reading

- [Laravel Documentation: Testing Getting Started](https://laravel.com/docs/testing)
- [Smoke Testing vs Sanity Testing in Software Engineering](https://en.wikipedia.org/wiki/Smoke_testing_(software))
- [GitHub Actions Documentation: Workflow Syntax](https://docs.github.com/en/actions/using-workflows/workflow-syntax-for-github-actions)
- [Google SRE Book: Testing for Reliability](https://sre.google/sre-book/testing-reliability/)

What are the critical happy paths in your application that must never break? Tell us in the comments below.
