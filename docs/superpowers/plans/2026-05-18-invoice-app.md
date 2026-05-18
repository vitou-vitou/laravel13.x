# Invoice App Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a standalone Laravel 13 invoice management app at `examples/invoice-app/` with customers, invoices, line items, auth, and PDF export.

**Architecture:** Standalone Laravel 13 skeleton inside `examples/invoice-app/` (own `composer.json`, own `vendor/`). Keeps root Laravel framework repo clean. Eloquent models for Customer, Invoice, InvoiceItem. Resource controllers + Blade views. Auth via Laravel starter kit (`laravel/breeze` blade). PDF via `barryvdh/laravel-dompdf`. SQLite for storage.

**Tech Stack:** PHP 8.3, Laravel 13.8, PHPUnit 12, Pint, SQLite, Breeze (Blade), DomPDF.

---

## File Structure (under `examples/invoice-app/`)

- `database/migrations/*_create_customers_table.php` — customers schema
- `database/migrations/*_create_invoices_table.php` — invoices schema (FK customer)
- `database/migrations/*_create_invoice_items_table.php` — line items (FK invoice)
- `app/Models/Customer.php` — Eloquent model
- `app/Models/Invoice.php` — Eloquent model + computed `total`
- `app/Models/InvoiceItem.php` — Eloquent model + computed `line_total`
- `app/Http/Controllers/CustomerController.php` — resource controller
- `app/Http/Controllers/InvoiceController.php` — resource controller
- `app/Http/Controllers/InvoicePdfController.php` — single-action PDF download
- `app/Http/Requests/StoreCustomerRequest.php`, `UpdateCustomerRequest.php`
- `app/Http/Requests/StoreInvoiceRequest.php`, `UpdateInvoiceRequest.php`
- `resources/views/customers/{index,create,edit,show}.blade.php`
- `resources/views/invoices/{index,create,edit,show,pdf}.blade.php`
- `resources/views/layouts/app.blade.php` — from Breeze
- `routes/web.php` — resource routes + pdf route
- `database/factories/{Customer,Invoice,InvoiceItem}Factory.php`
- `tests/Feature/CustomerTest.php`, `InvoiceTest.php`, `InvoicePdfTest.php`
- `tests/Unit/InvoiceTotalsTest.php`

---

## Task 1: Bootstrap Standalone Laravel Skeleton

**Files:**
- Create: `examples/invoice-app/` (entire skeleton)

- [ ] **Step 1: Create Laravel project**

Run from repo root:
```bash
composer create-project laravel/laravel examples/invoice-app "^13.8" --prefer-dist
```
Expected: `examples/invoice-app/` populated with skeleton, `.env` created, `database/database.sqlite` touched, key generated.

- [ ] **Step 2: Verify default test passes**

```bash
cd examples/invoice-app
php artisan test
```
Expected: PASS (default `ExampleTest`).

- [ ] **Step 3: Commit**

```bash
git add examples/invoice-app
git commit -m "feat(examples): scaffold invoice-app Laravel 13 skeleton"
```

---

## Task 2: Install Auth (Breeze) + DomPDF

**Files:**
- Modify: `examples/invoice-app/composer.json`

- [ ] **Step 1: Require Breeze + DomPDF**

```bash
cd examples/invoice-app
composer require laravel/breeze --dev
composer require barryvdh/laravel-dompdf
```

- [ ] **Step 2: Install Breeze (blade stack, no dark mode, phpunit)**

```bash
php artisan breeze:install blade --pest=0
npm install
npm run build
php artisan migrate
```
Expected: auth scaffolding installed, migrations run.

- [ ] **Step 3: Run tests**

```bash
php artisan test
```
Expected: all Breeze auth tests PASS.

- [ ] **Step 4: Commit**

```bash
git add examples/invoice-app
git commit -m "feat(invoice-app): add breeze auth and dompdf"
```

---

## Task 3: Customer Model + Migration (TDD)

**Files:**
- Create: `database/migrations/*_create_customers_table.php`
- Create: `app/Models/Customer.php`
- Create: `database/factories/CustomerFactory.php`
- Create: `tests/Unit/CustomerModelTest.php`

- [ ] **Step 1: Write failing model test**

`tests/Unit/CustomerModelTest.php`:
```php
<?php

namespace Tests\Unit;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_be_created_with_required_fields(): void
    {
        $customer = Customer::create([
            'name' => 'Acme Corp',
            'email' => 'billing@acme.test',
            'address' => '1 Way',
        ]);

        $this->assertDatabaseHas('customers', ['email' => 'billing@acme.test']);
        $this->assertSame('Acme Corp', $customer->name);
    }
}
```

- [ ] **Step 2: Run — expect fail**

```bash
php artisan test --filter=CustomerModelTest
```
Expected: FAIL (no `Customer` class / no table).

- [ ] **Step 3: Generate migration + model + factory**

```bash
php artisan make:model Customer -mf
```

- [ ] **Step 4: Fill migration**

`database/migrations/*_create_customers_table.php` `up()`:
```php
Schema::create('customers', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->string('address');
    $table->timestamps();
});
```

- [ ] **Step 5: Fill model**

`app/Models/Customer.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'address'];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
```

- [ ] **Step 6: Fill factory**

`database/factories/CustomerFactory.php`:
```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'email' => $this->faker->unique()->companyEmail(),
            'address' => $this->faker->address(),
        ];
    }
}
```

- [ ] **Step 7: Run — expect pass**

```bash
php artisan test --filter=CustomerModelTest
```
Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add examples/invoice-app
git commit -m "feat(invoice-app): add Customer model and migration"
```

---

## Task 4: Invoice + InvoiceItem Models (TDD)

**Files:**
- Create: `database/migrations/*_create_invoices_table.php`
- Create: `database/migrations/*_create_invoice_items_table.php`
- Create: `app/Models/Invoice.php`
- Create: `app/Models/InvoiceItem.php`
- Create: `database/factories/InvoiceFactory.php`
- Create: `database/factories/InvoiceItemFactory.php`
- Create: `tests/Unit/InvoiceTotalsTest.php`

- [ ] **Step 1: Write failing totals test**

`tests/Unit/InvoiceTotalsTest.php`:
```php
<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTotalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_total_sums_line_items(): void
    {
        $customer = Customer::factory()->create();
        $invoice = Invoice::factory()->for($customer)->create();

        InvoiceItem::factory()->for($invoice)->create(['quantity' => 2, 'unit_price' => 50.00]);
        InvoiceItem::factory()->for($invoice)->create(['quantity' => 1, 'unit_price' => 25.50]);

        $this->assertEqualsWithDelta(125.50, $invoice->fresh()->total, 0.001);
    }

    public function test_line_total_is_quantity_times_unit_price(): void
    {
        $item = new InvoiceItem(['quantity' => 3, 'unit_price' => 10.00]);
        $this->assertEqualsWithDelta(30.00, $item->line_total, 0.001);
    }
}
```

- [ ] **Step 2: Run — expect fail**

```bash
php artisan test --filter=InvoiceTotalsTest
```
Expected: FAIL.

- [ ] **Step 3: Generate**

```bash
php artisan make:model Invoice -mf
php artisan make:model InvoiceItem -mf
```

- [ ] **Step 4: Fill invoices migration**

```php
Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
    $table->string('number')->unique();
    $table->date('issued_on');
    $table->date('due_on');
    $table->enum('status', ['draft', 'sent', 'paid'])->default('draft');
    $table->timestamps();
});
```

- [ ] **Step 5: Fill invoice_items migration**

```php
Schema::create('invoice_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
    $table->string('description');
    $table->unsignedInteger('quantity');
    $table->decimal('unit_price', 10, 2);
    $table->timestamps();
});
```

- [ ] **Step 6: Fill Invoice model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'number', 'issued_on', 'due_on', 'status'];

    protected $casts = [
        'issued_on' => 'date',
        'due_on' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    protected function total(): Attribute
    {
        return Attribute::get(fn () => $this->items->sum(fn ($i) => $i->line_total));
    }
}
```

- [ ] **Step 7: Fill InvoiceItem model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = ['invoice_id', 'description', 'quantity', 'unit_price'];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    protected function lineTotal(): Attribute
    {
        return Attribute::get(fn () => round($this->quantity * (float) $this->unit_price, 2));
    }
}
```

- [ ] **Step 8: Fill factories**

`InvoiceFactory.php`:
```php
public function definition(): array
{
    return [
        'customer_id' => \App\Models\Customer::factory(),
        'number' => 'INV-' . $this->faker->unique()->numerify('######'),
        'issued_on' => now()->toDateString(),
        'due_on' => now()->addDays(30)->toDateString(),
        'status' => 'draft',
    ];
}
```

`InvoiceItemFactory.php`:
```php
public function definition(): array
{
    return [
        'invoice_id' => \App\Models\Invoice::factory(),
        'description' => $this->faker->sentence(3),
        'quantity' => $this->faker->numberBetween(1, 5),
        'unit_price' => $this->faker->randomFloat(2, 10, 500),
    ];
}
```

- [ ] **Step 9: Run — expect pass**

```bash
php artisan migrate:fresh
php artisan test --filter=InvoiceTotalsTest
```
Expected: PASS.

- [ ] **Step 10: Commit**

```bash
git add examples/invoice-app
git commit -m "feat(invoice-app): add Invoice and InvoiceItem models with totals"
```

---

## Task 5: Customer CRUD Controller + Routes (TDD)

**Files:**
- Create: `app/Http/Controllers/CustomerController.php`
- Create: `app/Http/Requests/StoreCustomerRequest.php`
- Create: `app/Http/Requests/UpdateCustomerRequest.php`
- Modify: `routes/web.php`
- Create: `resources/views/customers/{index,create,edit,show}.blade.php`
- Create: `tests/Feature/CustomerTest.php`

- [ ] **Step 1: Write failing feature test**

`tests/Feature/CustomerTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_list_customers(): void
    {
        $this->get('/customers')->assertRedirect('/login');
    }

    public function test_user_can_create_customer(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/customers', [
                'name' => 'Acme',
                'email' => 'a@a.test',
                'address' => '1 St',
            ])
            ->assertRedirect('/customers');

        $this->assertDatabaseHas('customers', ['email' => 'a@a.test']);
    }

    public function test_user_can_update_customer(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs(User::factory()->create())
            ->put("/customers/{$customer->id}", [
                'name' => 'Updated',
                'email' => $customer->email,
                'address' => $customer->address,
            ])
            ->assertRedirect('/customers');

        $this->assertSame('Updated', $customer->fresh()->name);
    }

    public function test_user_can_delete_customer(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete("/customers/{$customer->id}")
            ->assertRedirect('/customers');

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}
```

- [ ] **Step 2: Run — expect fail**

```bash
php artisan test --filter=CustomerTest
```
Expected: FAIL.

- [ ] **Step 3: Generate controller + form requests**

```bash
php artisan make:controller CustomerController --resource --model=Customer
php artisan make:request StoreCustomerRequest
php artisan make:request UpdateCustomerRequest
```

- [ ] **Step 4: Fill StoreCustomerRequest**

```php
public function authorize(): bool { return true; }

public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'address' => ['required', 'string', 'max:1000'],
    ];
}
```

- [ ] **Step 5: Fill UpdateCustomerRequest** (identical rules)

- [ ] **Step 6: Fill CustomerController**

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        return view('customers.index', ['customers' => Customer::latest()->paginate(20)]);
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        Customer::create($request->validated());
        return redirect('/customers');
    }

    public function show(Customer $customer)
    {
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());
        return redirect('/customers');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect('/customers');
    }
}
```

- [ ] **Step 7: Add routes**

`routes/web.php` (inside `auth` middleware group):
```php
Route::middleware(['auth'])->group(function () {
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);
});
```

- [ ] **Step 8: Create minimal Blade views**

`resources/views/customers/index.blade.php`:
```blade
<x-app-layout>
    <div class="p-6">
        <a href="{{ route('customers.create') }}" class="text-blue-600">+ New Customer</a>
        <table class="mt-4 w-full">
            <thead><tr><th>Name</th><th>Email</th><th></th></tr></thead>
            <tbody>
            @foreach ($customers as $c)
                <tr>
                    <td><a href="{{ route('customers.show', $c) }}">{{ $c->name }}</a></td>
                    <td>{{ $c->email }}</td>
                    <td>
                        <a href="{{ route('customers.edit', $c) }}">Edit</a>
                        <form method="POST" action="{{ route('customers.destroy', $c) }}" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $customers->links() }}
    </div>
</x-app-layout>
```

`resources/views/customers/create.blade.php`:
```blade
<x-app-layout>
    <form method="POST" action="{{ route('customers.store') }}" class="p-6 space-y-4">
        @csrf
        <input name="name" placeholder="Name" class="border p-2" value="{{ old('name') }}">
        <input name="email" placeholder="Email" class="border p-2" value="{{ old('email') }}">
        <textarea name="address" placeholder="Address" class="border p-2">{{ old('address') }}</textarea>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2">Create</button>
    </form>
</x-app-layout>
```

`resources/views/customers/edit.blade.php`:
```blade
<x-app-layout>
    <form method="POST" action="{{ route('customers.update', $customer) }}" class="p-6 space-y-4">
        @csrf @method('PUT')
        <input name="name" class="border p-2" value="{{ old('name', $customer->name) }}">
        <input name="email" class="border p-2" value="{{ old('email', $customer->email) }}">
        <textarea name="address" class="border p-2">{{ old('address', $customer->address) }}</textarea>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2">Update</button>
    </form>
</x-app-layout>
```

`resources/views/customers/show.blade.php`:
```blade
<x-app-layout>
    <div class="p-6">
        <h1>{{ $customer->name }}</h1>
        <p>{{ $customer->email }}</p>
        <p>{{ $customer->address }}</p>
    </div>
</x-app-layout>
```

- [ ] **Step 9: Run — expect pass**

```bash
php artisan test --filter=CustomerTest
```
Expected: PASS.

- [ ] **Step 10: Commit**

```bash
git add examples/invoice-app
git commit -m "feat(invoice-app): customer CRUD with auth and form requests"
```

---

## Task 6: Invoice CRUD + Nested Items (TDD)

**Files:**
- Create: `app/Http/Controllers/InvoiceController.php`
- Create: `app/Http/Requests/StoreInvoiceRequest.php`
- Create: `app/Http/Requests/UpdateInvoiceRequest.php`
- Modify: `routes/web.php`
- Create: `resources/views/invoices/{index,create,edit,show}.blade.php`
- Create: `tests/Feature/InvoiceTest.php`

- [ ] **Step 1: Write failing feature test**

`tests/Feature/InvoiceTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_invoice_with_items(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post('/invoices', [
                'customer_id' => $customer->id,
                'number' => 'INV-001',
                'issued_on' => '2026-01-01',
                'due_on' => '2026-01-31',
                'status' => 'draft',
                'items' => [
                    ['description' => 'Service A', 'quantity' => 2, 'unit_price' => 100.00],
                    ['description' => 'Service B', 'quantity' => 1, 'unit_price' => 50.00],
                ],
            ])
            ->assertRedirect('/invoices');

        $this->assertDatabaseHas('invoices', ['number' => 'INV-001']);
        $this->assertDatabaseCount('invoice_items', 2);
    }

    public function test_user_can_view_invoice_with_total(): void
    {
        $invoice = Invoice::factory()
            ->hasItems(2, ['quantity' => 1, 'unit_price' => 50])
            ->create();

        $this->actingAs(User::factory()->create())
            ->get("/invoices/{$invoice->id}")
            ->assertOk()
            ->assertSee($invoice->number)
            ->assertSee('100.00');
    }
}
```

- [ ] **Step 2: Run — expect fail**

```bash
php artisan test --filter=InvoiceTest
```
Expected: FAIL.

- [ ] **Step 3: Generate**

```bash
php artisan make:controller InvoiceController --resource --model=Invoice
php artisan make:request StoreInvoiceRequest
php artisan make:request UpdateInvoiceRequest
```

- [ ] **Step 4: Fill StoreInvoiceRequest**

```php
public function authorize(): bool { return true; }

public function rules(): array
{
    return [
        'customer_id' => ['required', 'exists:customers,id'],
        'number' => ['required', 'string', 'max:64'],
        'issued_on' => ['required', 'date'],
        'due_on' => ['required', 'date', 'after_or_equal:issued_on'],
        'status' => ['required', 'in:draft,sent,paid'],
        'items' => ['required', 'array', 'min:1'],
        'items.*.description' => ['required', 'string', 'max:255'],
        'items.*.quantity' => ['required', 'integer', 'min:1'],
        'items.*.unit_price' => ['required', 'numeric', 'min:0'],
    ];
}
```

- [ ] **Step 5: Fill UpdateInvoiceRequest** (same rules; `number` add `unique:invoices,number,{$this->invoice->id}` if needed)

- [ ] **Step 6: Fill InvoiceController**

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index()
    {
        return view('invoices.index', [
            'invoices' => Invoice::with('customer')->latest()->paginate(20),
        ]);
    }

    public function create()
    {
        return view('invoices.create', ['customers' => Customer::orderBy('name')->get()]);
    }

    public function store(StoreInvoiceRequest $request)
    {
        DB::transaction(function () use ($request) {
            $invoice = Invoice::create($request->safe()->except('items'));
            $invoice->items()->createMany($request->validated('items'));
        });

        return redirect('/invoices');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('customer', 'items');
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('items');
        return view('invoices.edit', [
            'invoice' => $invoice,
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        DB::transaction(function () use ($request, $invoice) {
            $invoice->update($request->safe()->except('items'));
            $invoice->items()->delete();
            $invoice->items()->createMany($request->validated('items'));
        });

        return redirect('/invoices');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect('/invoices');
    }
}
```

- [ ] **Step 7: Add routes**

In `routes/web.php` inside the same `auth` group:
```php
Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);
```

- [ ] **Step 8: Create Blade views**

`resources/views/invoices/index.blade.php`:
```blade
<x-app-layout>
    <div class="p-6">
        <a href="{{ route('invoices.create') }}" class="text-blue-600">+ New Invoice</a>
        <table class="mt-4 w-full">
            <thead><tr><th>Number</th><th>Customer</th><th>Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @foreach ($invoices as $inv)
                <tr>
                    <td><a href="{{ route('invoices.show', $inv) }}">{{ $inv->number }}</a></td>
                    <td>{{ $inv->customer->name }}</td>
                    <td>{{ number_format($inv->total, 2) }}</td>
                    <td>{{ $inv->status }}</td>
                    <td>
                        <a href="{{ route('invoices.edit', $inv) }}">Edit</a>
                        <a href="{{ route('invoices.pdf', $inv) }}">PDF</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $invoices->links() }}
    </div>
</x-app-layout>
```

`resources/views/invoices/show.blade.php`:
```blade
<x-app-layout>
    <div class="p-6">
        <h1>{{ $invoice->number }}</h1>
        <p>Customer: {{ $invoice->customer->name }}</p>
        <p>Issued: {{ $invoice->issued_on->toDateString() }} — Due: {{ $invoice->due_on->toDateString() }}</p>
        <table class="mt-4 w-full">
            <thead><tr><th>Description</th><th>Qty</th><th>Unit</th><th>Total</th></tr></thead>
            <tbody>
            @foreach ($invoice->items as $i)
                <tr>
                    <td>{{ $i->description }}</td>
                    <td>{{ $i->quantity }}</td>
                    <td>{{ number_format($i->unit_price, 2) }}</td>
                    <td>{{ number_format($i->line_total, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot><tr><td colspan="3" class="text-right font-bold">Total</td><td>{{ number_format($invoice->total, 2) }}</td></tr></tfoot>
        </table>
        <a href="{{ route('invoices.pdf', $invoice) }}" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2">Download PDF</a>
    </div>
</x-app-layout>
```

`resources/views/invoices/create.blade.php`:
```blade
<x-app-layout>
    <form method="POST" action="{{ route('invoices.store') }}" class="p-6 space-y-4">
        @csrf
        <select name="customer_id" class="border p-2">
            @foreach ($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
        <input name="number" placeholder="INV-001" class="border p-2">
        <input type="date" name="issued_on" class="border p-2">
        <input type="date" name="due_on" class="border p-2">
        <select name="status" class="border p-2">
            <option value="draft">Draft</option>
            <option value="sent">Sent</option>
            <option value="paid">Paid</option>
        </select>
        <div id="items" class="space-y-2">
            <div class="flex gap-2">
                <input name="items[0][description]" placeholder="Description" class="border p-2 flex-1">
                <input name="items[0][quantity]" type="number" min="1" value="1" class="border p-2 w-20">
                <input name="items[0][unit_price]" type="number" step="0.01" min="0" value="0" class="border p-2 w-32">
            </div>
        </div>
        <button type="button" onclick="addRow()" class="bg-gray-300 px-3 py-1">+ Item</button>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2">Create</button>
    </form>
    <script>
        let idx = 1;
        function addRow() {
            const div = document.createElement('div');
            div.className = 'flex gap-2';
            div.innerHTML = `
                <input name="items[${idx}][description]" placeholder="Description" class="border p-2 flex-1">
                <input name="items[${idx}][quantity]" type="number" min="1" value="1" class="border p-2 w-20">
                <input name="items[${idx}][unit_price]" type="number" step="0.01" min="0" value="0" class="border p-2 w-32">
            `;
            document.getElementById('items').appendChild(div);
            idx++;
        }
    </script>
</x-app-layout>
```

`resources/views/invoices/edit.blade.php`: same structure as `create.blade.php` but `@method('PUT')`, action `route('invoices.update', $invoice)`, and pre-fill values from `$invoice` and loop `$invoice->items` to seed rows.

- [ ] **Step 9: Run — expect pass**

```bash
php artisan test --filter=InvoiceTest
```
Expected: PASS.

- [ ] **Step 10: Commit**

```bash
git add examples/invoice-app
git commit -m "feat(invoice-app): invoice CRUD with nested line items"
```

---

## Task 7: PDF Export (TDD)

**Files:**
- Create: `app/Http/Controllers/InvoicePdfController.php`
- Modify: `routes/web.php`
- Create: `resources/views/invoices/pdf.blade.php`
- Create: `tests/Feature/InvoicePdfTest.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/InvoicePdfTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_download_invoice_pdf(): void
    {
        $invoice = Invoice::factory()->hasItems(1)->create();

        $response = $this->actingAs(User::factory()->create())
            ->get(route('invoices.pdf', $invoice));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }
}
```

- [ ] **Step 2: Run — expect fail**

```bash
php artisan test --filter=InvoicePdfTest
```
Expected: FAIL (route undefined).

- [ ] **Step 3: Create controller**

```bash
php artisan make:controller InvoicePdfController --invokable
```

Content:
```php
<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfController extends Controller
{
    public function __invoke(Invoice $invoice)
    {
        $invoice->load('customer', 'items');
        return Pdf::loadView('invoices.pdf', ['invoice' => $invoice])
            ->download("{$invoice->number}.pdf");
    }
}
```

- [ ] **Step 4: Add route**

In `auth` group, before `Route::resource('invoices', ...)`:
```php
Route::get('invoices/{invoice}/pdf', \App\Http\Controllers\InvoicePdfController::class)
    ->name('invoices.pdf');
```

- [ ] **Step 5: Create PDF Blade**

`resources/views/invoices/pdf.blade.php`:
```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px; text-align: left; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Invoice {{ $invoice->number }}</h1>
    <p>
        <strong>Bill To:</strong><br>
        {{ $invoice->customer->name }}<br>
        {{ $invoice->customer->address }}
    </p>
    <p>Issued: {{ $invoice->issued_on->toDateString() }} | Due: {{ $invoice->due_on->toDateString() }}</p>
    <table>
        <thead><tr><th>Description</th><th>Qty</th><th>Unit</th><th>Total</th></tr></thead>
        <tbody>
        @foreach ($invoice->items as $i)
            <tr>
                <td>{{ $i->description }}</td>
                <td>{{ $i->quantity }}</td>
                <td class="right">{{ number_format($i->unit_price, 2) }}</td>
                <td class="right">{{ number_format($i->line_total, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot><tr><td colspan="3" class="right"><strong>Total</strong></td><td class="right"><strong>{{ number_format($invoice->total, 2) }}</strong></td></tr></tfoot>
    </table>
</body>
</html>
```

- [ ] **Step 6: Run — expect pass**

```bash
php artisan test --filter=InvoicePdfTest
```
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add examples/invoice-app
git commit -m "feat(invoice-app): pdf export via dompdf"
```

---

## Task 8: README + Pint + Final Suite

**Files:**
- Create: `examples/invoice-app/README.md`

- [ ] **Step 1: Write README**

`examples/invoice-app/README.md`:
````markdown
# Invoice App Example

Standalone Laravel 13 invoice management example. Built with Breeze auth, DomPDF.

## Setup

```bash
cd examples/invoice-app
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install && npm run build
php artisan serve
```

Register at `/register`, then visit `/customers` and `/invoices`.

## Test

```bash
php artisan test
```
````

- [ ] **Step 2: Run Pint**

```bash
cd examples/invoice-app
./vendor/bin/pint
```

- [ ] **Step 3: Run full suite**

```bash
php artisan test
```
Expected: all tests PASS.

- [ ] **Step 4: Final commit**

```bash
git add examples/invoice-app
git commit -m "docs(invoice-app): add README and pint format pass"
```

---

## Self-Review Notes

- **Spec coverage:** Customers ✓, Invoices ✓, Line items ✓, Auth ✓, PDF ✓, Tests ✓. README ✓.
- **Placeholders:** none — all code blocks complete. (`UpdateInvoiceRequest` references the same rules as `Store` — engineer copies them; rationale stated.)
- **Type consistency:** `total` (Attribute) and `line_total` (Attribute) match between model and tests. `items` relation name consistent.
- **Composer alignment:** standalone skeleton ≠ root composer.json; root `App\\` autoload untouched. Root project remains Laravel framework dev repo. Each `cd examples/invoice-app` step keeps tooling scoped.

---

## Execution Handoff

Plan complete and saved to `docs/superpowers/plans/2026-05-18-invoice-app.md`. Two execution options:

1. **Subagent-Driven (recommended)** — fresh subagent per task, two-stage review.
2. **Inline Execution** — execute tasks in this session with checkpoints.

Which approach?
