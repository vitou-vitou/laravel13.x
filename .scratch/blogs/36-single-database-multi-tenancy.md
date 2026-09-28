# Single-Database Multi-Tenancy in Laravel: Global Scopes That Never Leak Rows

A developer runs `Customer::all()` inside an export command or background job. Ten seconds later, Company A downloads a spreadsheet containing invoices, contact names, and addresses belonging to Company B. The multi-tenant isolation layer depended on developers remembering to add `->where('team_id', $teamId)` to every query across eighty controllers.

Single-database multi-tenancy is the most cost-effective and operationally simple way to run a SaaS application on Laravel. But if tenancy depends on human memory, a data leak is an inevitability. If you bind a tenant context to the service container, apply a dedicated Eloquent Global Scope to every tenant model, and enforce automatic foreign key assignment on create, you eliminate data cross-contamination permanently.

## The Flaw of Manual Tenant Filtering

When multi-tenancy is implemented informally, controllers look like this:

```php
// Anti-pattern: relying on developers to remember tenant checks on every query
public function index(Request $request)
{
    // If someone writes Order::latest()->paginate() instead, tenant rows leak!
    $orders = Order::where('team_id', $request->user()->current_team_id)
        ->latest()
        ->paginate(20);

    return view('orders.index', compact('orders'));
}
```

This pattern fails under pressure:
- A newly hired engineer creates an API endpoint and forgets `where('team_id', ...)`.
- A queued job receives a model ID and fetches across tenant boundaries.
- An admin search bar queries without tenant constraints and returns competitor data.

## Step 1: Manage Tenant Context in the Container

Create a singleton service that holds the currently authenticated tenant for the lifecycle of the request:

app/Services/TenantContext.php:
```php
namespace App\Services;

use App\Models\Team;

class TenantContext
{
    protected ?Team $currentTenant = null;

    public function set(Team $tenant): void
    {
        $this->currentTenant = $tenant;
    }

    public function get(): ?Team
    {
        return $this->currentTenant;
    }

    public function id(): ?int
    {
        return $this->currentTenant?->id;
    }

    public function check(): bool
    {
        return $this->currentTenant !== null;
    }
}
```

Register this class as a singleton in `AppServiceProvider`:

app/Providers/AppServiceProvider.php:
```php
public function register(): void
{
    $this->app->singleton(TenantContext::class);
}
```

## Step 2: Set Context via Middleware

Resolve the current tenant at the start of the HTTP request and set it on the context:

app/Http/Middleware/IdentifyTenant.php:
```php
namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function __construct(protected TenantContext $tenantContext) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->currentTeam) {
            $this->tenantContext->set($user->currentTeam);
        }

        return $next($request);
    }
}
```

Apply this middleware to your `web` and `auth:sanctum` route groups.

## Step 3: Build the TenantScope

Create an Eloquent Global Scope that intercepts every `SELECT`, `UPDATE`, and `DELETE` query:

app/Scopes/TenantScope.php:
```php
namespace App\Scopes;

use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(TenantContext::class);

        if ($context->check()) {
            $builder->where($model->qualifyColumn('team_id'), $context->id());
        }
    }
}
```

Using `$model->qualifyColumn('team_id')` generates `orders.team_id = 42` rather than a bare `team_id`, preventing ambiguous column SQL errors when queries join across related tables.

## Step 4: The BelongsToTenant Trait

Package the scope and automatic foreign key injection into a reusable model trait:

app/Traits/BelongsToTenant.php:
```php
namespace App\Traits;

use App\Models\Team;
use App\Scopes\TenantScope;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // Intercept all read, update, and delete queries
        static::addGlobalScope(new TenantScope());

        // Automatically assign tenant ID upon creation
        static::creating(function ($model) {
            $context = app(TenantContext::class);

            if ($context->check() && empty($model->team_id)) {
                $model->team_id = $context->id();
            }
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
```

Attach this trait to any tenant-owned model:

app/Models/Order.php:
```php
namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use BelongsToTenant;

    protected $guarded = [];
}
```

Now, when a developer writes `Order::all()`, `Order::latest()->paginate()`, or `Order::find(10)`, Laravel automatically appends `WHERE orders.team_id = 42`. When a developer calls `Order::create($data)`, Laravel automatically injects `team_id` without requiring it in form inputs.

## What Can Go Wrong

The biggest trap with global scopes is running console commands, database seeders, or background queue jobs where no HTTP user session exists.

If a worker picks up a queued job without initializing the `TenantContext`, `$context->check()` returns false and the query executes across all tenants.

For background jobs that process tenant data, set the tenant context explicitly at the beginning of the `handle()` method:

```php
public function handle(TenantContext $tenantContext): void
{
    $tenantContext->set($this->order->team);

    // Queries now safely scoped to this specific tenant
    $this->order->syncWithAccounting();
}
```

Alternatively, use `withoutGlobalScope(TenantScope::class)` only in super-admin panels or root reporting scripts where cross-tenant aggregation is explicitly required.

## Summary

Never leave multi-tenant data isolation to human discipline.

Use an immutable `TenantContext` singleton set by middleware, bind a `TenantScope` to all tenant-owned models via a shared trait, and automatically inject the tenant foreign key on model creation.

Data leaks become architecturally impossible, and your engineers can write standard, expressive Eloquent queries with complete confidence.

## Further Reading

- [Laravel Eloquent: Global Scopes](https://laravel.com/docs/eloquent#global-scopes)
- [Single-Database vs Multi-Database Multi-Tenancy Architecture](https://aws.amazon.com/blogs/database/multi-tenant-data-isolation-with-postgresql/)
- [Laravel Service Container: Singletons](https://laravel.com/docs/container#binding-singletons)
- [OWASP Top 10: Broken Access Control](https://owasp.org/Top10/A01_2021-Broken_Access_Control/)

How do you enforce multi-tenant isolation in your background queue workers? Share your approach in the comments below.
