# 03 — Project Scaffolding

> The first 30 minutes of a new project shape the next 3 years. Senior devs scaffold deliberately.

---

## The "What Are We Building?" Decision Tree

Before `composer create-project`, answer these:

| Question | Impact |
|----------|--------|
| API or full-stack? | Determines starter kit |
| Single-tenant or multi-tenant? | Determines auth & architecture |
| One developer or team? | Determines tooling strictness |
| MVP or production? | Determines optimization upfront |
| Internal or public? | Determines monitoring & compliance |

---

## Decision Matrix: Which Starter

| Project Type | Starter | Why |
|--------------|---------|-----|
| REST API for mobile/SPA | None (bare Laravel + Sanctum) | No frontend bloat |
| Simple web app, Blade | Breeze (Blade) | Lightest, easiest |
| SPA (Vue/React) | Breeze + Inertia | One backend, one frontend, no API layer |
| SaaS with teams | Jetstream | Teams, 2FA, API tokens built in |
| SaaS with billing | Jetstream + Spark | Stripe/Paddle integration |
| Internal admin tool | Filament | 80% of admin is done in 1 hour |
| Marketing site + admin | Statamic | CMS without WordPress pain |

---

## The Senior's Init Sequence (Full-Stack SPA)

```bash
# 1. Create
composer create-project laravel/laravel my-saas --prefer-dist
cd my-saas

# 2. Git first, always
git init
git add -A
git commit -m "chore: initial laravel install"
gh repo create my-saas --private --source=. --push

# 3. Local env
cp .env.example .env
php artisan key:generate

# 4. Sail with full stack
composer require laravel/sail --dev
php artisan sail:install --with=pgsql,redis,mailpit,minio

# 5. Frontend (Breeze + Vue + TS)
composer require laravel/breeze --dev
php artisan breeze:install vue --typescript --pest

# 6. Dev tools (one shot)
composer require --dev \
    laravel/pint \
    larastan/larastan \
    barryvdh/laravel-debugbar \
    laravel/telescope \
    nunomaduro/collision \
    spatie/laravel-ignition

# 7. Production essentials
composer require \
    spatie/laravel-permission \
    spatie/laravel-medialibrary \
    spatie/laravel-activitylog \
    laravel/horizon \
    sentry/sentry-laravel

# 8. Install Telescope (dev only)
php artisan telescope:install --hidden

# 9. Bring it up
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

Done. Visit `http://localhost`. You have:
- Working auth (login/register/forgot-password)
- TypeScript-powered Vue 3 SPA
- Tailwind v4 styling
- Roles & permissions ready
- File upload to S3/Minio ready
- Activity logging ready
- Queue worker via Horizon
- Sentry error tracking ready
- Pest testing
- Static analysis (Larastan)
- Code formatting (Pint)

This is **the senior's day-1 scaffold**.

---

## Project Structure (Senior Convention)

```
app/
├── Actions/              # Single-purpose action classes
│   └── User/
│       ├── CreateUser.php
│       ├── UpdateUser.php
│       └── DeleteUser.php
├── Http/
│   ├── Controllers/      # Thin: validate, dispatch, respond
│   ├── Middleware/
│   ├── Requests/         # FormRequest validation
│   └── Resources/        # API response shaping
├── Models/               # Eloquent only, no business logic
├── Services/             # Cross-cutting domain logic
│   ├── Billing/
│   ├── Notifications/
│   └── Reports/
├── Jobs/                 # Background work
├── Events/
├── Listeners/
├── Mail/
├── Notifications/
├── Policies/             # Authorization
├── Providers/
└── Support/              # Helpers, traits, value objects
    ├── Enums/
    ├── ValueObjects/
    └── Helpers/

resources/
├── js/
│   ├── Components/       # Reusable UI
│   ├── Layouts/
│   ├── Pages/            # Inertia pages
│   ├── Composables/      # Vue composables
│   ├── Stores/           # Pinia stores
│   ├── Types/            # TypeScript types
│   └── app.ts
├── css/
└── views/                # Blade emails, PDFs

tests/
├── Feature/              # Most tests live here
├── Unit/                 # For pure logic only
└── Browser/              # Dusk (if needed)
```

---

## The Naming Convention Bible

| Thing | Pattern | Example |
|-------|---------|---------|
| Controller | `{Resource}Controller` | `PostController` |
| Action | `{Verb}{Noun}` | `CreatePost`, `PublishPost` |
| Model | Singular noun | `Post`, `User` |
| Migration | `create_{table}_table` | `create_posts_table` |
| Job | `{Verb}{Noun}Job` or just `{Verb}{Noun}` | `SendWelcomeEmail` |
| Event | Past tense | `PostPublished` |
| Listener | `{Verb}{Event}` | `NotifySubscribersOfPublishedPost` |
| Policy | `{Model}Policy` | `PostPolicy` |
| Request | `{Verb}{Resource}Request` | `StorePostRequest`, `UpdatePostRequest` |
| Resource | `{Model}Resource` | `PostResource` |
| Service | `{Domain}Service` | `BillingService`, `ReportingService` |
| Enum | `{Domain}{Type}` | `OrderStatus`, `UserRole` |
| Trait | `{Adjective}` | `HasSlug`, `Searchable` |
| Scope | Adjective | `published`, `active` |

---

## `composer.json` Scripts (Senior Standard)

```json
{
  "scripts": {
    "post-autoload-dump": [
      "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
      "@php artisan package:discover --ansi"
    ],
    "post-update-cmd": [
      "@php artisan vendor:publish --tag=laravel-assets --ansi --force"
    ],
    "post-root-package-install": [
      "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
    ],
    "post-create-project-cmd": [
      "@php artisan key:generate --ansi",
      "@php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\"",
      "@php artisan migrate --graceful --ansi"
    ],
    "lint": "pint",
    "lint:test": "pint --test",
    "stan": "phpstan analyse --memory-limit=2G",
    "test": "pest",
    "test:parallel": "pest --parallel",
    "test:coverage": "pest --coverage --min=80",
    "ci": [
      "@lint:test",
      "@stan",
      "@test:parallel"
    ]
  }
}
```

Now: `composer ci` runs everything CI runs. Locally before pushing. Saves rebuild loops.

---

## `phpstan.neon` (Larastan Config)

```yaml
includes:
    - vendor/larastan/larastan/extension.neon

parameters:
    paths:
        - app/
        - config/
        - database/
        - routes/
        - tests/

    level: 6

    ignoreErrors:
        # Add specific exceptions only

    excludePaths:
        - app/Console/Kernel.php
```

Level 6 is the senior's baseline. Level 8 is for libraries. Level 9 is masochism.

---

## `pint.json` (Code Style)

```json
{
    "preset": "laravel",
    "rules": {
        "declare_strict_types": true,
        "ordered_imports": {
            "sort_algorithm": "alpha"
        },
        "no_unused_imports": true,
        "single_quote": true,
        "global_namespace_import": {
            "import_classes": true,
            "import_constants": true,
            "import_functions": true
        }
    }
}
```

Forces strict types. Cleans imports. Single quotes (PHP convention).

---

## `phpunit.xml` (Senior Tweaks)

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit ...>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_STORE" value="array"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="TELESCOPE_ENABLED" value="false"/>
    </php>
    <coverage>
        <report>
            <html outputDirectory="coverage"/>
        </report>
    </coverage>
</phpunit>
```

In-memory SQLite + low bcrypt = test suite 5× faster.

---

## `.gitignore` Senior Additions

```
/node_modules
/public/build
/public/hot
/public/storage
/storage/*.key
/storage/pail
/vendor

.env
.env.backup
.env.production

.phpactor.json
.phpunit.cache
.phpunit.result.cache
.idea
.vscode
.fleet
.zed

# Sail
docker-compose.override.yml

# Editor
*.swp
*.swo
.DS_Store

# Test artifacts
/coverage
.phpstan
.pint
```

---

## `.editorconfig` (Cross-Editor Consistency)

```ini
root = true

[*]
charset = utf-8
end_of_line = lf
indent_size = 4
indent_style = space
insert_final_newline = true
trim_trailing_whitespace = true

[*.md]
trim_trailing_whitespace = false

[*.{yml,yaml,js,ts,vue,json}]
indent_size = 2

[*.blade.php]
indent_size = 4
```

---

## Authorization First (Spatie Permission)

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

`app/Models/User.php`:
```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    // ...
}
```

Seed roles day 1:
```php
// database/seeders/RoleSeeder.php
Role::create(['name' => 'super-admin']);
Role::create(['name' => 'admin']);
Role::create(['name' => 'editor']);
Role::create(['name' => 'subscriber']);

Permission::create(['name' => 'manage-users']);
Permission::create(['name' => 'manage-posts']);
// ...
```

You will need this in week 2. Set it up week 1.

---

## Logging Channels (Production-Ready)

`config/logging.php`:
```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => env('LOG_STACK', 'daily,sentry'),
    ],

    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
    ],

    'sentry' => [
        'driver' => 'sentry',
        'level' => env('LOG_LEVEL', 'error'),
        'bubble' => true,
    ],

    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'level' => 'critical',
    ],
],
```

In production: errors go to Sentry. Critical alerts go to Slack. All logs rotate daily.

---

## Health Check Endpoint (Render Needs This)

`routes/web.php`:
```php
Route::get('/healthz', function () {
    try {
        DB::select('SELECT 1');
        Cache::store('redis')->put('healthz', true, 1);
        return response()->json([
            'status' => 'ok',
            'time' => now()->toIso8601String(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'error' => $e->getMessage(),
        ], 503);
    }
});
```

Render uses this to determine if your app is alive. Without it, you can't enable proper auto-restart.

---

## CI Config (`.github/workflows/ci.yml`)

```yaml
name: CI

on:
  push:
    branches: [main, develop]
  pull_request:

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      pgsql:
        image: postgres:16
        env:
          POSTGRES_PASSWORD: password
          POSTGRES_DB: testing
        ports: ['5432:5432']
        options: --health-cmd pg_isready --health-interval 10s --health-timeout 5s --health-retries 5
      redis:
        image: redis:7-alpine
        ports: ['6379:6379']

    steps:
      - uses: actions/checkout@v4

      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: pdo, pdo_pgsql, redis, gd, zip, bcmath
          coverage: pcov

      - uses: actions/setup-node@v4
        with: { node-version: '22', cache: 'npm' }

      - run: composer install --prefer-dist --no-progress --no-interaction
      - run: npm ci
      - run: cp .env.example .env
      - run: php artisan key:generate
      - run: npm run build

      - name: Lint
        run: ./vendor/bin/pint --test

      - name: Static analysis
        run: ./vendor/bin/phpstan analyse --memory-limit=2G

      - name: Tests
        env:
          DB_CONNECTION: pgsql
          DB_HOST: localhost
          DB_USERNAME: postgres
          DB_PASSWORD: password
          DB_DATABASE: testing
          REDIS_HOST: localhost
        run: ./vendor/bin/pest --parallel --coverage --min=70
```

This catches 90% of bugs before they hit main.

---

## The First Commit Discipline

```bash
git add -A
git commit -m "chore: initial scaffold

- Laravel 13 with PHP 8.4
- Breeze + Vue + TypeScript
- Sail with pgsql/redis/mailpit/minio
- Pest + Larastan + Pint
- Sentry, Spatie Permission, Media Library
- Telescope (dev), Horizon (queues)
- CI via GitHub Actions"

git push -u origin main
```

This commit is your North Star. Every new project starts here. Your future self will thank you.

---

## What NOT to Add Day 1

Resist:
- Auth0/Clerk (use Breeze, switch later if needed)
- GraphQL (use Inertia/REST, switch only if forced)
- Microservices (you don't have the load)
- Custom container abstractions (use Laravel's)
- Premature DDD aggregates (use Models)
- ElasticSearch (use Postgres FTS or Scout)
- Custom logger (use stack channel)
- Custom queue driver (use Redis)

Add these only when:
1. The current solution measurably fails
2. The replacement is researched
3. The team agrees
4. The migration plan is written

---

## The Scaffold Receipt

After scaffolding, you should be able to demo in 5 minutes:
1. ✅ Visit homepage
2. ✅ Register a user
3. ✅ Login
4. ✅ View dashboard
5. ✅ Upload a file (goes to Minio/S3)
6. ✅ Assign a role
7. ✅ Dispatch a job (lands in Horizon)
8. ✅ Trigger an error (lands in Sentry/Telescope)
9. ✅ Run tests, all green
10. ✅ Push to CI, all green
11. ✅ Open Render preview (if connected)

If any of these doesn't work, fix it before writing feature code. **First impressions matter to your future self.**
