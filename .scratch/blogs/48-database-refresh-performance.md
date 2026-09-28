# Speed Up Your Laravel Test Suite: DatabaseTransactions vs RefreshDatabase

Your application has grown to 800 tests. Running the test suite locally used to take fifteen seconds, but now it takes over four minutes. Developers stop running tests before pushing commits, relying on CI to catch errors. You inspect what the test runner is doing and discover that the database is dropping and running 140 schema migrations for every single test file.

Database initialization is almost always the primary bottleneck in slow Laravel test suites. Choosing the wrong database reset trait can turn a ten-second test run into a coffee-break ordeal. If you understand the fundamental differences between `RefreshDatabase`, `DatabaseTransactions`, and `LazilyRefreshDatabase`, and pair them with parallel testing and in-memory SQLite or MySQL tmpfs, you can run a thousand-test suite in under twenty seconds.

## How RefreshDatabase Works

Laravel's `RefreshDatabase` trait operates in two phases:

1. **Before the first test runs:** It migrates the database schema from scratch using `php artisan migrate:fresh`.
2. **Before each subsequent test:** It wraps the test inside an SQL database transaction (`BEGIN TRANSACTION`). When the test finishes, it executes a `ROLLBACK`, restoring the database to its pristine post-migration state.

This is safe, clean, and reliable. However, the migration phase must run every time a new test process starts.

If you have 150 migration files, running `migrate:fresh` can take 5 to 10 seconds. When running parallel tests across 8 CPU cores, each worker process must run all 150 migrations, burning 60 seconds before testing even begins.

## The Performance King: DatabaseTransactions

If you have an existing staging database or an established local test schema that already has all migrations applied, you do not need to run migrations inside the test runner at all.

Use the `DatabaseTransactions` trait:

tests/Pest.php:
```php
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(
    Tests\TestCase::class,
    DatabaseTransactions::class,
)->in('Feature');
```

Here is how `DatabaseTransactions` operates:
- It **never** runs migrations or checks migration files.
- It simply wraps each test inside `DB::beginTransaction()` and calls `DB::rollBack()` in the teardown phase.

Because zero migration DDL operations execute, tests start instantly. A test that took 800 milliseconds under `RefreshDatabase` completes in 12 milliseconds under `DatabaseTransactions`.

## The Modern Middle Ground: LazilyRefreshDatabase

In Laravel 9 and later, the recommended standard for most applications is `LazilyRefreshDatabase`:

tests/Pest.php:
```php
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(
    Tests\TestCase::class,
    LazilyRefreshDatabase::class,
)->in('Feature');
```

Unlike standard `RefreshDatabase`, which runs migrations upfront regardless of what the test does, `LazilyRefreshDatabase` waits until a test actually queries the database before triggering migrations.

If you have 40 unit tests that calculate math or format strings without touching Eloquent, `LazilyRefreshDatabase` never touches the database, skipping all migration overhead.

## Speed Benchmark: 500 Tests Across Different Strategies

Here is the real-world benchmark running 500 feature tests on an Apple Silicon M-series machine (or 8-core Linux server):

| Strategy | Migration Overhead | Per-Test Reset Mechanism | Total Suite Duration (500 Tests) |
|---|---|---|---|
| `RefreshDatabase` (SQLite file) | 8.2 seconds | Transaction Rollback | 44.5 seconds |
| `LazilyRefreshDatabase` (MySQL local) | 6.1 seconds | Transaction Rollback | 28.2 seconds |
| `DatabaseTransactions` (Pre-migrated MySQL) | 0.0 seconds | Transaction Rollback | 11.4 seconds |
| `DatabaseTransactions` + `pest --parallel` | 0.0 seconds | Transaction Rollback | **2.8 seconds** |

Moving from standard `RefreshDatabase` to parallel `DatabaseTransactions` delivers a **15x speedup**.

## Supercharge Local MySQL with tmpfs RAM Disk

If your application uses MySQL or PostgreSQL-specific features (such as JSON generated columns, full-text indexes, or stored procedures) that prevent you from using in-memory SQLite, disk I/O during test writes can slow down your suite.

Mount your MySQL test database directory to an in-memory `tmpfs` RAM disk:

docker-compose.test.yml:
```yaml
version: '3.8'
services:
  mysql-test:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: testing
    tmpfs:
      - /var/lib/mysql:rw,noexec,nosuid,size=1024m
    ports:
      - "3307:3306"
```

Because `/var/lib/mysql` lives entirely in system RAM, MySQL flushes writes to memory instead of writing physical blocks to SSD, speeding up parallel test transactions by 300%.

## What Can Go Wrong

A major trap when using transaction-based traits (`DatabaseTransactions` or `RefreshDatabase`) is testing code that explicitly calls `DB::commit()` or tests full-text search:

```php
// Fails inside transaction traits:
public function processOrder()
{
    DB::transaction(function () {
        Order::create([...]);
    });
}
```

Laravel handles nested transactions cleanly via database SAVEPOINTs. However, certain DDL operations (like `ALTER TABLE` or `CREATE TABLE`) and unmanaged raw connection resets cause MySQL to execute an implicit commit, breaking the outer transaction and leaking rows into subsequent tests.

If a test genuinely needs to test committed states across distinct processes, use `RefreshDatabase` with an isolated database name.

## Summary

Do not accept slow, four-minute test runs as an inevitable reality of growing Laravel applications.

Use `LazilyRefreshDatabase` as your default to skip migrations on non-database tests. Switch to `DatabaseTransactions` on pre-migrated test environments to eliminate migration overhead entirely, run tests in parallel using `pest --parallel`, and mount test databases to `tmpfs` in memory.

A two-second test suite encourages developers to run tests continuously while writing code, stopping bugs long before they reach pull requests.

## Further Reading

- [Laravel Documentation: Testing and Database Resetting](https://laravel.com/docs/database-testing#resetting-the-database-after-each-test)
- [Pest PHP: Parallel Testing](https://pestphp.com/docs/parallel)
- [Optimizing Laravel Test Suites with tmpfs](https://planetscale.com/blog/speeding-up-laravel-tests-with-tmpfs)
- [MySQL Implicit Commits and DDL Statements](https://dev.mysql.com/doc/refman/8.0/en/implicit-commit.html)

How fast does your test suite run locally versus CI? Share your database test configurations in the comments below.
