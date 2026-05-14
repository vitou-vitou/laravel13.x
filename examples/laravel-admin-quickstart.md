# Laravel 12/13 — Install + /admin Access (x10 Speed)

## Min Spec

| Item | Min |
|---|---|
| PHP | 8.2+ (8.3 recommended, 8.5 use `--ignore-platform-reqs`) |
| Composer | 2.x |
| RAM | 256MB (512MB comfortable) |
| Disk | ~150MB |
| Database | SQLite (zero setup) |

## PHP Extensions Required

```
BCMath  Ctype  cURL  DOM  Fileinfo  JSON
Mbstring  OpenSSL  PCRE  PDO  Tokenizer  XML
```

---

## x10 Speed: Full Install + /admin (copy-paste)

```bash
composer create-project laravel/laravel myapp
cd myapp
php artisan migrate
composer require filament/filament:"^3.0" -W --ignore-platform-reqs
php artisan filament:install --panels
php artisan make:filament-user
php artisan serve
```

Open: http://localhost:8000/admin

Total time: ~2-3 min (warm Composer cache).

---

## Step-by-Step

### 1. Create project

```bash
composer create-project laravel/laravel myapp
cd myapp
```

### 2. Migrate (SQLite default — no DB server needed)

```bash
php artisan migrate
```

### 3. Install Filament panel

```bash
composer require filament/filament:"^3.0" -W --ignore-platform-reqs
php artisan filament:install --panels
```

Follow prompts. Creates `app/Providers/Filament/AdminPanelProvider.php`.

> **PHP 8.5 note:** Filament `^3.0` hasn't declared 8.5 support yet. Use `--ignore-platform-reqs` to bypass. If still fails, try `filament/filament:"^4.0"`.

### 4. Create admin user

```bash
php artisan make:filament-user
```

Enter name, email, password.

### 5. Serve

```bash
php artisan serve
```

### 6. Access

http://localhost:8000/admin — login with credentials from step 4.

---

## Allow User to Access /admin

In `app/Models/User.php`:

```php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return true; // lock down: $this->is_admin === true
    }
}
```

---

## Speed Tips

- Warm Composer cache = repeat installs ~20s vs ~2min cold.
- SQLite default (Laravel 11+) — skip MySQL/Postgres setup entirely.
- Skip `npm install` if no frontend needed.
