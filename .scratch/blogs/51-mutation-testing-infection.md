# Mutation Testing in Laravel: How Infection Reveals Tests That Never Truly Asserted

Your project dashboard proudly displays 98% code coverage. Every controller, service, and action file shows green lines in the coverage report. But when a junior developer changes a pricing discount check from `$order->total >= 100` to `$order->total > 100`, or accidentally removes an `if ($user->isSuspended())` guard, the test suite still reports 100% green.

Code coverage only measures which lines of code were *executed* during a test run; it does not measure whether your tests actually *asserted* that the logic was correct. A test can execute 500 lines of code without a single `assert()` statement and report 100% coverage. Mutation testing solves this by intentionally introducing bugs ("mutants") into your codebase to see if your tests fail. If you run Infection PHP against your Laravel application, you discover the false-positive tests that give you a dangerous illusion of security.

## The Illusion of Line Coverage

Consider this standard service method:

app/Services/DiscountCalculator.php:
```php
class DiscountCalculator
{
    public function applyDiscount(Order $order): int
    {
        if ($order->subtotal_cents >= 10000 && ! $order->customer->is_vip) {
            return (int) ($order->subtotal_cents * 0.90); // 10% discount
        }

        return $order->subtotal_cents;
    }
}
```

Now, consider a weak test that achieves 100% line coverage:

tests/Feature/DiscountTest.php:
```php
test('apply discount executes without errors', function () {
    $order = Order::factory()->create(['subtotal_cents' => 15000]);
    $calculator = new DiscountCalculator();

    $result = $calculator->applyDiscount($order);

    // Weak assertion: only asserts that an integer was returned!
    expect($result)->toBeInt();
});
```

Because every line in `applyDiscount()` was executed, your code coverage tool reports 100% coverage for the file.

However, if someone changes `* 0.90` to `* 0.50` or removes `&& ! $order->customer->is_vip`, the test still passes in green. Your code coverage metric lied to you.

## How Mutation Testing Works with Infection

Mutation testing tests the quality of your *tests*:

1. **Mutation:** Infection parses your source code abstract syntax tree (AST) and makes small semantic changes:
   - Changes `>=` to `>`.
   - Replaces `true` with `false`.
   - Changes `&&` to `||`.
   - Removes method calls like `$order->save()`.
2. **Execution:** Infection runs your test suite against each mutated version of your code.
3. **Evaluation:**
   - **Mutant Killed (Good):** If a test fails when the mutation is introduced, your test caught the bug.
   - **Mutant Escaped (Bad):** If your tests still pass despite the bug, your assertions are weak or missing.

## Install and Configure Infection for Laravel

Install Infection as a development dependency via Composer:

```bash
composer require --dev infection/infection
```

Create a configuration file in `infection.json`:

infection.json:
```json
{
  "$schema": "vendor/infection/infection/resources/schema.json",
  "source": {
    "directories": [
      "app/Services",
      "app/Actions"
    ]
  },
  "logs": {
    "text": "infection.log",
    "summary": "infection-summary.log"
  },
  "mutators": {
    "@default": true,
    "MethodCallRemoval": {
      "ignore": [
        "Illuminate\\Support\\Facades\\Log::*"
      ]
    }
  },
  "testFramework": "pest"
}
```

Notice that we focus Infection on `app/Services` and `app/Actions`. Running mutation tests against thin boilerplate controllers or generated migrations is unnecessary; focus mutation testing where your business calculations, authorizations, and money moves live.

## Run Mutation Testing and Analyze the Results

Run Infection from your terminal:

```bash
./vendor/bin/infection --threads=4 --min-msi=80
```

Infection outputs a **Mutation Score Indicator (MSI)**:

```
Generating mutants...
Processing mutants...

85 mutations were generated:
  72 mutants were killed
   2 mutants were not covered by tests
  11 mutants escaped

Metrics:
  Mutation Score Indicator (MSI): 84.7%
  Mutation Code Coverage: 97.6%
```

Look at the escaped mutants in `infection.log`:

```diff
1) app/Services/DiscountCalculator.php:7 [M] GreaterThanOrEqualTo
@@ -7,3 +7,3 @@
-        if ($order->subtotal_cents >= 10000 && ! $order->customer->is_vip) {
+        if ($order->subtotal_cents > 10000 && ! $order->customer->is_vip) {
             return (int) ($order->subtotal_cents * 0.90);
```

Infection reveals that you never tested an order with a subtotal of exactly $100.00 (`10000` cents). Changing `>=` to `>` escaped without notice because your test suite only tested an order with $150.00.

## Fix the Test to Kill the Mutant

Now that Infection has pointed out the exact boundary gap, add the missing edge-case test:

tests/Feature/DiscountTest.php:
```php
test('customers with exactly 100 dollars receive 10 percent discount', function () {
    $order = Order::factory()->create(['subtotal_cents' => 10000]);
    $calculator = new DiscountCalculator();

    $result = $calculator->applyDiscount($order);

    // Precise assertion kills the GreaterThanOrEqualTo mutant
    expect($result)->toBe(9000);
});

test('vip customers do not receive the standard discount', function () {
    $vipCustomer = Customer::factory()->create(['is_vip' => true]);
    $order = Order::factory()->create([
        'customer_id' => $vipCustomer->id,
        'subtotal_cents' => 15000,
    ]);

    $calculator = new DiscountCalculator();
    $result = $calculator->applyDiscount($order);

    // Kills the BooleanNot and LogicalAnd mutants
    expect($result)->toBe(15000);
});
```

Re-run Infection. The MSI climbs to 100%. The mutants are killed, and your tests now actively defend your business logic against boundary regressions.

## What Can Go Wrong

The primary challenge with mutation testing is execution time. Generating and testing 500 mutants can take ten minutes if your test suite runs database migrations.

To keep mutation testing fast:
1. Target only high-risk business logic folders (`app/Domain`, `app/Services/Pricing`).
2. Run Infection with `--git-diff-lines` on pull requests so it only mutates code modified in that specific branch:

```bash
# Fast PR check: only mutates lines changed in this pull request
./vendor/bin/infection --git-diff-filter=AM --threads=4
```

This allows mutation testing to run in under twenty seconds on pull requests.

## Summary

Code coverage is a vanity metric; Mutation Score Indicator (MSI) is an integrity metric.

Use Infection PHP to test your test suite. Let Infection generate subtle bugs in your business services, observe which mutants escape, and write precise boundary assertions that kill them.

You transform a passive, superficial test suite into a battle-hardened safety net that genuinely protects your application from regressions.

## Further Reading

- [Infection PHP Official Documentation](https://infection.github.io/)
- [Martin Fowler: Mutation Testing](https://martinfowler.com/bliki/MutationTesting.html)
- [Pest PHP Integration with Infection](https://infection.github.io/guide/using-with-pest.html)
- [Why 100% Code Coverage is Misleading](https://kentcdodds.com/blog/how-to-know-what-to-test)

Have you run mutation testing on your core domain services? Share your initial MSI score and findings in the comments below.
