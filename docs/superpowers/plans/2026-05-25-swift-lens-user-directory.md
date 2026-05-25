# swift-lens-4829 User Directory Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a read-only user directory admin panel at `examples/swift-lens-4829` that wraps the SQL filter script into a Laravel 13 Blade + Alpine.js UI.

**Architecture:** Clone `examples/invoice-app`, strip auth/invoice/customer code, replace with a single `UserController@index` that translates GET filter params into Eloquent `when()` chains, rendering a filter sidebar + paginated table.

**Tech Stack:** Laravel 13, Blade, Tailwind CSS, Alpine.js, SQLite, PHPUnit

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `examples/swift-lens-4829/` | Full Laravel app (cloned from invoice-app) |
| Modify | `examples/swift-lens-4829/composer.json` | Update app name |
| Modify | `examples/swift-lens-4829/.env` | Set APP_NAME, DB path |
| Replace | `examples/swift-lens-4829/database/migrations/0001_01_01_000000_create_users_table.php` | Users table with filter columns |
| Delete | invoice/customer migrations | Not needed |
| Create | `examples/swift-lens-4829/database/factories/UserFactory.php` | Fake user data |
| Create | `examples/swift-lens-4829/database/seeders/UserSeeder.php` | 200 seeded users |
| Modify | `examples/swift-lens-4829/database/seeders/DatabaseSeeder.php` | Call UserSeeder |
| Create | `examples/swift-lens-4829/app/Models/User.php` | Fillable + cast |
| Create | `examples/swift-lens-4829/app/Http/Controllers/UserController.php` | index() with filter logic |
| Replace | `examples/swift-lens-4829/routes/web.php` | GET / → UserController@index only |
| Create | `examples/swift-lens-4829/resources/views/users/index.blade.php` | Filter sidebar + table |
| Modify | `examples/swift-lens-4829/resources/views/layouts/app.blade.php` | Update app title |
| Create | `examples/swift-lens-4829/tests/Feature/UserDirectoryTest.php` | Filter + pagination tests |

---

## Task 1: Clone invoice-app

**Files:**
- Create: `examples/swift-lens-4829/` (full copy)

- [ ] **Step 1: Copy invoice-app to new directory**

```powershell
Copy-Item -Recurse -Path "D:\laravel13.x\examples\invoice-app" -Destination "D:\laravel13.x\examples\swift-lens-4829"
```

- [ ] **Step 2: Remove vendor and node_modules (will reinstall)**

```powershell
Remove-Item -Recurse -Force "D:\laravel13.x\examples\swift-lens-4829\vendor"
Remove-Item -Recurse -Force "D:\laravel13.x\examples\swift-lens-4829\node_modules"
Remove-Item -Force "D:\laravel13.x\examples\swift-lens-4829\database\database.sqlite"
Remove-Item -Force "D:\laravel13.x\examples\swift-lens-4829\.phpunit.result.cache" -ErrorAction SilentlyContinue
```

- [ ] **Step 3: Commit scaffold**

```bash
git add examples/swift-lens-4829/
git commit -m "chore: scaffold swift-lens-4829 from invoice-app clone"
```

---

## Task 2: Configure app identity

**Files:**
- Modify: `examples/swift-lens-4829/composer.json`
- Modify: `examples/swift-lens-4829/.env`

- [ ] **Step 1: Update composer.json name**

In `examples/swift-lens-4829/composer.json`, change line 3:
```json
"name": "swift-lens/user-directory",
```
Also remove `barryvdh/laravel-dompdf` from `require` (not needed):
```json
"require": {
    "php": "^8.3",
    "laravel/framework": "^13.0",
    "laravel/tinker": "^3.0"
},
```

- [ ] **Step 2: Update .env**

In `examples/swift-lens-4829/.env`, set:
```
APP_NAME="Swift Lens"
APP_URL=http://localhost:8001
DB_CONNECTION=sqlite
```

Ensure this line exists (create sqlite file path):
```
DB_DATABASE=/absolute/path/to/examples/swift-lens-4829/database/database.sqlite
```

Actually, for SQLite with Laravel 13, just leave `DB_DATABASE` as default — Laravel resolves `database/database.sqlite` relative to base path. Remove any MySQL lines.

- [ ] **Step 3: Create fresh SQLite database file**

```powershell
New-Item -ItemType File -Path "D:\laravel13.x\examples\swift-lens-4829\database\database.sqlite" -Force
```

- [ ] **Step 4: Commit config**

```bash
git add examples/swift-lens-4829/composer.json examples/swift-lens-4829/.env
git commit -m "chore: configure swift-lens-4829 app identity"
```

---

## Task 3: Clean up unused code from clone

**Files:**
- Delete: `examples/swift-lens-4829/app/Http/Controllers/CustomerController.php`
- Delete: `examples/swift-lens-4829/app/Http/Controllers/InvoiceController.php`
- Delete: `examples/swift-lens-4829/app/Http/Controllers/InvoicePdfController.php`
- Delete: `examples/swift-lens-4829/app/Http/Controllers/ProfileController.php`
- Delete: `examples/swift-lens-4829/app/Http/Controllers/Auth/` (whole directory)
- Delete: `examples/swift-lens-4829/app/Http/Requests/` (whole directory)
- Delete: `examples/swift-lens-4829/app/Models/Customer.php`
- Delete: `examples/swift-lens-4829/app/Models/Invoice.php`
- Delete: `examples/swift-lens-4829/app/Models/InvoiceItem.php`
- Delete: `examples/swift-lens-4829/app/View/Components/GuestLayout.php`
- Delete: `examples/swift-lens-4829/database/factories/CustomerFactory.php`
- Delete: `examples/swift-lens-4829/database/factories/InvoiceFactory.php`
- Delete: `examples/swift-lens-4829/database/factories/InvoiceItemFactory.php`
- Delete: `examples/swift-lens-4829/database/migrations/2026_05_18_134221_create_customers_table.php`
- Delete: `examples/swift-lens-4829/database/migrations/2026_05_18_134328_create_invoices_table.php`
- Delete: `examples/swift-lens-4829/database/migrations/2026_05_18_134329_create_invoice_items_table.php`
- Delete: `examples/swift-lens-4829/resources/views/auth/`
- Delete: `examples/swift-lens-4829/resources/views/customers/`
- Delete: `examples/swift-lens-4829/resources/views/invoices/`
- Delete: `examples/swift-lens-4829/resources/views/profile/`
- Delete: `examples/swift-lens-4829/resources/views/layouts/guest.blade.php`
- Delete: `examples/swift-lens-4829/routes/auth.php`

- [ ] **Step 1: Delete unused controllers and models**

```powershell
$base = "D:\laravel13.x\examples\swift-lens-4829"
Remove-Item "$base\app\Http\Controllers\CustomerController.php" -Force
Remove-Item "$base\app\Http\Controllers\InvoiceController.php" -Force
Remove-Item "$base\app\Http\Controllers\InvoicePdfController.php" -Force
Remove-Item "$base\app\Http\Controllers\ProfileController.php" -Force
Remove-Item -Recurse -Force "$base\app\Http\Controllers\Auth"
Remove-Item -Recurse -Force "$base\app\Http\Requests"
Remove-Item "$base\app\Models\Customer.php" -Force
Remove-Item "$base\app\Models\Invoice.php" -Force
Remove-Item "$base\app\Models\InvoiceItem.php" -Force
Remove-Item "$base\app\View\Components\GuestLayout.php" -Force
```

- [ ] **Step 2: Delete unused factories and migrations**

```powershell
$base = "D:\laravel13.x\examples\swift-lens-4829"
Remove-Item "$base\database\factories\CustomerFactory.php" -Force
Remove-Item "$base\database\factories\InvoiceFactory.php" -Force
Remove-Item "$base\database\factories\InvoiceItemFactory.php" -Force
Remove-Item "$base\database\migrations\2026_05_18_134221_create_customers_table.php" -Force
Remove-Item "$base\database\migrations\2026_05_18_134328_create_invoices_table.php" -Force
Remove-Item "$base\database\migrations\2026_05_18_134329_create_invoice_items_table.php" -Force
```

- [ ] **Step 3: Delete unused views and routes**

```powershell
$base = "D:\laravel13.x\examples\swift-lens-4829"
Remove-Item -Recurse -Force "$base\resources\views\auth"
Remove-Item -Recurse -Force "$base\resources\views\customers"
Remove-Item -Recurse -Force "$base\resources\views\invoices"
Remove-Item -Recurse -Force "$base\resources\views\profile"
Remove-Item "$base\resources\views\layouts\guest.blade.php" -Force
Remove-Item "$base\routes\auth.php" -Force
```

- [ ] **Step 4: Commit cleanup**

```bash
git add examples/swift-lens-4829/
git commit -m "chore: remove invoice/customer/auth code from swift-lens-4829"
```

---

## Task 4: Users migration

**Files:**
- Replace: `examples/swift-lens-4829/database/migrations/0001_01_01_000000_create_users_table.php`

- [ ] **Step 1: Write the failing test first**

Create `examples/swift-lens-4829/tests/Feature/UserDirectoryTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_has_expected_columns(): void
    {
        $columns = \Schema::getColumnListing('users');

        $this->assertContains('username', $columns);
        $this->assertContains('country', $columns);
        $this->assertContains('city', $columns);
        $this->assertContains('device_type', $columns);
        $this->assertContains('signup_source', $columns);
        $this->assertContains('avatar', $columns);
        $this->assertContains('last_login_at', $columns);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```powershell
cd D:\laravel13.x\examples\swift-lens-4829
composer install --no-interaction
php artisan key:generate
php artisan test --filter=test_users_table_has_expected_columns
```

Expected: FAIL — columns missing.

- [ ] **Step 3: Replace users migration**

Replace `examples/swift-lens-4829/database/migrations/0001_01_01_000000_create_users_table.php` with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 100);
            $table->string('email', 150)->unique();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('device_type', 20)->nullable();
            $table->string('signup_source', 20)->nullable();
            $table->string('avatar', 255)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
```

- [ ] **Step 4: Run test to verify it passes**

```powershell
php artisan test --filter=test_users_table_has_expected_columns
```

Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add examples/swift-lens-4829/database/migrations/ examples/swift-lens-4829/tests/
git commit -m "feat: add users table migration for swift-lens-4829"
```

---

## Task 5: User model, factory, seeder

**Files:**
- Create: `examples/swift-lens-4829/app/Models/User.php`
- Create: `examples/swift-lens-4829/database/factories/UserFactory.php`
- Create: `examples/swift-lens-4829/database/seeders/UserSeeder.php`
- Modify: `examples/swift-lens-4829/database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Write failing test**

Add to `examples/swift-lens-4829/tests/Feature/UserDirectoryTest.php`:

```php
public function test_seeder_creates_200_users(): void
{
    \Database\Seeders\UserSeeder::class;
    $this->seed(\Database\Seeders\UserSeeder::class);

    $this->assertDatabaseCount('users', 200);
}
```

- [ ] **Step 2: Run test to verify it fails**

```powershell
php artisan test --filter=test_seeder_creates_200_users
```

Expected: FAIL — UserSeeder not found.

- [ ] **Step 3: Create User model**

Create `examples/swift-lens-4829/app/Models/User.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'email',
        'country',
        'city',
        'device_type',
        'signup_source',
        'avatar',
        'last_login_at',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
    ];
}
```

- [ ] **Step 4: Create UserFactory**

Create `examples/swift-lens-4829/database/factories/UserFactory.php`:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    public function definition(): array
    {
        $countries = ['US', 'KH', 'TH', 'JP', 'FR', 'DE', 'AU', 'SG'];
        $cities = [
            'US' => ['New York', 'Los Angeles', 'Chicago'],
            'KH' => ['Phnom Penh', 'Siem Reap', 'Battambang'],
            'TH' => ['Bangkok', 'Chiang Mai', 'Phuket'],
            'JP' => ['Tokyo', 'Osaka', 'Kyoto'],
            'FR' => ['Paris', 'Lyon', 'Marseille'],
            'DE' => ['Berlin', 'Munich', 'Hamburg'],
            'AU' => ['Sydney', 'Melbourne', 'Brisbane'],
            'SG' => ['Singapore'],
        ];
        $country = $this->faker->randomElement($countries);
        $isActive = $this->faker->boolean(70);

        return [
            'username'      => $this->faker->unique()->userName(),
            'email'         => $this->faker->unique()->safeEmail(),
            'country'       => $country,
            'city'          => $this->faker->randomElement($cities[$country]),
            'device_type'   => $this->faker->randomElement(['web', 'mobile', 'tablet']),
            'signup_source' => $this->faker->randomElement(['organic', 'referral', 'social', 'paid']),
            'avatar'        => $this->faker->boolean(60) ? 'https://i.pravatar.cc/80?u=' . $this->faker->uuid() : null,
            'last_login_at' => $isActive
                ? $this->faker->dateTimeBetween('-29 days', 'now')
                : $this->faker->optional(0.7)->dateTimeBetween('-1 year', '-31 days'),
            'created_at'    => $this->faker->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
```

- [ ] **Step 5: Create UserSeeder**

Create `examples/swift-lens-4829/database/seeders/UserSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->count(200)->create();
    }
}
```

- [ ] **Step 6: Update DatabaseSeeder**

Replace `examples/swift-lens-4829/database/seeders/DatabaseSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UserSeeder::class);
    }
}
```

- [ ] **Step 7: Run test to verify it passes**

```powershell
php artisan test --filter=test_seeder_creates_200_users
```

Expected: PASS

- [ ] **Step 8: Commit**

```bash
git add examples/swift-lens-4829/app/Models/User.php examples/swift-lens-4829/database/
git commit -m "feat: add User model, factory, and seeder (200 rows)"
```

---

## Task 6: UserController with filter logic

**Files:**
- Create: `examples/swift-lens-4829/app/Http/Controllers/UserController.php`

- [ ] **Step 1: Write failing tests**

Add to `examples/swift-lens-4829/tests/Feature/UserDirectoryTest.php`:

```php
public function test_index_returns_200(): void
{
    $this->seed(\Database\Seeders\UserSeeder::class);
    $response = $this->get('/');
    $response->assertStatus(200);
}

public function test_keyword_filter_narrows_results(): void
{
    \App\Models\User::factory()->create(['username' => 'zephyr_unique_xyz', 'email' => 'zephyr@example.com', 'country' => 'US', 'city' => 'New York']);
    \App\Models\User::factory()->count(10)->create();

    $response = $this->get('/?keyword=zephyr_unique_xyz');
    $response->assertStatus(200);
    $response->assertSee('zephyr_unique_xyz');
}

public function test_country_filter_narrows_results(): void
{
    \App\Models\User::factory()->create(['username' => 'user_kh_only', 'email' => 'kh@example.com', 'country' => 'KH', 'city' => 'Phnom Penh']);
    \App\Models\User::factory()->count(5)->create(['country' => 'US']);

    $response = $this->get('/?country=KH');
    $response->assertStatus(200);
    $response->assertSee('user_kh_only');
}

public function test_pagination_works(): void
{
    \App\Models\User::factory()->count(25)->create();

    $response = $this->get('/');
    $response->assertStatus(200);

    $response2 = $this->get('/?page=2');
    $response2->assertStatus(200);
}
```

- [ ] **Step 2: Run tests to verify they fail**

```powershell
php artisan test --filter=UserDirectoryTest
```

Expected: FAIL on index tests — route not defined.

- [ ] **Step 3: Create UserController**

Create `examples/swift-lens-4829/app/Http/Controllers/UserController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private const ACTIVE_DAYS = 30;

    public function index(Request $request)
    {
        $query = User::query();

        $query->when($request->filled('keyword'), function ($q) use ($request) {
            $kw = $request->keyword;
            $q->where(function ($inner) use ($kw) {
                $inner->where('username', 'like', "%{$kw}%")
                      ->orWhere('email', 'like', "%{$kw}%")
                      ->orWhere('city', 'like', "%{$kw}%")
                      ->orWhere('country', 'like', "%{$kw}%");
            });
        });

        $query->when($request->filled('country'), fn ($q) =>
            $q->where('country', $request->country)
        );

        $query->when($request->filled('city'), fn ($q) =>
            $q->where('city', $request->city)
        );

        $query->when($request->filled('device_type'), fn ($q) =>
            $q->where('device_type', $request->device_type)
        );

        $query->when($request->filled('signup_source'), fn ($q) =>
            $q->where('signup_source', $request->signup_source)
        );

        $query->when($request->has('has_avatar') && $request->has_avatar !== '', function ($q) use ($request) {
            if ($request->has_avatar == '1') {
                $q->whereNotNull('avatar');
            } else {
                $q->whereNull('avatar');
            }
        });

        $query->when($request->has('is_active') && $request->is_active !== '', function ($q) use ($request) {
            $threshold = now()->subDays(self::ACTIVE_DAYS);
            if ($request->is_active == '1') {
                $q->where('last_login_at', '>=', $threshold);
            } else {
                $q->where(fn ($inner) =>
                    $inner->where('last_login_at', '<', $threshold)
                          ->orWhereNull('last_login_at')
                );
            }
        });

        $period = $request->period;
        $query->when($period && $period !== 'custom', function ($q) use ($period) {
            $q->where('created_at', '>=', match ($period) {
                'day'   => now()->subDay(),
                'week'  => now()->subWeek(),
                'month' => now()->subMonth(),
                'year'  => now()->subYear(),
                default => now()->subYear(),
            });
        });

        $query->when($period === 'custom' && $request->filled('start_date') && $request->filled('end_date'), fn ($q) =>
            $q->whereBetween('created_at', [$request->start_date, $request->end_date])
        );

        $users = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $countries = User::distinct()->orderBy('country')->pluck('country')->filter()->values();
        $cities    = User::distinct()->orderBy('city')->pluck('city')->filter()->values();

        return view('users.index', compact('users', 'countries', 'cities'));
    }
}
```

- [ ] **Step 4: Update routes/web.php**

Replace `examples/swift-lens-4829/routes/web.php`:

```php
<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [UserController::class, 'index'])->name('users.index');
```

- [ ] **Step 5: Run tests to verify they pass**

```powershell
php artisan test --filter=UserDirectoryTest
```

Expected: PASS all tests.

- [ ] **Step 6: Commit**

```bash
git add examples/swift-lens-4829/app/Http/Controllers/UserController.php examples/swift-lens-4829/routes/web.php
git commit -m "feat: add UserController with filter logic and routes"
```

---

## Task 7: Layout and navigation

**Files:**
- Modify: `examples/swift-lens-4829/resources/views/layouts/app.blade.php`
- Modify: `examples/swift-lens-4829/resources/views/layouts/navigation.blade.php`

- [ ] **Step 1: Simplify navigation**

Replace `examples/swift-lens-4829/resources/views/layouts/navigation.blade.php`:

```blade
<nav class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <span class="text-xl font-semibold text-gray-800">Swift Lens</span>
                <span class="ml-3 text-sm text-gray-500">User Directory</span>
            </div>
        </div>
    </div>
</nav>
```

- [ ] **Step 2: Commit**

```bash
git add examples/swift-lens-4829/resources/views/layouts/
git commit -m "feat: simplify navigation for swift-lens-4829"
```

---

## Task 8: Users index view

**Files:**
- Create: `examples/swift-lens-4829/resources/views/users/index.blade.php`

- [ ] **Step 1: Create the view**

Create `examples/swift-lens-4829/resources/views/users/index.blade.php`:

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            User Directory
            <span class="ml-2 text-sm font-normal text-gray-500">({{ $users->total() }} results)</span>
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex gap-6">

                {{-- FILTER SIDEBAR --}}
                <aside class="w-64 flex-shrink-0">
                    <div class="bg-white rounded-lg shadow p-5">
                        <h3 class="font-semibold text-gray-700 mb-4">Filters</h3>
                        <form method="GET" action="/" x-data="{ period: '{{ request('period') }}' }">

                            {{-- Keyword --}}
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Keyword</label>
                                <input type="text" name="keyword" value="{{ request('keyword') }}"
                                    placeholder="name, email, city..."
                                    class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            {{-- Country --}}
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Country</label>
                                <select name="country" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">Any</option>
                                    @foreach ($countries as $c)
                                        <option value="{{ $c }}" @selected(request('country') === $c)>{{ $c }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- City --}}
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">City</label>
                                <select name="city" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">Any</option>
                                    @foreach ($cities as $c)
                                        <option value="{{ $c }}" @selected(request('city') === $c)>{{ $c }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Device --}}
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Device</label>
                                <select name="device_type" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">Any</option>
                                    @foreach (['web', 'mobile', 'tablet'] as $d)
                                        <option value="{{ $d }}" @selected(request('device_type') === $d)>{{ ucfirst($d) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Signup Source --}}
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Signup Source</label>
                                <select name="signup_source" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">Any</option>
                                    @foreach (['organic', 'referral', 'social', 'paid'] as $s)
                                        <option value="{{ $s }}" @selected(request('signup_source') === $s)>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Has Avatar --}}
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Avatar</label>
                                <select name="has_avatar" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">Any</option>
                                    <option value="1" @selected(request('has_avatar') === '1')>Has avatar</option>
                                    <option value="0" @selected(request('has_avatar') === '0')>No avatar</option>
                                </select>
                            </div>

                            {{-- Status --}}
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                                <select name="is_active" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">Any</option>
                                    <option value="1" @selected(request('is_active') === '1')>Active</option>
                                    <option value="0" @selected(request('is_active') === '0')>Inactive</option>
                                </select>
                            </div>

                            {{-- Period --}}
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Joined Period</label>
                                <select name="period" x-model="period" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">Any time</option>
                                    <option value="day">Today</option>
                                    <option value="week">This week</option>
                                    <option value="month">This month</option>
                                    <option value="year">This year</option>
                                    <option value="custom">Custom range</option>
                                </select>
                            </div>

                            {{-- Custom date range (Alpine x-show) --}}
                            <div x-show="period === 'custom'" x-cloak class="mb-4 space-y-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
                                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                                        class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
                                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                                        class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                </div>
                            </div>

                            {{-- Buttons --}}
                            <div class="flex gap-2 mt-5">
                                <button type="submit"
                                    class="flex-1 bg-indigo-600 text-white text-sm font-medium py-2 px-3 rounded-md hover:bg-indigo-700">
                                    Apply
                                </button>
                                <a href="/"
                                    class="flex-1 text-center bg-gray-100 text-gray-700 text-sm font-medium py-2 px-3 rounded-md hover:bg-gray-200">
                                    Reset
                                </a>
                            </div>

                        </form>
                    </div>
                </aside>

                {{-- RESULTS TABLE --}}
                <div class="flex-1 min-w-0">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($users as $user)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-gray-400">{{ $user->id }}</td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-3">
                                                    @if ($user->avatar)
                                                        <img src="{{ $user->avatar }}" alt="" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                                                    @else
                                                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                                                            <span class="text-xs text-gray-500">{{ strtoupper(substr($user->username, 0, 1)) }}</span>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="font-medium text-gray-900">{{ $user->username }}</div>
                                                        <div class="text-gray-400 text-xs">{{ $user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">
                                                {{ $user->city ?? '—' }}, {{ $user->country ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">{{ $user->device_type ?? '—' }}</td>
                                            <td class="px-4 py-3 text-gray-600">{{ $user->signup_source ?? '—' }}</td>
                                            <td class="px-4 py-3">
                                                @php
                                                    $isActive = $user->last_login_at && $user->last_login_at->gte(now()->subDays(30));
                                                @endphp
                                                @if ($isActive)
                                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                                @else
                                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $user->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-10 text-center text-gray-400">No users found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($users->hasPages())
                            <div class="px-4 py-3 border-t border-gray-200">
                                {{ $users->links() }}
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
```

- [ ] **Step 2: Add [x-cloak] CSS to app.css**

In `examples/swift-lens-4829/resources/css/app.css`, add:

```css
[x-cloak] { display: none !important; }
```

- [ ] **Step 3: Run all tests**

```powershell
php artisan test
```

Expected: all PASS.

- [ ] **Step 4: Commit**

```bash
git add examples/swift-lens-4829/resources/
git commit -m "feat: add users index view with filter sidebar and results table"
```

---

## Task 9: Install dependencies and seed

**Files:** none (runtime setup)

- [ ] **Step 1: Install PHP deps**

```powershell
cd D:\laravel13.x\examples\swift-lens-4829
composer install --no-interaction
```

- [ ] **Step 2: Install JS deps and build assets**

```powershell
npm install
npm run build
```

- [ ] **Step 3: Run migrations and seed**

```powershell
php artisan migrate --force
php artisan db:seed --force
```

Expected: 200 users in database.

- [ ] **Step 4: Run full test suite**

```powershell
php artisan test
```

Expected: all PASS.

- [ ] **Step 5: Commit**

```bash
git add examples/swift-lens-4829/
git commit -m "chore: install deps and seed swift-lens-4829 for demo"
```

---

## Task 10: Smoke test the running app

**Files:** none

- [ ] **Step 1: Start dev server**

```powershell
php artisan serve --port=8001
```

- [ ] **Step 2: Verify in browser**

Open `http://localhost:8001`. Confirm:
- 200 users listed, paginated 20/page
- Keyword filter works
- Country/city dropdowns populated
- Status badges show Active/Inactive
- Date range inputs appear when period = custom
- Reset link clears all filters

- [ ] **Step 3: Final commit**

```bash
git add examples/swift-lens-4829/
git commit -m "feat: swift-lens-4829 user directory complete"
```
