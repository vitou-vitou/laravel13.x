# Singapore Laravel example (`examples/my-sg-laravel`) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create runnable Laravel 13 app under `examples/my-sg-laravel/` with Breeze (Blade), `en` + `zh_CN` locale switching, SGD formatting demo, dismissible cookie consent + placeholder PDPA page, and a “Laravel development services” marketing page (original copy; inspired by `services__laravel-development.md`, not a site clone).

**Architecture:** Session-backed locale (`App::setLocale`) via middleware reading `session('locale')`; single layout wraps public pages + `<x-cookie-banner>`; `GET /locale/{locale}` validates allowed locales then redirects back. No Filament in v1. No lead-capture form in v1 (spec optional — shipped as skipped). SQLite for dev per `.env.example`.

**Tech stack:** PHP ^8.3, Laravel ^13.0, Laravel Breeze (Blade), Tailwind (via Breeze), PHPUnit feature tests.

**Spec:** `docs/superpowers/specs/2026-05-10-my-sg-laravel-design.md`

---

## File map (all under `examples/my-sg-laravel/` unless noted)

| Path | Role |
|------|------|
| `composer.json`, `package.json` | Scaffold + Breeze deps |
| `.env.example` | `APP_LOCALE=en`, `APP_FALLBACK_LOCALE=en`, `DB_CONNECTION=sqlite`, commented MySQL prod |
| `database/database.sqlite` | Empty file created locally; **do not commit** — `.gitignore` already ignores `*.sqlite` |
| `bootstrap/app.php` | Append `SetLocale` to `web` middleware group |
| `app/Http/Middleware/SetLocale.php` | Apply session locale |
| `app/Http/Controllers/LocaleController.php` | Switch locale + redirect back |
| `app/Http/Controllers/CookieConsentController.php` | Set consent cookie, redirect back |
| `routes/web.php` | Home, services page, privacy, locale, cookie consent |
| `lang/en.json`, `lang/zh_CN.json` | UI strings |
| `config/app.php` | `supported_locales` array (custom key — add explicitly) |
| `resources/views/layouts/guest.blade.php` | Extend Breeze guest; banner + locale switcher — **or** new `layouts/marketing.blade.php` if cleaner |
| `resources/views/components/cookie-banner.blade.php` | Dismiss UI |
| `resources/views/home.blade.php` | Welcome / positioning |
| `resources/views/services/laravel.blade.php` | Service narrative |
| `resources/views/privacy.blade.php` | PDPA placeholder |
| `tests/Feature/LocaleAndPagesTest.php` | HTTP smoke + locale assertion |
| `README.md` | Setup commands |

---

### Task 1: Scaffold Laravel 13 application

**Files:** Create entire tree via Composer at `examples/my-sg-laravel/`

- [ ] **Step 1:** From repository root (`laravel13.x`), run:

```bash
composer create-project laravel/laravel examples/my-sg-laravel "^13.0"
```

Expected: directory `examples/my-sg-laravel` with `artisan`, `composer.json` requiring `laravel/framework: ^13.0`.

- [ ] **Step 2:** Enter directory and verify PHP version:

```bash
cd examples/my-sg-laravel && php -v
```

Expected: PHP 8.3.x or 8.4.x.

- [ ] **Step 3:** Copy env and generate key:

```bash
cp .env.example .env && php artisan key:generate
```

- [ ] **Step 4:** Commit scaffold:

```bash
git add examples/my-sg-laravel
git commit -m "chore(examples): scaffold my-sg-laravel Laravel 13 app"
```

---

### Task 2: SQLite + documented MySQL in `.env.example`

**Files:** `examples/my-sg-laravel/.env.example`

- [ ] **Step 1:** Edit `.env.example` — ensure block reads like (merge with existing keys; do not delete unrelated keys):

```dotenv
APP_LOCALE=en
APP_FALLBACK_LOCALE=en

DB_CONNECTION=sqlite
# DB_DATABASE is not needed when using sqlite — Laravel uses database/database.sqlite

# Production example (uncomment and fill):
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=my_sg_app
# DB_USERNAME=root
# DB_PASSWORD=
```

- [ ] **Step 2:** Local `.env` (developer machine only): set same SQLite vars; touch database:

```bash
touch database/database.sqlite
php artisan migrate
```

Expected: migrations succeed (users table etc.).

- [ ] **Step 3:** Commit:

```bash
git add examples/my-sg-laravel/.env.example
git commit -m "chore(my-sg-laravel): default SQLite + prod MySQL hints"
```

---

### Task 3: Install Laravel Breeze (Blade)

**Files:** `composer.json`, `composer.lock`, `package.json`, `routes/auth.php`, `resources/views/*` (Breeze generates)

- [ ] **Step 1:**

```bash
cd examples/my-sg-laravel
composer require laravel/breeze --dev
php artisan breeze:install blade
```

Choose **Blade** when prompted if installer asks stack.

- [ ] **Step 2:** Install JS deps and build:

```bash
npm install
npm run build
```

- [ ] **Step 3:** Run migrations if not already:

```bash
php artisan migrate
```

- [ ] **Step 4:** Commit:

```bash
git add examples/my-sg-laravel
git commit -m "feat(my-sg-laravel): add Breeze blade stack"
```

---

### Task 4: Supported locales + SetLocale middleware

**Files:**  
- Create: `app/Http/Middleware/SetLocale.php`  
- Modify: `bootstrap/app.php`  
- Modify: `config/app.php`

- [ ] **Step 1:** Add to `config/app.php` after `'faker_locale' => ...` (or nearby):

```php
    'supported_locales' => ['en', 'zh_CN'],
```

- [ ] **Step 2:** Create `app/Http/Middleware/SetLocale.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = config('app.supported_locales', ['en']);

        $locale = $request->session()->get('locale');

        if (is_string($locale) && in_array($locale, $allowed, true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
```

- [ ] **Step 3:** Register middleware in `bootstrap/app.php` inside `withMiddleware`:

```php
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
```

Ensure `use Illuminate\Foundation\Configuration\Middleware;` stays present.

- [ ] **Step 4:** Commit:

```bash
git add examples/my-sg-laravel/app/Http/Middleware/SetLocale.php examples/my-sg-laravel/bootstrap/app.php examples/my-sg-laravel/config/app.php
git commit -m "feat(my-sg-laravel): session locale middleware"
```

---

### Task 5: Locale + cookie consent controllers and routes

**Files:**  
- Create: `app/Http/Controllers/LocaleController.php`  
- Create: `app/Http/Controllers/CookieConsentController.php`  
- Modify: `routes/web.php`

- [ ] **Step 1:** `app/Http/Controllers/LocaleController.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request, string $locale): RedirectResponse
    {
        $allowed = config('app.supported_locales', ['en']);

        if (! in_array($locale, $allowed, true)) {
            abort(404);
        }

        $request->session()->put('locale', $locale);

        return redirect()->back();
    }
}
```

- [ ] **Step 2:** `app/Http/Controllers/CookieConsentController.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CookieConsentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        return redirect()
            ->back()
            ->cookie(
                'cookie_consent',
                '1',
                60 * 24 * 365,
                '/',
                null,
                config('session.secure'),
                true,
                false,
                config('session.same_site') ?? 'lax'
            );
    }
}
```

- [ ] **Step 3:** Replace **non-auth** web routes section in `routes/web.php` (keep Breeze `require __DIR__.'/auth.php';` at bottom). Top of file after default use imports, ensure:

```php
use App\Http\Controllers\CookieConsentController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::view('/services/laravel', 'services.laravel')->name('services.laravel');

Route::view('/privacy', 'privacy')->name('privacy');

Route::get('/locale/{locale}', [LocaleController::class, 'update'])
    ->name('locale.switch');

Route::post('/cookie-consent', [CookieConsentController::class, 'store'])
    ->name('cookie.consent');
```

Keep existing `Route::get('/dashboard', ...)` from Breeze if present.

- [ ] **Step 4:** Manual check:

```bash
php artisan route:list --path=locale
php artisan route:list --path=cookie
```

Expected: `locale.switch` and `cookie.consent` registered.

- [ ] **Step 5:** Commit:

```bash
git add examples/my-sg-laravel/app/Http/Controllers/LocaleController.php examples/my-sg-laravel/app/Http/Controllers/CookieConsentController.php examples/my-sg-laravel/routes/web.php
git commit -m "feat(my-sg-laravel): locale switch + cookie consent routes"
```

---

### Task 6: Translation JSON files

**Files:** `lang/en.json`, `lang/zh_CN.json`

- [ ] **Step 1:** Create `lang/en.json`:

```json
{
    "nav.home": "Home",
    "nav.services_laravel": "Laravel services",
    "nav.privacy": "Privacy",
    "nav.locale.en": "English",
    "nav.locale.zh": "中文",
    "cookie.message": "We use cookies for essential site function and analytics placeholder. See our privacy notice.",
    "cookie.accept": "Accept",
    "cookie.dismiss": "Dismiss",
    "home.title": "Singapore-ready Laravel builds",
    "home.lead": "Performance, security, and shipping velocity — structured for teams who care about maintainability.",
    "home.cta_services": "View Laravel offering",
    "home.demo_price_label": "Sample invoice line (GST-inclusive style)",
    "services.title": "Laravel development for growth-stage products",
    "services.lead": "Use Laravel when you want conventions, ecosystem depth, and predictable upgrades — common choice for APIs, admin-heavy apps, and multi-role workflows.",
    "services.benefits_title": "What you get",
    "services.benefit.performance": "Queues, caching, Octane-ready patterns where needed",
    "services.benefit.security": "Sanctum/Passport-ready APIs; policies + gates baked into routing",
    "services.benefit.scale": "Horizontal-friendly session/cache drivers",
    "services.benefit.velocity": "Rapid iteration with Blade/Livewire ecosystems",
    "services.industries_title": "Typical domains",
    "services.industry.fintech": "Fintech & payments integrations",
    "services.industry.healthcare": "Healthcare workflows & audits",
    "services.industry.saas": "Multi-tenant SaaS patterns",
    "privacy.title": "Privacy notice (placeholder)",
    "privacy.body": "Replace this page with counsel-reviewed PDPA copy and your data controller details."
}
```

- [ ] **Step 2:** Create `lang/zh_CN.json` (Simplified Chinese — adjust wording freely if you prefer terms like “新加坡”):

```json
{
    "nav.home": "首页",
    "nav.services_laravel": "Laravel 服务",
    "nav.privacy": "隐私",
    "nav.locale.en": "English",
    "nav.locale.zh": "中文",
    "cookie.message": "本站使用 Cookie（示例文案）。详见隐私说明。",
    "cookie.accept": "接受",
    "cookie.dismiss": "关闭",
    "home.title": "面向新加坡场景的 Laravel 交付",
    "home.lead": "性能、安全、可维护发布节奏——适合重视长期演进的团队。",
    "home.cta_services": "查看 Laravel 方案",
    "home.demo_price_label": "示例账单行（含税展示）",
    "services.title": "面向增长的 Laravel 开发",
    "services.lead": "当你需要约定优于配置、丰富生态与可控升级路径时，Laravel 很适合 API、后台密集型与多角色协作场景。",
    "services.benefits_title": "交付重点",
    "services.benefit.performance": "队列、缓存与必要时 Octane 友好结构",
    "services.benefit.security": "Sanctum/Passport 友好；路由层落地 policies / gates",
    "services.benefit.scale": "会话/缓存驱动便于横向扩展",
    "services.benefit.velocity": "Blade / Livewire 生态加速迭代",
    "services.industries_title": "常见领域",
    "services.industry.fintech": "金融科技与支付集成",
    "services.industry.healthcare": "医疗流程与审计",
    "services.industry.saas": "多租户 SaaS 形态",
    "privacy.title": "隐私声明（占位）",
    "privacy.body": "请替换为经法务审核的 PDPA 文本与数据控制者信息。"
}
```

- [ ] **Step 3:** Commit:

```bash
git add examples/my-sg-laravel/lang/en.json examples/my-sg-laravel/lang/zh_CN.json
git commit -m "feat(my-sg-laravel): add en + zh_CN strings"
```

---

### Task 7: Marketing layout, cookie banner, pages

**Files:**  
- Create: `resources/views/layouts/marketing.blade.php`  
- Create: `resources/views/components/cookie-banner.blade.php`  
- Create: `resources/views/home.blade.php`  
- Create: `resources/views/services/laravel.blade.php`  
- Create: `resources/views/privacy.blade.php`

- [ ] **Step 1:** `resources/views/layouts/marketing.blade.php` — extend Breeze guest layout pattern; minimal Tailwind:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased">
    <header class="border-b border-zinc-200 bg-white">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-4 px-4 py-4">
            <a href="{{ route('home') }}" class="font-semibold">{{ config('app.name') }}</a>
            <nav class="flex flex-wrap items-center gap-4 text-sm">
                <a href="{{ route('home') }}" class="hover:underline">{{ __('nav.home') }}</a>
                <a href="{{ route('services.laravel') }}" class="hover:underline">{{ __('nav.services_laravel') }}</a>
                <a href="{{ route('privacy') }}" class="hover:underline">{{ __('nav.privacy') }}</a>
                <span class="text-zinc-400">|</span>
                <a href="{{ route('locale.switch', ['locale' => 'en']) }}" class="{{ app()->isLocale('en') ? 'font-semibold' : 'hover:underline' }}">{{ __('nav.locale.en') }}</a>
                <a href="{{ route('locale.switch', ['locale' => 'zh_CN']) }}" class="{{ app()->isLocale('zh_CN') ? 'font-semibold' : 'hover:underline' }}">{{ __('nav.locale.zh') }}</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-10">
        @yield('content')
    </main>

    <x-cookie-banner />

    @if (Route::has('login'))
        <footer class="border-t border-zinc-200 bg-white py-6 text-center text-xs text-zinc-500">
            <a href="{{ route('login') }}" class="underline">{{ __('Login') }}</a>
            @if (Route::has('register'))
                <span class="mx-2">·</span>
                <a href="{{ route('register') }}" class="underline">{{ __('Register') }}</a>
            @endif
        </footer>
    @endif
</body>
</html>
```

Note: `__('Login')` relies on Breeze/lang files — if missing, replace with literal `Login` / wire `lang/en.json` keys later.

- [ ] **Step 2:** `resources/views/components/cookie-banner.blade.php`:

```blade
@php
    $consent = request()->cookie('cookie_consent');
@endphp

@if (! $consent)
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-zinc-200 bg-white p-4 shadow-lg">
        <div class="mx-auto flex max-w-5xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-zinc-700">
                {{ __('cookie.message') }}
                <a href="{{ route('privacy') }}" class="font-medium underline">{{ __('nav.privacy') }}</a>
            </p>
            <form method="POST" action="{{ route('cookie.consent') }}" class="flex shrink-0 gap-2">
                @csrf
                <button type="submit" class="rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800">
                    {{ __('cookie.accept') }}
                </button>
            </form>
        </div>
    </div>
@endif
```

- [ ] **Step 3:** `resources/views/home.blade.php` — SGD demo uses `Number` facade:

```blade
@extends('layouts.marketing')

@section('title', __('home.title'))

@section('content')
    <h1 class="text-3xl font-bold tracking-tight">{{ __('home.title') }}</h1>
    <p class="mt-4 max-w-2xl text-lg text-zinc-600">{{ __('home.lead') }}</p>

    <p class="mt-6 text-sm text-zinc-500">{{ __('home.demo_price_label') }}:
        <span class="font-mono text-base text-zinc-900">{{ \Illuminate\Support\Number::currency(1080, 'SGD', 'en_SG') }}</span>
    </p>

    <p class="mt-8">
        <a href="{{ route('services.laravel') }}" class="rounded-md bg-zinc-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-zinc-800">
            {{ __('home.cta_services') }}
        </a>
    </p>
@endsection
```

- [ ] **Step 4:** `resources/views/services/laravel.blade.php`:

```blade
@extends('layouts.marketing')

@section('title', __('services.title'))

@section('content')
    <h1 class="text-3xl font-bold">{{ __('services.title') }}</h1>
    <p class="mt-4 max-w-2xl text-zinc-600">{{ __('services.lead') }}</p>

    <h2 class="mt-10 text-xl font-semibold">{{ __('services.benefits_title') }}</h2>
    <ul class="mt-4 list-disc space-y-2 pl-6 text-zinc-700">
        <li>{{ __('services.benefit.performance') }}</li>
        <li>{{ __('services.benefit.security') }}</li>
        <li>{{ __('services.benefit.scale') }}</li>
        <li>{{ __('services.benefit.velocity') }}</li>
    </ul>

    <h2 class="mt-10 text-xl font-semibold">{{ __('services.industries_title') }}</h2>
    <ul class="mt-4 list-disc space-y-2 pl-6 text-zinc-700">
        <li>{{ __('services.industry.fintech') }}</li>
        <li>{{ __('services.industry.healthcare') }}</li>
        <li>{{ __('services.industry.saas') }}</li>
    </ul>
@endsection
```

- [ ] **Step 5:** `resources/views/privacy.blade.php`:

```blade
@extends('layouts.marketing')

@section('title', __('privacy.title'))

@section('content')
    <h1 class="text-3xl font-bold">{{ __('privacy.title') }}</h1>
    <p class="mt-6 max-w-2xl text-zinc-700">{{ __('privacy.body') }}</p>
@endsection
```

- [ ] **Step 6:** Run build + smoke:

```bash
npm run build
php artisan serve
```

Visit `/`, `/services/laravel`, `/privacy`, toggle locale links, submit cookie consent — banner disappears after POST.

- [ ] **Step 7:** Commit:

```bash
git add examples/my-sg-laravel/resources/views
git commit -m "feat(my-sg-laravel): marketing layout, pages, cookie banner"
```

---

### Task 8: README for `examples/my-sg-laravel`

**Files:** `examples/my-sg-laravel/README.md`

- [ ] **Step 1:** Replace default Laravel README top section with:

```markdown
# my-sg-laravel (Singapore-oriented Laravel 13 example)

## Setup

```bash
cd examples/my-sg-laravel
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
npm run build
php artisan serve
```

Open http://127.0.0.1:8000 — use header links for English / 中文 and `/services/laravel`.

## Locale

Session key `locale`: values `en` or `zh_CN`. Switched via `GET /locale/{locale}`.

## PDPA

`/privacy` is placeholder copy only — replace before production.

## Production DB

Switch `.env` from SQLite to MySQL using commented block in `.env.example`.
```

- [ ] **Step 2:** Commit:

```bash
git add examples/my-sg-laravel/README.md
git commit -m "docs(my-sg-laravel): setup + locale notes"
```

---

### Task 9: Feature tests

**Files:** Create `tests/Feature/LocaleAndPagesTest.php`

- [ ] **Step 1:** Create file:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleAndPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_ok(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(__('home.title', [], 'en'), false);
    }

    public function test_services_page_ok(): void
    {
        $response = $this->get('/services/laravel');

        $response->assertOk();
        $response->assertSee(__('services.title', [], 'en'), false);
    }

    public function test_locale_switch_sets_session_and_changes_locale(): void
    {
        $this->from('/')->get('/locale/zh_CN')->assertRedirect('/');

        $this->assertSame('zh_CN', session('locale'));

        app()->setLocale('zh_CN');
        $response = $this->withSession(['locale' => 'zh_CN'])->get('/');
        $response->assertOk();
        $response->assertSee(__('home.title'), false);
    }

    public function test_privacy_ok(): void
    {
        $this->get('/privacy')->assertOk();
    }

    public function test_cookie_consent_sets_cookie(): void
    {
        $response = $this->from('/')->post('/cookie-consent');

        $response->assertRedirect('/');
        $response->assertCookie('cookie_consent', '1');
    }
}
```

- [ ] **Step 2:** Fix test brittleness if `assertSee` with translation fails — use `app()->setLocale('en')` in `setUp()`:

```php
protected function setUp(): void
{
    parent::setUp();
    config(['app.locale' => 'en']);
    app()->setLocale('en');
}
```

Add to class body above tests.

- [ ] **Step 3:** Run:

```bash
cd examples/my-sg-laravel
php artisan test --filter=LocaleAndPagesTest
```

Expected: all pass.

- [ ] **Step 4:** Commit:

```bash
git add examples/my-sg-laravel/tests/Feature/LocaleAndPagesTest.php
git commit -m "test(my-sg-laravel): locale + public pages smoke tests"
```

---

## Spec coverage check

| Spec requirement | Task |
|------------------|------|
| Runnable app under `examples/my-sg-laravel/` | Task 1–3 |
| `en` + `zh_CN` | Tasks 4–6 |
| SGD formatting demo | Task 7 (`Number::currency`, `en_SG`) |
| Cookie banner + PDPA placeholder | Tasks 5–7 |
| Service narrative page | Task 7 |
| SQLite dev + MySQL doc | Task 2 |
| README | Task 8 |
| No external images required | Task 7 (no `<img>`) |
| Lead form optional skipped | Explicitly omitted |

---

Plan complete and saved to `docs/superpowers/plans/2026-05-10-my-sg-laravel.md`.

**Execution options:**

1. **Subagent-driven** — fresh subagent per task; review between tasks.
2. **Inline** — run tasks in this thread with checkpoints.

Which approach?
