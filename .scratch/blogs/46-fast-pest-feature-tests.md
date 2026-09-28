# Write Fast Feature Tests with Pest: Stop Testing Framework Code

A developer submits a pull request with forty unit tests. You review the test suite and discover that half of the tests assert that `$user->orders()` returns an instance of `HasMany`, or that an `$order->status` column is a string. When someone adds a legitimate bug in the checkout discount calculator, the entire test suite still passes in green because nobody wrote tests asserting real HTTP status codes, session flashes, or database mutations.

Testing framework plumbing—like Eloquent relationship declarations or model casts—is a waste of engineering time because Laravel's internal test suite already tests those contracts. The highest-leverage tests in a Laravel application are **HTTP Feature Tests** written with Pest. If you write end-to-end feature tests that simulate real user requests, assert database side effects, and avoid mocking internal framework logic, your test suite catches real regression bugs while executing in milliseconds.

## The Flaw of Testing Framework Internals

Consider tests commonly found in low-value test suites:

```php
// Anti-pattern: testing that Laravel behaves like Laravel
test('user has many orders relationship', function () {
    $user = new User();
    expect($user->orders())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('order model casts total to integer', function () {
    $order = new Order(['total_cents' => '100']);
    expect($order->total_cents)->toBeInt();
});
```

These tests provide zero confidence:
- If a route breaks, these tests stay green.
- If a validation rule rejects valid phone numbers, these tests stay green.
- If a discount coupon code calculation deducts tax twice, these tests stay green.

Taylor Otwell and the Laravel core team already test Eloquent relationships across thousands of framework commits. Your job is to test your **application's business behavior**.

## Write Concise, High-Leverage Feature Tests with Pest

Pest provides a clean, functional syntax that eliminates boilerplate PHPUnit class declarations.

Here is an idiomatic feature test for an order checkout route:

tests/Feature/CheckoutTest.php:
```php
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('authenticated customer can purchase a product with sufficient stock', function () {
    // 1. Arrange: Create customer and an in-stock product
    $customer = Customer::factory()->create();
    $product = Product::factory()->create([
        'price_cents' => 2500,
        'stock_count' => 10,
    ]);

    // 2. Act: Send an authenticated HTTP POST request to the checkout route
    $response = $this->actingAs($customer)
        ->post(route('checkout.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

    // 3. Assert: Check redirect, database mutation, and inventory reduction
    $response->assertRedirect(route('checkout.success'));
    $response->assertSessionHas('status', 'Order placed successfully.');

    $this->assertDatabaseHas('orders', [
        'customer_id' => $customer->id,
        'total_cents' => 5000,
        'status' => 'paid',
    ]);

    expect($product->fresh()->stock_count)->toBe(8);
});
```

Look at what this single test verifies in forty milliseconds:
- The route exists and accepts POST requests.
- The `auth` middleware permits authenticated customers.
- Validation accepts valid payloads.
- The controller coordinates the transaction.
- An order row is inserted into the database with the correct calculated total.
- Product inventory is decremented by two.
- A session flash status message is set and the user receives a clean redirect.

## Test Validation Failures Explicitly

Do not test validation rules in isolation with unit reflection. Test that your HTTP endpoints return the expected validation errors:

tests/Feature/CheckoutValidationTest.php:
```php
test('checkout rejects requests when quantity exceeds available stock', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['stock_count' => 2]);

    $response = $this->actingAs($customer)
        ->from(route('checkout.create'))
        ->post(route('checkout.store'), [
            'product_id' => $product->id,
            'quantity' => 5, // Exceeds available inventory
        ]);

    $response->assertRedirect(route('checkout.create'));
    $response->assertSessionHasErrors(['quantity']);
    $this->assertDatabaseEmpty('orders');
});
```

This proves that invalid input cannot corrupt database records and returns the user back to the form with targeted field errors.

## Test Guest Authorization Boundaries

Ensure unauthenticated guests and unauthorized roles cannot access private endpoints:

tests/Feature/AdminOrderTest.php:
```php
test('unauthenticated guests are redirected to the login screen', function () {
    $this->get(route('admin.orders.index'))
        ->assertRedirect(route('login'));
});

test('regular users cannot view administrative order dashboards', function () {
    $regularUser = Customer::factory()->create();

    $this->actingAs($regularUser)
        ->get(route('admin.orders.index'))
        ->assertForbidden(); // HTTP 403 Forbidden
});
```

Two lines of Pest prove that your middleware and authorization policies are actively protecting administrative resources.

## What Can Go Wrong

A frequent performance killer in Laravel test suites is using `RefreshDatabase` when `LazilyRefreshDatabase` should be used.

`RefreshDatabase` migrates and resets the database before *every single test*, even tests that never touch the database.

Use `LazilyRefreshDatabase` in `tests/Pest.php`:

tests/Pest.php:
```php
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(
    Tests\TestCase::class,
    LazilyRefreshDatabase::class,
)->in('Feature');
```

`LazilyRefreshDatabase` monitors database connections and only executes migrations and transactions if a test actually queries the database, cutting total test suite execution times in half.

## Summary

Stop writing fragile unit tests that verify framework relationships and casts.

Invest your engineering effort in high-value Pest feature tests. Simulate real HTTP requests, assert state transitions, verify validation boundaries, and test authorization gates.

You will catch real regressions before they hit production while keeping your test suite concise, readable, and lightning fast.

## Further Reading

- [Pest PHP Official Documentation](https://pestphp.com/)
- [Laravel Documentation: HTTP Tests](https://laravel.com/docs/http-tests)
- [Martin Fowler: The Practical Test Pyramid](https://martinfowler.com/articles/practical-test-pyramid.html)
- [Testing Laravel: Best Practices by Brent Roose](https://stitcher.io/blog/testing-laravel)

How long does your application's test suite take to run in CI? Share your execution benchmarks in the comments below.
