# Database Transactions in Laravel: How to Prevent Race Conditions and Deadlocks

Two customers click "Place Order" on the last available item at the exact same millisecond. Both requests read an available inventory count of one, both pass validation, both decrement the row, and your inventory balance drops to zero while two paying customers expect the item. You try wrapping the code in `DB::transaction()`, but under traffic spikes you start receiving MySQL error 1213: "Deadlock found when trying to get lock; try restarting transaction."

Transactions alone do not prevent race conditions. A transaction ensures that all queries succeed or none do; it does not automatically lock rows against concurrent reads. If you pair Laravel database transactions with explicit row-level locking, keep external network calls outside transaction boundaries, and lock records in a deterministic order, you eliminate double-spending bugs and prevent database deadlocks.

## The Illusion of Safety: Standard Transactions

Wrapping database operations in `DB::transaction()` guarantees atomicity. If an exception occurs, Laravel automatically rolls back all changes made during the closure.

However, standard transactions run under read-committed or repeatable-read isolation levels:

```php
// Anti-pattern: standard transaction does NOT prevent concurrent read-modify-write races
DB::transaction(function () use ($account, $amount) {
    if ($account->balance >= $amount) { // Both concurrent requests read balance = $100
        $account->decrement('balance', $amount); // Both write, resulting in -$100!
    }
});
```

Because neither request locked the row during the read, both requests see the original balance of $100. Both evaluate the `if` statement to true, and both deduct $100, leaving the account at -$100.

## Use Pessimistic Locking with lockForUpdate

To prevent concurrent race conditions, instruct the database to lock the row as soon as it reads it using `lockForUpdate()`:

app/Services/TransferService.php:
```php
namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class TransferService
{
    public function transfer(int $fromAccountId, int $toAccountId, int $amountCents): void
    {
        // Retry the transaction up to 3 times if a transient deadlock occurs
        DB::transaction(function () use ($fromAccountId, $toAccountId, $amountCents) {
            // Lock the sender row exclusively
            $fromAccount = Account::where('id', $fromAccountId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($fromAccount->balance_cents < $amountCents) {
                throw new InsufficientBalanceException('Not enough funds available.');
            }

            // Lock the recipient row
            $toAccount = Account::where('id', $toAccountId)
                ->lockForUpdate()
                ->firstOrFail();

            $fromAccount->decrement('balance_cents', $amountCents);
            $toAccount->increment('balance_cents', $amountCents);
        }, attempts: 3);
    }
}
```

When `lockForUpdate()` runs, any other transaction attempting to read that row with `lockForUpdate()` or modify it must wait until the current transaction completes. The second request waits, reads the updated balance, and cleanly fails validation.

Notice the `attempts: 3` parameter. If MySQL detects an unavoidable deadlock with another query, Laravel catches the error and transparently retries the entire closure up to three times.

## Prevent Deadlocks with Deterministic Lock Ordering

Deadlocks happen when two transactions acquire locks in opposite order:
- Transaction A locks Account #1 and requests a lock on Account #2.
- Transaction B locks Account #2 and requests a lock on Account #1.

Both transactions freeze, waiting for the other to release its lock. The database engine aborts one of them with a deadlock error.

To avoid cyclic deadlocks, always acquire locks in ascending order by primary key:

app/Services/TransferService.php:
```php
public function transfer(int $fromAccountId, int $toAccountId, int $amountCents): void
{
    DB::transaction(function () use ($fromAccountId, $toAccountId, $amountCents) {
        // Sort IDs deterministically to guarantee consistent lock ordering
        $accountIds = [$fromAccountId, $toAccountId];
        sort($accountIds);

        // Locks accounts in predictable order (e.g., #1 then #2, never #2 then #1)
        $accounts = Account::whereIn('id', $accountIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $fromAccount = $accounts->get($fromAccountId);
        $toAccount = $accounts->get($toAccountId);

        if ($fromAccount->balance_cents < $amountCents) {
            throw new InsufficientBalanceException('Insufficient funds.');
        }

        $fromAccount->decrement('balance_cents', $amountCents);
        $toAccount->increment('balance_cents', $amountCents);
    }, attempts: 3);
}
```

Because every worker in your application acquires locks in strict numerical order, cyclic deadlocks become mathematically impossible.

## Keep External HTTP Calls Outside the Transaction

The most common cause of database lock exhaustion is placing slow network operations inside `DB::transaction()`:

```php
// Anti-pattern: holding database locks while waiting on external APIs
DB::transaction(function () use ($order) {
    $order->lockForUpdate()->first();
    
    // External API call takes 2,000ms! All other queries waiting on this row stall
    $charge = Stripe::charges()->create([...]);

    $order->update(['status' => 'paid', 'charge_id' => $charge->id]);
});
```

If Stripe takes two seconds to respond or your email server stalls, that row lock stays active for two seconds. All other web requests trying to access that record pile up, quickly exhausting your database connection pool.

Charge the card first, or verify funds, then open the database transaction to record the result. Database transactions should hold locks for single-digit milliseconds.

## What Can Go Wrong

Be careful with non-repeatable reads in `DB::transaction()`. If you catch an exception inside the transaction closure and do not re-throw it:

```php
// Dangerous: swallowing an exception inside a transaction
DB::transaction(function () {
    try {
        Order::create([...]);
    } catch (\Exception $e) {
        // Swallowing the error prevents Laravel from rolling back!
    }
});
```

If you swallow an exception inside the closure, Laravel assumes the operation completed successfully and commits whatever partial writes occurred. Always let exceptions bubble up so Laravel can execute a clean rollback, or trigger `DB::rollBack()` manually.

## Summary

Protecting concurrent data in Laravel requires more than wrapping code in `DB::transaction()`. Use `lockForUpdate()` when reading values that govern business rules, sort IDs to ensure deterministic lock acquisition order, pass an `attempts` retry count to `DB::transaction()`, and never run slow external API calls while holding a database lock.

With these four rules in place, your financial transfers, inventory checkouts, and high-frequency writes remain rock-solid regardless of traffic volume.

## Further Reading

- [Laravel Database Transactions Documentation](https://laravel.com/docs/database#database-transactions)
- [Laravel Pessimistic Locking](https://laravel.com/docs/queries#pessimistic-locking)
- [MySQL Deadlocks and How to Minimize Them](https://dev.mysql.com/doc/refman/8.0/en/innodb-deadlocks-handling.html)
- [PostgreSQL Explicit Locking Guide](https://www.postgresql.org/docs/current/explicit-locking.html)

Have you encountered a stubborn deadlock in your Laravel production database? Tell us how you traced and resolved it in the comments below.
