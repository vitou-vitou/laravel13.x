# Advanced Eloquent Subqueries: Compute Balances and Totals Inside SQL Without N+1

You are building a customer index table that displays each customer's name alongside their total lifetime spend and the date of their latest order. You implement it using standard Eloquent relationships, and immediately hit two extremes: either your page executes fifty-one database queries (the classic N+1 problem), or you eager load all orders with `with('orders')`, which pulls 100,000 historical order models into PHP memory just to calculate two small numbers.

Fetching full relationship models when you only need an aggregate is a major source of memory bloat in Laravel applications. Eloquent provides advanced subquery select methods like `withSum()`, `withMax()`, and `addSelect()`. If you push calculated aggregates into correlated SQL subqueries, you can compute totals, balances, and latest statuses in a single database query using negligible memory.

## The Memory Trap of Eager Loading Aggregates

When developers want to avoid N+1 query warnings, they often eager-load entire collections:

```php
// Anti-pattern: loads 50,000 Order models into memory just to calculate sums
$customers = Customer::with('orders')->paginate(50);

foreach ($customers as $customer) {
    // Calculates in PHP memory after hydrating thousands of objects
    $totalSpent = $customer->orders->sum('total_amount');
    $lastOrder = $customer->orders->sortByDesc('created_at')->first();
}
```

If your 50 customers have an average of 1,000 orders each, PHP must instantiate 50,000 Eloquent model instances, allocate memory for all their attributes, and hydrate timestamps. Your server memory climbs to over 100 megabytes for a simple list page.

## Native Aggregate Subqueries: withSum and withCount

Laravel includes built-in query builder methods for common aggregates: `withCount()`, `withSum()`, `withAvg()`, `withMin()`, and `withMax()`.

These methods execute a single correlated subquery within your base SQL statement:

app/Http/Controllers/CustomerController.php:
```php
namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        // Executes a single query that calculates totals directly in SQL
        $customers = Customer::query()
            ->withCount('orders')
            ->withSum('orders', 'total_amount')
            ->withMax('orders', 'created_at')
            ->latest()
            ->paginate(25);

        return view('customers.index', compact('customers'));
    }
}
```

Laravel appends the calculated attributes directly to each customer model:
- `$customer->orders_count`
- `$customer->orders_sum_total_amount`
- `$customer->orders_max_created_at`

Zero child order models are hydrated into memory. The database handles the math in parallel using its optimized execution engine.

## Advanced Correlated Subqueries with addSelect

What if you need a non-numeric aggregate, such as the status string of a customer's latest order? Neither `withCount()` nor `withMax()` handles strings from the latest related record.

Use `addSelect()` with a subquery closure:

app/Models/Customer.php:
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    public function scopeWithLatestOrderStatus(Builder $query): Builder
    {
        return $query->addSelect([
            'latest_order_status' => Order::select('status')
                ->whereColumn('customer_id', 'customers.id')
                ->latest()
                ->take(1),
        ]);
    }
}
```

The generated SQL performs a correlated subquery in the `SELECT` clause:

```sql
SELECT `customers`.*,
    (SELECT `status` FROM `orders`
     WHERE `orders`.`customer_id` = `customers`.`id`
     ORDER BY `created_at` DESC
     LIMIT 1) AS `latest_order_status`
FROM `customers`;
```

In your Blade view or API resource, `$customer->latest_order_status` is readily available as a standard string property.

## Order by Subqueries Without Table Duplication

Joining tables with `leftJoin()` to sort by related totals often introduces bugs because rows can be duplicated when multiple related records match, breaking your pagination counts.

Sorting by a subquery avoids table duplication entirely:

```php
// Sort customers by their total order spend cleanly
$customers = Customer::query()
    ->withSum('orders', 'total_amount')
    ->orderByDesc('orders_sum_total_amount')
    ->paginate(25);
```

The database sorts by the alias produced by the subquery, keeping pagination counts completely accurate.

## What Can Go Wrong

Correlated subqueries execute once for each row returned by the outer query. If the child table lacks an index on the foreign key and order column, this creates an N×M performance disaster.

For the `latest_order_status` subquery to execute efficiently:

```php
Schema::table('orders', function (Blueprint $table) {
    // Crucial: composite index for correlated subquery seeks
    $table->index(['customer_id', 'created_at']);
});
```

Without this composite index, MySQL or PostgreSQL must scan the entire `orders` table for every single customer on the page.

## Summary

Never load thousands of child models into PHP memory just to compute counts, sums, or latest status values.

Leverage Laravel's built-in `withCount()`, `withSum()`, and `withMax()` methods for scalar calculations, and use `addSelect()` subqueries for complex relational lookups. Keep your calculations inside the database engine where they belong, and your pages will remain blazing fast with minimal memory consumption.

## Further Reading

- [Laravel Eloquent: Querying Relationship Existence & Aggregates](https://laravel.com/docs/eloquent-relationships#querying-relationship-aggregates)
- [Laravel Advanced Subqueries (Laravel News Tutorial)](https://laravel-news.com/eloquent-subquery-enhancements)
- [Correlated Subqueries in Relational Databases](https://en.wikipedia.org/wiki/Correlated_subquery)
- [MySQL 8.0 Correlated Subquery Optimization](https://dev.mysql.com/doc/refman/8.0/en/correlated-subqueries.html)

How much memory did you save by converting heavy relationship eager-loading to subquery aggregates? Share your stats in the comments below.
