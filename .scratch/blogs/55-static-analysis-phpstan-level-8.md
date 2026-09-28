# Zero Type Exceptions: How to Run PHPStan at Level 8 in Large Laravel Apps

Your application runs smoothly until a customer with a missing billing address triggers a checkout, and production immediately crashes with: `TypeError: Cannot assign null to property App\Models\Invoice::$billing_address of type string`. A developer assumed the address was guaranteed to exist, PHP's dynamic typing permitted the code to run in local testing, and the error escaped to production.

PHP is dynamically typed by default, meaning type mismatches, unhandled null values, and misspelled method calls are often discovered only when code executes in production. Static analysis tools like PHPStan (paired with Larastan) analyze your entire codebase without running it, proving type correctness mathematically before a single test executes. If you configure PHPStan at Level 8, establish a progressive baseline for legacy debt, and use strict native type declarations, you can eliminate entire categories of runtime fatal errors.

## The Flaw of Dynamic Runtime Guessing

Consider a standard controller action:

```php
// Fails silently in local testing, crashes in production when customer has no team
public function sendInvoice(Order $order)
{
    // If $order->customer is null, this throws a fatal ErrorException!
    $team = $order->customer->team;

    // If $team->billing_email is null, mail fails!
    Mail::to($team->billing_email)->send(new InvoiceMail($order));
}
```

In your local environment, you test with a factory user who always has a customer and a team. The code works.

In production, an unverified guest checkout or orphaned account triggers the route. PHP throws `Attempt to read property "team" on null`, crashing the request.

PHPStan catches this at build time, reporting: `Cannot access property $team on App\Models\Customer|null.`

## Step 1: Install Larastan

Because Laravel relies on dynamic features (like magic Eloquent properties, macroable traits, and dynamic scopes), standard PHPStan requires Laravel-specific knowledge.

Install Larastan, the official Laravel extension for PHPStan:

```bash
composer require --dev "larastan/larastan:^2.0"
```

Create a configuration file in `phpstan.neon`:

phpstan.neon:
```neon
includes:
    - vendor/larastan/larastan/extension.neon

parameters:
    paths:
        - app/
        - bootstrap/app.php
        - config/
        - routes/

    # Level 8: Enforces strict types and explicit null checking
    level: 8

    # Treat PHPDoc types with the same strictness as native types
    treatPhpDocTypesAsCertain: false
```

## Understanding PHPStan Levels: Why Level 8?

PHPStan levels range from 0 (very loose) to 9 (extreme strictness):

- **Level 0–4:** Basic syntax, undefined classes, unknown methods, and missing variables.
- **Level 5–6:** Parameter types, return types, and missing typehints.
- **Level 7:** Union types and partially missing properties.
- **Level 8:** **Strict nullability.** You cannot pass `Type|null` to a function expecting `Type` without explicitly checking `if ($value !== null)`.
- **Level 9:** Extremely strict mixed-type checking (often too restrictive for dynamic framework collections).

**Level 8 is the gold standard for production Laravel applications.** It eliminates `null` pointer exceptions without fighting the framework.

## Fix Common Level 8 Type Violations

Here are the three most common Level 8 violations and how to fix them idiomatically:

### 1. Handling Nullable Eloquent Relations

```php
// Level 8 Violation:
$email = $order->customer->email; // Error: Cannot access property $email on Customer|null

// Idiomatic Fix 1: Null-safe operator with fallback
$email = $order->customer?->email ?? throw new RuntimeException('Order has no customer.');

// Idiomatic Fix 2: Explicit early return guard
if (! $order->customer) {
    return;
}
$email = $order->customer->email; // PHPStan now knows $customer is non-null!
```

### 2. Typing Eloquent Dynamic Properties

Tell PHPStan about dynamic model properties using docblocks or the `barryvdh/laravel-ide-helper` package:

app/Models/Order.php:
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $reference
 * @property int $total_cents
 * @property Customer|null $customer
 */
class Order extends Model
{
    // ...
}
```

### 3. Strict Typed Return Types on Methods

Ensure every service method and action declares exact native parameter and return types:

app/Actions/CalculateTaxAction.php:
```php
namespace App\Actions;

use App\Models\Order;

class CalculateTaxAction
{
    public function execute(Order $order, float $taxRate): int
    {
        return (int) round($order->total_cents * $taxRate);
    }
}
```

## Taming Legacy Codebases with a Baseline

If you enable Level 8 on an existing three-year-old Laravel project, PHPStan might report 1,400 errors. You do not need to fix all 1,400 errors before benefiting from static analysis.

Generate a **PHPStan Baseline**:

```bash
./vendor/bin/phpstan analyse --generate-baseline
```

This creates `phpstan-baseline.neon`, which records all 1,400 existing errors and tells PHPStan to ignore them.

Now, add `phpstan-baseline.neon` to your configuration:

phpstan.neon:
```neon
includes:
    - vendor/larastan/larastan/extension.neon
    - phpstan-baseline.neon
```

Run PHPStan again:

```
[OK] No errors
```

From this moment forward:
1. **Existing technical debt is frozen.** You can refactor old files incrementally over time.
2. **Zero new type errors are permitted.** Any new code or modified line in pull requests must strictly pass Level 8.

## Add PHPStan to Your CI Pipeline

Enforce static analysis on every pull request:

```yaml
- name: Run Static Analysis (PHPStan Level 8)
  run: ./vendor/bin/phpstan analyse --memory-limit=2G --error-format=github
```

Using `--error-format=github` automatically annotates exact line numbers with error messages directly in GitHub pull request diffs.

## What Can Go Wrong

A frequent pitfall is using `treatPhpDocTypesAsCertain: true` with incorrect PHPDoc annotations.

If a docblock claims `@var User $user`, but the database actually returns `null`, PHPStan will believe the docblock and hide the potential crash from you.

Always write defensive code, verify nullable states in migrations, and prefer native PHP 8.3 union types (`?User`, `User|Guest`) over verbose docblock annotations whenever possible.

## Summary

Do not wait for your users to discover unhandled `null` pointers and type mismatch bugs in production.

Install Larastan and configure PHPStan at Level 8. Use a baseline file to freeze legacy errors, enforce strict typing and null guards on all new code, and run static analysis in CI alongside your tests.

You eliminate entire classes of runtime fatal crashes, write more self-documenting code, and give your team absolute confidence in every deployment.

## Further Reading

- [PHPStan Official Documentation](https://phpstan.org/)
- [Larastan: Laravel Static Analysis](https://github.com/larastan/larastan)
- [PHPStan Baseline Feature Guide](https://phpstan.org/user-guide/baseline)
- [PHP 8 Type System Overview](https://www.php.net/manual/en/language.types.declarations.php)

What PHPStan level does your Laravel codebase run at today? Share your static analysis journey in the comments below.
