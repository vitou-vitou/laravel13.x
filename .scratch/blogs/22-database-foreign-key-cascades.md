# Foreign Key Constraints in Laravel: Cascade Delete vs Soft Deletes vs Restrict

An administrator clicks "Delete Company" on a test account in your internal tool. Ten seconds later, panic erupts across your engineering team. That single delete wiped out 10,000 paid invoices, three years of tax compliance records, and five hundred audit logs because the initial database migration added `$table->cascadeOnDelete()` to every single relation.

Database foreign keys are the ultimate enforcement layer for relational data integrity. But choosing the wrong delete behavior creates catastrophic data loss or clutters your logs with raw SQL foreign key violation errors. If you understand the distinct trade-offs between `cascadeOnDelete()`, `restrictOnDelete()`, `nullOnDelete()`, and Laravel's `SoftDeletes` trait, you can protect business records while keeping administrative cleanup effortless.

## The Four Foreign Key Delete Strategies

When you define a foreign key relationship in a Laravel migration, you specify what happens when the parent record is deleted:

database/migrations/2024_04_01_000001_create_foreign_key_examples_table.php:
```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // 1. Restrict: default database behavior
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();

            // 2. Cascade: automatically deletes child rows
            // $table->foreignId('cart_id')->constrained()->cascadeOnDelete();

            // 3. Null on delete: disassociates child rows
            $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }
};
```

Here is when to use each strategy:

### 1. Cascade Delete (`cascadeOnDelete()`)
When the parent row is deleted, the database engine automatically deletes all matching child rows in the same transaction.

**When to use it:** Only for strictly dependent, non-audited child records that have no independent value without the parent.
- Cart items belonging to a shopping cart (`cart_items`)
- Temporary session tokens or one-time verification codes
- Tag attachments in pivot tables (`taggables`)

**When NOT to use it:** Invoices, financial transactions, audit logs, or customer files.

### 2. Restrict Delete (`restrictOnDelete()`)
The database actively blocks the deletion of the parent record if any child record still references it, throwing a Foreign Key Constraint Violation.

**When to use it:** For critical financial and compliance records. A customer cannot be deleted if they have paid invoices. A department cannot be deleted if active employees belong to it.

Here is how to handle a restricted delete gracefully in a Laravel controller:

app/Http/Controllers/CustomerController.php:
```php
namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;

class CustomerController extends Controller
{
    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->invoices()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot delete customer with existing invoices. Archive the account instead.',
            ]);
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('status', 'Customer removed successfully.');
    }
}
```

Checking `$customer->invoices()->exists()` before attempting the delete allows you to present a helpful message to the user rather than an unhandled 500 database error.

### 3. Null on Delete (`nullOnDelete()`)
When the parent is removed, the database sets the foreign key column to `NULL` on all child records.

**When to use it:** Optional associations where the child record should survive even if the referenced entity is removed.
- An issue ticket where `assigned_to_user_id` should become `NULL` when an employee leaves the company.
- A product category where removing the category simply leaves products "Uncategorized".

## The Big Catch: Foreign Keys and Laravel Soft Deletes

The most common misconception in Laravel is assuming that database `cascadeOnDelete()` works with Eloquent `SoftDeletes`.

When a model uses `SoftDeletes`, calling `$customer->delete()` does not issue an SQL `DELETE` statement. It executes an SQL `UPDATE`:

```sql
UPDATE `customers` SET `deleted_at` = '2026-09-27 12:00:00' WHERE `id` = 42;
```

Because the row is never physically deleted from the table, **database-level foreign key cascades never trigger**. All child records remain completely active and visible unless you handle them explicitly in code or use an established package like `dyrynda/laravel-cascade-soft-deletes`.

## What Can Go Wrong

A major operational problem with soft deletes is handling unique database constraints.

Consider this standard migration:

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('email')->unique();
    $table->softDeletes();
});
```

If User A with `email = 'alex@example.com'` is soft-deleted, their record still exists in the database with `deleted_at` set. If Alex tries to register again with that same email address, MySQL throws a duplicate key error because standard unique indexes do not ignore soft-deleted rows.

In PostgreSQL, you can solve this with a partial index:

```sql
CREATE UNIQUE INDEX users_email_unique ON users (email) WHERE deleted_at IS NULL;
```

In MySQL, you must use composite keys or append the timestamp to the email address upon deletion.

## Summary

Never apply `cascadeOnDelete()` as a default habit in your migrations. Restrict deletes on audited business entities with `restrictOnDelete()`, use `nullOnDelete()` for optional relationships, and reserve `cascadeOnDelete()` strictly for ephemeral child data.

Remember that database cascades only fire on real SQL deletes, not on Laravel soft deletes. Treating your schema constraints as intentional security boundaries protects your data from catastrophic accidents.

## Further Reading

- [Laravel Foreign Key Constraints](https://laravel.com/docs/migrations#foreign-key-constraints)
- [Laravel Eloquent Soft Deleting](https://laravel.com/docs/eloquent#soft-deleting)
- [MySQL 8.0 Foreign Key Constraints](https://dev.mysql.com/doc/refman/8.0/en/create-table-foreign-keys.html)
- [PostgreSQL Referential Integrity](https://www.postgresql.org/docs/current/ddl-constraints.html#DDL-CONSTRAINTS-FK)

How do you handle unique email constraints on soft-deleted users in your applications? Join the discussion in the comments below.
