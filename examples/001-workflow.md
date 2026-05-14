# Laravel Workflow — PHP Version + Claude Quick Access

---

## 1. Switch PHP Version in Herd

### Check current version

```powershell
php -v
```

Example output:
```
PHP 8.2.31 (cli) (built: Aug 13 2024 17:50:43) (NTS Visual C++ 16.13)
```

### Change PHP version

**A) Herd UI:**
1. Open **Herd** application (system tray)
2. Click **PHP version dropdown** (top right)
3. Select desired version (e.g., `8.3`)
4. Verify in terminal: `php -v`

**B) Terminal (alternative):**
```powershell
herd use php 8.3
```

> Verify change took effect — restart any open terminals after switching.

---

## 2. After PHP Version Change

### Clear composer cache + reinstall

```powershell
composer clear-cache
composer install
```

> Ensures packages match new PHP version requirements.

### Run migrations + seeders

```powershell
php artisan migrate --seed
```

> Database schema + test data ready.

---

## 3. Claude Quick Access — Read & Write Patterns

### Pattern: Ask Claude to read and write

Run command:
```powershell
claude "read config/app.php and write documentation about config options"
```

Or:
```powershell
claude "read routes/web.php and write endpoint summary in ENDPOINTS.md"
```

### Examples

**Generate docs from code:**
```powershell
claude "read app/Models/User.php and write API documentation"
```

**Analyze + fix:**
```powershell
claude "read app/Http/Controllers/OrderController.php and write improved version with better validation"
```

**Create test file:**
```powershell
claude "read app/Services/PaymentService.php and write comprehensive Pest tests"
```

**Compare + suggest:**
```powershell
claude "read database/migrations/*.php and write migration best practices guide"
```

### Workflow tip

1. **Read** — Claude extracts context from existing code
2. **Write** — Claude generates new files/changes based on that context
3. **Verify** — Check output, iterate if needed

> Use Claude for bulk documentation, tests, refactoring, or exploratory writing. Pair with regular terminal commands (`php artisan make:...`) for scaffolding.

---

## Full Setup Checklist

- [ ] PHP version correct (8.3+): `php -v`
- [ ] Composer installed: `composer --version`
- [ ] Dependencies fresh: `composer install`
- [ ] Database migrated: `php artisan migrate --seed`
- [ ] Dev server running: `herd open myappadmin.test`
- [ ] Tests passing: `php artisan test`

---

## Quick Reference

| Task | Command |
|------|---------|
| Check PHP | `php -v` |
| Switch PHP | Herd UI or `herd use php 8.3` |
| Fresh install | `composer clear-cache && composer install` |
| Run migrations | `php artisan migrate --seed` |
| Run tests | `php artisan test` |
| Watch tests | `php artisan test --watch` |
| Generate docs | `claude "read <file> and write <output>"` |
