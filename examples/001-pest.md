# Pest Testing Framework — Trusted Patterns

> Pest: elegant PHP testing framework, 16M+ users. Built on PHPUnit with inspiration from RSpec (Ruby) and Jest (JavaScript).

---

## Setup

### Install

```powershell
composer require pestphp/pest pestphp/pest-plugin-laravel --dev
```

> `php artisan pest:install` may not exist. Create `tests/Pest.php` manually (see below).

### Pest.php (Test Setup)

```php
<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');
```

> RefreshDatabase: rolls back DB after each test. No production data at risk.

---

## Test Patterns

### 1. Feature Tests (HTTP/Database)

```php
<?php

it('returns 200 when accessing /admin as authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin');

    $response->assertOk();
});

it('redirects to login when accessing /admin as guest', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/login');
});
```

> Feature tests touch database. Use RefreshDatabase to isolate.

### 2. Unit Tests (Logic Only)

```php
<?php

it('calculates tax correctly', function () {
    $calculator = new TaxCalculator();

    $result = $calculator->calculate(100, 0.1);

    expect($result)->toBe(10.0);
});
```

> No database. No HTTP. Pure logic testing.

### 3. Architecture Tests (Enforce Standards)

```php
<?php

it('models use strict attribute casting', function () {
    expect(User::class)->toUseStrictCasting();
});

it('service classes never use queries', function () {
    expect(UserService::class)
        ->not->toHaveMethod('where')
        ->not->toHaveMethod('get');
});
```

> Enforce code quality at scale. Prevent regressions.

---

## Database Testing

### RefreshDatabase (Default — Transaction Rollback)

```php
<?php
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates user', function () {
    $user = User::factory()->create();

    expect(User::count())->toBe(1);
}); // Database rolled back after test
```

**Speed:** Fast. Transactions only.  
**Safety:** ✓ Production-safe.

### DatabaseMigrations (Full Reset)

```php
<?php
use Illuminate\Foundation\Testing\DatabaseMigrations;

uses(DatabaseMigrations::class);

it('migrates schema', function () {
    artisan('migrate');
    // Full database reset
});
```

**Speed:** Slow (~500ms per test).  
**When:** Only when RefreshDatabase isn't enough.

---

## Common Helpers

### act($user) — Auth as User

```php
$user = User::factory()->create();

$response = $this->actingAs($user)->post('/orders', [...]);

expect($response->status())->toBe(201);
```

### seed() — Run Seeders

```php
it('displays seeded data', function () {
    $this->seed([ProductSeeder::class]);

    $response = $this->get('/products');

    expect($response)->toSee('iPhone');
});
```

### artisan() — Run Commands

```php
it('queues job', function () {
    $this->artisan('queue:work --stop-when-empty');

    Queue::assertPushed(SendEmail::class);
});
```

---

## Assertions

### HTTP Responses

```php
$response->assertOk();                    // 200
$response->assertStatus(403);
$response->assertRedirect('/login');
$response->assertJson(['id' => 1]);
$response->assertSee('Welcome');
$response->assertDontSee('Error');
```

### Database

```php
expect(User::count())->toBe(1);
assertDatabaseHas('users', ['email' => 'test@example.com']);
assertDatabaseMissing('users', ['email' => 'old@example.com']);
```

### Collections/Objects

```php
expect($users)->toHaveCount(3);
expect($user)->toHaveProperty('email');
expect($value)->toBeTruthy();
expect($arr)->toContain('value');
```

---

## Performance Tips

### Parallel Testing (2x—10x faster)

```powershell
php artisan test --parallel
```

> Runs tests across CPUs. Sharded by execution time (not count).

### Watch Mode (TDD)

```powershell
php artisan test --watch
```

> Re-runs tests when files change. Always on during development.

### Coverage

```powershell
php artisan test --coverage
```

> Shows which code isn't tested. Set minimum threshold in phpunit.xml.

---

## File Structure

```
tests/
├── Pest.php                    # Global setup + traits
├── Feature/
│   ├── AdminAccessTest.php
│   ├── OrderCreationTest.php
│   └── ...
├── Unit/
│   ├── TaxCalculatorTest.php
│   └── ...
└── Architecture/
    └── StandardsTest.php
```

---

## Gotchas

1. **Tests run in isolation** — State from one test doesn't leak to next (RefreshDatabase)
2. **No `$this` in unit tests** — Unit tests don't extend TestCase; use plain closures
3. **Database is rolled back** — Don't expect data to persist across tests
4. **Async/Queue jobs** — Use `Queue::fake()` or `Bus::fake()` to mock

---

## Sources

- [Pest Documentation](https://pestphp.com/)
- [Pest Writing Tests](https://pestphp.com/docs/writing-tests)
- [Laravel Database Testing](https://laravel.com/docs/10.x/database-testing)
- [How RefreshDatabase Works](https://dev.to/daniel_werner/under-the-hood-how-refreshdatabase-works-in-laravel-tests-2728)
