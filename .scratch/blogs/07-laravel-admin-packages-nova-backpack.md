# Laravel Nova and Backpack: Follow the Package Conventions, Don't Build a Second Admin

You join a team whose production application has run on Backpack CRUD or Laravel Nova for three years. A newly hired engineer wants to upgrade the user experience, so they install Filament alongside it or start building a custom Vue SPA for "the new dashboard." Six months later, the application has two different admin login screens, conflicting asset builds, and customer records that can only be edited halfway in each panel.

Laravel has a rich ecosystem of administrative packages: Nova, Backpack, Orchid, and Filament each provide distinct, battle-tested solutions. The danger is never the tool you chose; it is fighting the tool by splitting your admin architecture in half. If you embrace the conventions of the package currently running in your repository, you can deliver robust features without creating years of technical debt.

## Each Package Has Its Own Idiom

Different administrative packages approach CRUD from different angles. Nova uses standalone Resource classes; Backpack uses specialized `CrudController` classes; Orchid structures screens around declarative layouts.

When adding a feature, match the existing idiom in your repository. Here is a typical Backpack CRUD controller:

app/Http/Controllers/Admin/ProductCrudController.php:
```php
namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ProductCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup(): void
    {
        CRUD::setModel(\App\Models\Product::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/product');
        CRUD::setEntityNameStrings('product', 'products');
    }

    protected function setupListOperation(): void
    {
        CRUD::column('name')->type('text');
        CRUD::column('sku')->type('text');
        CRUD::column('price')->type('number')->prefix('$');
        CRUD::column('is_active')->type('boolean');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::field('name')->type('text');
        CRUD::field('sku')->type('text');
        CRUD::field('price')->type('number')->attributes(['step' => '0.01']);
        CRUD::field('is_active')->type('checkbox');
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}
```

This controller relies on Backpack's operation traits. You do not need to create custom views or route files. Backpack generates the data table, the create form, and the update form based on the fields defined in `setup()`.

By contrast, Laravel Nova keeps resources declarative outside of controllers:

app/Nova/Product.php:
```php
namespace App\Nova;

use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Product extends Resource
{
    public static $model = \App\Models\Product::class;
    public static $title = 'name';
    public static $search = ['id', 'name', 'sku'];

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Name')->sortable()->rules('required', 'max:255'),
            Text::make('SKU')->rules('required', 'max:50')->creationRules('unique:products,sku'),
            Currency::make('Price')->currency('USD')->sortable()->rules('required'),
            Boolean::make('Active', 'is_active'),
        ];
    }
}
```

Both patterns are valid, mature, and productive. The mistake is trying to write Nova-style resources inside a Backpack application, or trying to write raw Blade controllers to bypass Nova.

## Keep Business Logic in Standard Laravel Layers

While your presentation layer matches your admin package, your core business logic should stay in standard Laravel classes:

1. **Policies:** Check permissions with `$this->authorize()` or Laravel's Gate facade. All major admin packages integrate with Eloquent policies.
2. **Form Requests:** Use standard validation rule arrays so rules can be shared between public APIs and admin panels.
3. **Action Classes:** If an operation changes money balances or dispatches orders, encapsulate it in a single Action class invoked by the admin controller.

Here is an Action invoked from an admin controller or resource:

app/Actions/ApproveRefundAction.php:
```php
namespace App\Actions;

use App\Models\Refund;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApproveRefundAction
{
    public function execute(Refund $refund, User $adminUser): void
    {
        DB::transaction(function () use ($refund, $adminUser) {
            $refund->update([
                'status' => 'approved',
                'approved_by' => $adminUser->id,
                'approved_at' => now(),
            ]);

            $refund->order->decrement('total_paid', $refund->amount);
            $refund->customer->notifyAboutRefund($refund);
        });
    }
}
```

When you keep domain writes in dedicated Action classes, you protect your application from being locked into any specific admin package. If your company ever decides to migrate from Backpack to Nova or Filament in the future, the business logic remains untouched.

## The Cost of Dual-Admin Sprawl

When a team starts a second admin panel instead of maintaining the existing one, the true cost is rarely visible during development. It shows up in production:

- **Two Session Handlers:** Operators must authenticate twice and manage different session timeout behaviors.
- **Diverging Permissions:** An employee revoked in one panel remains active in the second panel because permission tables diverged.
- **Asset Overhead:** Vite and Webpack configurations become brittle when compiling two different styling frameworks (e.g., Bootstrap for Backpack and Tailwind for Filament) in one repository.

Unless your company is actively executing a phased, time-boxed migration with a sunset date for the legacy panel, refuse to add a second admin tool to `composer.json`.

## What Can Go Wrong

The most frequent breaking change occurs during major framework upgrades. Admin packages tightly couple with Laravel's router and form requests. Always inspect the package upgrade guide before running `composer update`:

```bash
# Check version constraints before upgrading Laravel core
composer why backpack/crud
composer why laravel/nova
```

Pin your admin package dependencies to compatible semantic versions so automated bot updates cannot break your admin screens.

## Summary

Respect the tooling already serving your business. Whether your repository runs Backpack, Nova, Orchid, or Filament, follow that package's established patterns and generators. Keep critical domain writes in dedicated Laravel Action classes and enforce access via Eloquent Policies.

One consistent, well-maintained admin panel that your staff can use without confusion is worth ten unfinished rewrites.

## Further Reading

- [Laravel Nova Official Documentation](https://nova.laravel.com/docs)
- [Backpack for Laravel Documentation](https://backpackforlaravel.com/docs)
- [Orchid Platform Documentation](https://orchid.software/en/docs)
- [Laravel Authorization Policies](https://laravel.com/docs/authorization#creating-policies)

Has your team navigated a large-scale admin panel migration? Share your insights and lessons learned in the comments below.
