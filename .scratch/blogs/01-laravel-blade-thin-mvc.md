# Laravel Blade MVC: Keep Controllers Thin Without Over-Engineering Layers

You open a pull request for a three-field product form, and find a Controller, a Repository interface, an Eloquent repository implementation, a Domain Action class, and an Assembler. The author wanted "clean architecture," but turned thirty lines of straightforward framework code into five files that all pass the same array forward.

Laravel's standard request lifecycle was built for Blade: HTTP arrives, a Form Request validates it, a controller coordinates the write, Eloquent saves the record, and Blade renders HTML. You do not need to invent an enterprise architecture layer to keep your code testable and readable.

## The Standard HTTP Flow Is Already Thin

Laravel gives you a clear four-step pipeline for standard web forms:

1. `routes/web.php` maps the URL to a controller action with CSRF and session middleware enabled.
2. A Form Request validates incoming parameters and authorizes the request before your controller runs.
3. The controller delegates the database write directly to Eloquent or to a single transaction closure.
4. The controller returns a redirect with a flash message or renders a Blade view with compact data.

When you follow this path, every developer on your team knows where to look. Validation lives in the Form Request. Database constraints live in migrations and model definitions. UI markup lives in Blade templates.

Here is what an idiomatic controller method looks like:

app/Http/Controllers/ProductController.php:
```php
namespace App\Http\Controllers;

use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()
            ->route('products.show', $product)
            ->with('status', 'Product updated successfully.');
    }
}
```

This method is five lines long, completely type-safe, and self-documenting. It uses Route Model Binding to resolve the product or throw an automatic 404, and it only touches validated input.

## Put Validation in Form Requests, Not Controllers

When controllers grow past twenty lines, validation logic is usually the culprit. Moving validation rules into a dedicated Form Request class keeps the controller focused on coordination.

Generate a Form Request using Artisan:

```bash
php artisan make:request UpdateProductRequest
```

Define both your authorization policy check and your validation rules in that request:

app/Http/Requests/UpdateProductRequest.php:
```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku,' . $this->route('product')->id],
            'is_active' => ['boolean'],
        ];
    }
}
```

The controller never executes if authorization fails or if validation errors exist. Laravel automatically redirects back with input and errors flashed to the session, which Blade displays without extra plumbing.

## The Anti-Pattern: The One-Model Repository

A repository makes sense when you swap data storage engines or manage complex multi-source aggregations. It does not make sense as a wrapper that merely calls `Product::find()` or `Product::create()`.

Consider this common layer of unnecessary indirection:

app/Repositories/ProductRepository.php:
```php
namespace App\Repositories;

use App\Models\Product;

class ProductRepository implements ProductRepositoryInterface
{
    public function findById(int $id): Product
    {
        return Product::findOrFail($id);
    }

    public function update(Product $product, array $data): bool
    {
        return $product->update($data);
    }
}
```

This class creates technical debt without benefit. It duplicates Eloquent's API, obscures query features like eager loading and query scopes, and requires an interface and a service provider binding for no practical reason. Eloquent is already an implementation of the Active Record pattern with a powerful query builder. Use it directly until domain complexity demands otherwise.

## When to Extract: Multi-Model Writes and Shared Logic

Staying thin does not mean stuffing eighty lines of payment processing and inventory adjustment into a controller. When a write touches multiple models, sends notifications, or runs inside a database transaction, extract one dedicated Action class.

Keep the Action class focused on a single operation:

app/Actions/PublishProductAction.php:
```php
namespace App\Actions;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class PublishProductAction
{
    public function execute(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $product->update(['status' => 'published', 'published_at' => now()]);
            $product->inventory()->create(['initial_stock' => 0]);
            $product->recordActivity('published');
        });
    }
}
```

Your controller injects `PublishProductAction`, calls `execute($product)`, and returns the redirect. You keep the controller thin, isolate the complex transaction, and retain the ability to run that same Action from a console command or queue worker.

## What Can Go Wrong

The biggest trap with standard Blade applications is letting views run database queries. If you access `$product->category->name` inside a Blade `@foreach` loop without eager loading, you trigger an N+1 query performance bug that slows down production.

Always eager load relationships in your controller queries:

app/Http/Controllers/ProductController.php:
```php
public function index(): View
{
    $products = Product::query()
        ->with('category')
        ->latest()
        ->paginate(20);

    return view('products.index', compact('products'));
}
```

Catch these queries early in local development using Laravel's strict mode:

app/Providers/AppServiceProvider.php:
```php
use Illuminate\Database\Eloquent\Model;

public function boot(): void
{
    Model::preventLazyLoading(! app()->isProduction());
}
```

Strict mode throws an exception in your local browser whenever a Blade view attempts to lazy-load an unloaded relationship.

## Summary

Write code that works with Laravel rather than against it. Validate in Form Requests, rely on Route Model Binding, write to Eloquent directly for standard CRUD, and extract single-action classes only when multi-table transactions or shared logic require them.

Start with the shortest working path. Your team will ship features faster, onboard new hires effortlessly, and debug issues in minutes instead of wading through five layers of empty abstractions.

## Further Reading

- [Laravel Routing and Controllers](https://laravel.com/docs/controllers)
- [Laravel Form Request Validation](https://laravel.com/docs/validation#form-request-validation)
- [Laravel Eloquent ORM](https://laravel.com/docs/eloquent)
- [Laravel Eloquent Strict Mode](https://laravel.com/docs/eloquent#configuring-eloquent-strictness)

Have an opinion on controller architecture or spotted a bloated repository pattern recently? Leave a comment below or share your team's convention.
