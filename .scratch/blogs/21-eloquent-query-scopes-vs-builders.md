# Custom Eloquent Query Builders: Replace Messy Scopes with Dedicated Classes

You open an `Order` model in a three-year-old Laravel project and find 1,200 lines of code. Half of the file consists of twenty-five different local query scopes: `scopeCompleted()`, `scopeForCustomer()`, `scopeDueForInvoice()`, and `scopeFlaggedForReview()`. Autocomplete in your IDE is flooded with magic methods, method chaining is brittle, and testing query logic in isolation requires instantiating an entire database model.

Local query scopes were designed for quick filters, not complex domain querying. As an application grows, stuffing dozens of query modifiers into a single model violates the Single Responsibility Principle and creates unreadable models. By overriding Eloquent's `newEloquentBuilder()` method and extracting queries into dedicated Query Builder classes, you get full IDE autocompletion, type safety, and clean separation between data definitions and query logic.

## The Problem with Model Scope Bloat

When you write local scopes directly on an Eloquent model, you rely on magic `scope*` prefixing:

```php
// Anti-pattern: model bloated with dozens of magic query scopes
class Order extends Model
{
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())->whereNull('paid_at');
    }

    // ... 20 more scopes in the same file
}
```

This pattern suffers from three major flaws:
1. **Zero IDE Type Completion:** When you type `Order::query()->`, your IDE does not know that `completed()` or `overdue()` exist unless you maintain manual PHPDoc annotations above the model class.
2. **Scattered Responsibility:** The `Order` model now manages database relations, casts, events, mutators, and dozens of business query variations.
3. **No Method Grouping:** You cannot organize related queries into separate traits or logical builder namespaces.

## Create a Dedicated Eloquent Query Builder

Instead of cluttering the model, create a dedicated class extending `Illuminate\Database\Eloquent\Builder`:

app/Builders/OrderBuilder.php:
```php
namespace App\Builders;

use App\Enums\OrderStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class OrderBuilder extends Builder
{
    public function completed(): self
    {
        return $this->where('status', OrderStatus::Completed);
    }

    public function overdue(): self
    {
        return $this->where('due_date', '<', now())
            ->whereNull('paid_at');
    }

    public function forCustomer(int $customerId): self
    {
        return $this->where('customer_id', $customerId);
    }

    public function placedBetween(CarbonInterface $start, CarbonInterface $end): self
    {
        return $this->whereBetween('created_at', [$start, $end]);
    }
}
```

Notice the clean method signatures:
- No magic `scope` prefix is required.
- You have complete type-hinting on method arguments (`CarbonInterface`, `int`, etc.).
- Every method returns `self`, enabling strict return types and fluent chaining.

## Wire the Custom Builder to Your Model

To tell Laravel to use your custom builder when querying the model, override `newEloquentBuilder()`:

app/Models/Order.php:
```php
namespace App\Models;

use App\Builders\OrderBuilder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static OrderBuilder query()
 * @mixin OrderBuilder
 */
class Order extends Model
{
    public function newEloquentBuilder($query): OrderBuilder
    {
        return new OrderBuilder($query);
    }
}
```

Adding `@method static OrderBuilder query()` tells PhpStorm and VS Code that calling `Order::query()` returns an instance of `OrderBuilder`.

## Query with Full Autocomplete and Type Safety

Now you can write fluent, highly readable queries across your controllers and actions:

app/Http/Controllers/DashboardController.php:
```php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        // Full IDE autocompletion for custom methods
        $overdueOrders = Order::query()
            ->forCustomer($request->user()->customer_id)
            ->overdue()
            ->latest('due_date')
            ->paginate(15);

        return view('dashboard.orders', compact('overdueOrders'));
    }
}
```

Your IDE immediately recognizes `forCustomer()` and `overdue()`. If you rename a method in your builder, refactoring tools update every reference across the codebase safely without relying on text search.

## What Can Go Wrong

A frequent trap occurs when chaining relationships. If you call `Order::query()->overdue()`, it uses your custom `OrderBuilder`. But if you query through a relation:

```php
// May return standard Eloquent Builder instead of OrderBuilder
$customer->orders()->overdue();
```

To ensure relation queries use your custom builder, verify that your model overrides `newEloquentBuilder()` properly and avoids creating custom relation classes that discard the builder instance.

Also, avoid executing queries inside the builder methods. A query builder method should only modify the SQL state (`$this->where(...)`) and return `$this`. Never call `->get()` or `->first()` inside a builder method.

## Summary

Stop letting your Eloquent models grow into thousand-line god objects. Extract query logic into dedicated Query Builder classes by overriding `newEloquentBuilder()`.

You clean up your model definitions, gain first-class IDE autocompletion without docblock maintenance, and give your team a structured place to craft expressive, reusable database queries.

## Further Reading

- [Laravel Eloquent: Custom Query Builders](https://laravel.com/docs/eloquent#custom-query-builders)
- [Single Responsibility Principle in Domain Modeling](https://en.wikipedia.org/wiki/Single-responsibility_principle)
- [PhpStorm Laravel Plugin Documentation](https://plugins.jetbrains.com/plugin/7532-laravel)
- [Laravel PHPStan Documentation](https://github.com/larastan/larastan)

Have you migrated model scopes into custom Eloquent builders? Share your team's architecture conventions in the comments below.
