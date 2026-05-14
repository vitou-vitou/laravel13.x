# Laravel 12/13 + Admin Quickstart
**PHP 8.2 | Windows 11 | Goal: reach /admin fast**

---

## 1. Create Laravel project

```powershell
composer create-project laravel/laravel myappadmin
cd myappadmin
```

## 2. Configure .env

```env
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite
```

> SQLite = zero setup. No MySQL needed for quickstart.

## 3. Run migrations

```powershell
php artisan migrate
```

## 4. Install Filament (admin panel)

```powershell
composer require filament/filament:"^3.0" -W
php artisan filament:install --panels
```

> Filament v3 officially supports PHP 8.1–8.3. PHP 8.2 is fully supported — no `--ignore-platform-reqs` needed.

## 5. Fix: allow user to access /admin

In `app/Models/User.php`:

```php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return true; // restrict later: $this->is_admin === true
    }
}
```

> Without this, authenticated users get 403 on `/admin`.

## 6. Create admin user

```powershell
php artisan make:filament-user
```

Enter name, email, password when prompted.

## 7. Serve

```powershell
php artisan serve
```

## 8. Access admin

```
http://localhost:8000/admin
```

Login with credentials from step 6.

---

## 9. Install Pest (optional — testing)

```powershell
composer require pestphp/pest pestphp/pest-plugin-laravel --dev -W
```

> `php artisan pest:install` may not exist. Create `tests/Pest.php` in next step.

## 10. Create tests/Pest.php

Create `tests/Pest.php`:

```php
<?php

use Tests\TestCase;

pest()->extend(TestCase::class)->in('Feature', 'Unit');
```

> Extends all tests with Laravel TestCase. Enables `$this->get()`, `$this->post()`, etc.

## 11. Update TestCase for database testing

In `tests/TestCase.php`, add `RefreshDatabase` trait:

```php
namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
}
```

> Migrates test database before each test. Required for User factory to work.

## 12. Create test

Requires step 5 (`canAccessPanel`) already applied.

Create `tests/Feature/AdminAccessTest.php`:

```php
<?php

it('redirects guest to login on /admin', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/admin/login');
});

it('can access /admin when authenticated as admin', function () {
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user)->get('/admin');

    $response->assertOk();
});
```

## 13. Run tests

```powershell
php artisan test --filter AdminAccess
```

Expected output (all pass):
```
PASS  Tests\Feature\AdminAccessTest
✓ it redirects guest to login on /admin
✓ it can access /admin when authenticated as admin
```

---

## Notes

- Default admin path: `/admin` (change in `app/Providers/Filament/AdminPanelProvider.php`)
- PHP 8.2: fully supported by Filament v3 (8.1–8.3) — no flags needed
- `php artisan pest:install` may not exist — step 10 creates `tests/Pest.php` manually
