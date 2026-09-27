# Composite Indexes in Laravel: How to Read EXPLAIN Before Adding Hardware

Your customer dashboard is crawling. Someone suggests upgrading the database instance from 4 cores to 16 cores, which doubles your cloud bill every month. You run the slow query directly in MySQL or PostgreSQL, and discover that a simple filter query scans two million rows on every page load because your migration only created single-column indexes.

Hardware does not fix a query that lacks an index. In relational databases, column order in composite indexes matters just as much as having an index in the first place. If you learn how to read an `EXPLAIN` query plan and structure your Laravel migrations around real query patterns, you can make multi-million row tables respond in single-digit milliseconds.

## How to Run EXPLAIN in Laravel

Before guessing which index to add, inspect what the database optimizer actually does. Laravel's query builder provides a built-in `explain()` method that prints the execution plan.

Run this in Tinker or in a local debug route:

```php
// Inspect the execution plan directly in Laravel
$query = Order::query()
    ->where('team_id', 42)
    ->where('status', 'completed')
    ->where('created_at', '>=', now()->subDays(30))
    ->orderByDesc('created_at');

dd($query->explain());
```

In MySQL, look at three key columns in the result:

1. `type`: If this says `ALL`, the database scans every single row in the table (a full table scan). If it says `ref` or `range`, it utilizes an index.
2. `possible_keys`: Indexes the database considered using.
3. `rows`: The estimated number of rows examined. If this is 500,000 when your query only returns 15 records, your index is missing or ineffective.

## The Leftmost Prefix Rule

A common mistake in Laravel migrations is creating separate single-column indexes for every column mentioned in a `WHERE` clause:

database/migrations/2024_01_01_000001_create_orders_table.php:
```php
// Anti-pattern: multiple separate indexes rarely help complex filters
Schema::table('orders', function (Blueprint $table) {
    $table->index('team_id');
    $table->index('status');
    $table->index('created_at');
});
```

When your query filters on `team_id`, `status`, AND `created_at`, the database engine picks only one of those indexes (or attempts an expensive index merge). It scans all rows matching that single condition and filters the rest in memory.

A composite index combines multiple columns into a single search tree:

database/migrations/2024_01_01_000002_add_composite_index_to_orders_table.php:
```php
namespace App\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['team_id', 'status', 'created_at'], 'orders_team_status_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_team_status_created_idx');
        });
    }
};
```

Column order in a composite index must follow the **Leftmost Prefix Rule**:
1. Put columns with exact equality checks (`=`) first (e.g., `team_id`, `status`).
2. Put columns used in range checks (`>`, `<`, `BETWEEN`) or ordering (`ORDER BY`) last (e.g., `created_at`).

Once a query hits a range condition, the database engine cannot use subsequent columns in that composite index for index-level filtering.

## Test the Index Under Load

Run `explain()` again after migrating the composite index. The `type` column shifts from `ALL` to `ref` or `range`, and the `rows` examined drops from two million to only the dozen rows matching that specific team's completed orders.

app/Models/Order.php:
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function scopeForTeamDashboard(Builder $query, int $teamId, int $days = 30): Builder
    {
        // Hits the composite index: team_id (=), status (=), created_at (>=)
        return $query
            ->where('team_id', $teamId)
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays($days))
            ->latest('created_at');
    }
}
```

The database traverses the B-Tree directly to the matching team, jumps to the `completed` branch, and reads the timestamps in order without scanning unrelated rows.

## What Can Go Wrong

Indexes are not free. Every index you add must be updated whenever a row is inserted, updated, or deleted. If you create ten composite indexes on a high-throughput table that receives 500 writes per second, you slow down your insert performance and inflate your disk storage.

Never add indexes blindly based on intuition:
- Review your database's slow query log regularly.
- Check that your indexes actually get used; unused indexes waste memory buffers.
- Remember that applying functions to indexed columns in SQL (such as `WHERE DATE(created_at) = '2026-09-27'`) invalidates index lookups. Use `whereDate()` or explicit timestamp ranges instead.

## Summary

Before upgrading database server hardware to cure slow queries, inspect your execution plan with `explain()`. Group your multi-column `WHERE` clauses into composite indexes, order columns with equality matches first and range filters last, and drop redundant single-column indexes.

A well-placed composite index costs nothing in hosting fees and turns painful multi-second dashboard timeouts into instantaneous page loads.

## Further Reading

- [Laravel Query Builder: Explaining Queries](https://laravel.com/docs/queries#explaining-queries)
- [MySQL 8.0 Reference: Multiple-Column Indexes](https://dev.mysql.com/doc/refman/8.0/en/multiple-column-indexes.html)
- [PostgreSQL Documentation: Multicolumn Indexes](https://www.postgresql.org/docs/current/indexes-multicolumn.html)
- [Use The Index, Luke: A Guide to Database Performance](https://use-the-index-luke.com/)

What is the biggest query optimization win you've achieved with composite indexes? Share your query metrics in the comments below.
