# Laravel Workflow — PHP 8.3 Upgrade + Dependencies

## Problem

Project requires PHP 8.3+ but Herd was using PHP 8.2.31. Composer install fails:

```
Root composer.json requires php ^8.3 but your php version (8.2.31) does not satisfy that requirement
```

---

## Solution: 3 Steps

### 1. Switch PHP version

**Important:** Command syntax is `herd use <VERSION>` (not `herd use php <VERSION>`)

```powershell
herd use 8.3
```

Output:
```
Using new version: 8.3
PHP 8.3 is not installed. Installing now, please wait...
PHP 8.3 is now installed!
Herd is now using 8.3.
```

> Herd automatically installs missing PHP versions.

---

### 2. Restart terminal

Terminal shells cache PATH. Old PHP still visible:

```powershell
php -v
# Still shows PHP 8.2.31
```

**Solution:** Close PowerShell and open new terminal window.

Verify new version:
```powershell
php -v
# Now shows PHP 8.3.31
```

---

### 3. Run composer install

Fresh terminal with updated PATH:

```powershell
composer install
```

Output timeline:
1. **Loading repositories** — reads composer.json requirements
2. **Updating dependencies** — resolves 161 packages
3. **Lock file operations** — creates composer.lock
4. **Downloading packages** — ~10-30 seconds (internet speed dependent)
5. **Extracting archives** — unpacks vendor/
6. **Autoload generation** — optimizes class loading
7. **Discovering packages** — registers Laravel service providers
8. **Filament upgrade** — publishes admin UI assets
9. **Done** — 105 packages offer funding options (ignore)

> Full install: ~60-120 seconds depending on machine + internet.

---

## After Install

Project ready. Verify:

```powershell
php artisan --version
# Laravel Framework 13.9.0
```

Run migrations + seeders:
```powershell
php artisan migrate --seed
```

Start dev server:
```powershell
herd open myappadmin.test
```

> Served automatically via Herd nginx. No `php artisan serve` needed.

---

## If Issues Persist

### PHP still wrong version
```powershell
herd use 8.3
composer clear-cache
# Close ALL PowerShell windows
# Open fresh PowerShell
php -v
composer install
```

### Composer lock issues
```powershell
rm composer.lock
composer install
```

### Vendor corruption
```powershell
rm -r vendor
composer clear-cache
composer install
```

---

## Exit Code Reference

| Exit Code | Meaning | Fix |
|-----------|---------|-----|
| 0 | Success | None |
| 2 | Dependency conflict | Run `composer update` (updates lock) or `composer require` to fix versions |
| 127 | Command not found | PHP/composer not in PATH — verify `php -v` works |

---

## Performance Notes

**First install:** ~2-5 minutes (downloads 161 packages)  
**Subsequent installs:** ~30-60 seconds (uses composer cache)

Clear cache when switching PHP versions:
```powershell
composer clear-cache
```

---

## What Gets Installed

- **Laravel Framework 13.9.0** — core framework
- **Filament 5.6.3** — admin panel UI (pre-configured)
- **Pest 4.7.0** — testing framework
- **Livewire 4.3.0** — reactive components
- **160 dependencies** — symfony, doctrine, guzzle, etc.

Total size: ~100-150 MB in vendor/

---

## Sources

- [Herd Documentation](https://herd.laravel.com)
- [Composer Documentation](https://getcomposer.org/doc/)
- [Laravel Installation](https://laravel.com/docs/13.x#installation)
