# Zero-Downtime Database Migrations: Add, Rename, and Drop Columns Safely

You run `php artisan migrate` during a routine Friday deployment to rename `name` to `full_name` on your users table. For the four minutes while your deployment pipeline spins up new Docker containers, your existing application servers continue executing the previous release. Every incoming user registration crashes with a 500 Internal Server Error because the old codebase is still querying for a column that no longer exists in MySQL.

In modern continuous delivery, code and database schemas never update at the exact same millisecond. If you rename or drop database columns in a single migration, you guarantee downtime for active users. By using the Expand and Contract pattern across separate deployments, you can evolve production database schemas continuously without dropping a single HTTP request.

## The Problem with Single-Step Migrations

Consider what happens when you deploy this standard migration:

```php
// Dangerous: causes downtime during rolling deployments
public function up(): void
{
    Schema::table('customers', function (Blueprint $table) {
        $table->renameColumn('phone', 'phone_number');
    });
}
```

During a rolling deployment, servers running Version A of your code expect the column `phone`. Servers running Version B expect `phone_number`. As soon as the migration executes, Version A servers immediately fail on every customer read or write.

Renaming or dropping a column must always be treated as a multi-stage migration across multiple deployments.

## The Expand and Contract Pattern

The Expand and Contract pattern breaks a breaking schema change into three safe, backward-compatible deployments:

1. **Expand (Deployment 1):** Add the new column as nullable. Deploy code that writes to both columns but still reads from the old column.
2. **Transition (Deployment 2):** Backfill historical data in the background. Switch application code to read and write exclusively from the new column.
3. **Contract (Deployment 3):** Drop the old column once all servers are running the updated code.

Let us walk through each step with real code.

### Step 1: Expand with Dual Writing

In your first migration, create the new column as nullable:

database/migrations/2024_02_01_000001_add_phone_number_to_customers_table.php:
```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('phone_number');
        });
    }
};
```

Update your Eloquent model to write to both columns automatically during this transition phase:

app/Models/Customer.php:
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected static function booted(): void
    {
        // Dual-write: keep both columns synchronized for backward compatibility
        static::saving(function (Customer $customer) {
            if ($customer->isDirty('phone')) {
                $customer->phone_number = $customer->phone;
            } elseif ($customer->isDirty('phone_number')) {
                $customer->phone = $customer->phone_number;
            }
        });
    }
}
```

Deploy this migration and code. Old requests succeed, new records write to both columns, and zero errors occur.

### Step 2: Backfill Historical Data

Now, run a background console command or queued job to copy historical values from the old column to the new column:

app/Console/Commands/BackfillCustomerPhoneNumbers.php:
```php
namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;

class BackfillCustomerPhoneNumbers extends Command
{
    protected $signature = 'customers:backfill-phones';

    public function handle(): void
    {
        Customer::whereNull('phone_number')
            ->whereNotNull('phone')
            ->chunkById(500, function ($customers) {
                foreach ($customers as $customer) {
                    $customer->updateQuietly([
                        'phone_number' => $customer->phone,
                    ]);
                }
            });

        $this->info('Customer phone numbers backfilled successfully.');
    }
}
```

Using `chunkById()` processes the records in small batches, avoiding memory exhaustion and preventing table locks.

### Step 3: Transition and Contract

Once all historical rows are populated, release Deployment 2. Update your application queries, forms, and views to read from `phone_number` and remove the dual-write event listener from the model.

Finally, in Deployment 3 (several days later, once you are certain no running process touches the old column), drop the legacy column:

database/migrations/2024_02_10_000001_drop_phone_from_customers_table.php:
```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('phone')->nullable();
        });
    }
};
```

## Adding Columns with Defaults Without Table Locks

In older versions of MySQL, adding a column with a default value (`$table->boolean('is_verified')->default(false)`) rewrote the entire table, holding an exclusive metadata lock for minutes on large tables.

In MySQL 8.0 and PostgreSQL 11+, adding a nullable column or a column with a constant default uses an **instant algorithm**:

```php
// Instant in MySQL 8.0+ / PostgreSQL 11+
Schema::table('orders', function (Blueprint $table) {
    $table->boolean('is_flagged')->default(false);
});
```

Verify that your production database engine supports instant operations before running migrations on tables with tens of millions of rows.

## What Can Go Wrong

When dropping columns, Laravel's Eloquent models cache table column listings if you use model caching packages or certain query building features. If a server receives a request while a column drop is running, an in-flight `SELECT *` query will throw an exception if the column vanishes midway through execution.

Always deploy the code removal **before** running the migration that drops the column. A database with an unused extra column causes no harm; a codebase querying a dropped column causes an outage.

## Summary

Zero-downtime database changes require discipline, not complex tools. Never rename or drop a column in a single migration. Use the Expand and Contract pattern: add the new column first, dual-write to keep data synchronized, backfill historical rows with `chunkById()`, switch your reads, and contract the legacy column in a subsequent release.

Taking three deployments to change a column may feel slower than a single `renameColumn()`, but it guarantees that your users experience zero disruption.

## Further Reading

- [Laravel Migrations Documentation](https://laravel.com/docs/migrations)
- [Evolutionary Database Design by Martin Fowler](https://martinfowler.com/articles/evodb.html)
- [MySQL 8.0 Instant DDL Operations](https://dev.mysql.com/doc/refman/8.0/en/innodb-online-ddl-operations.html)
- [PostgreSQL Fast Column Addition](https://www.postgresql.org/docs/current/ddl-alter.html)

How does your team coordinate database migrations during continuous deployments? Share your workflow in the comments below.
