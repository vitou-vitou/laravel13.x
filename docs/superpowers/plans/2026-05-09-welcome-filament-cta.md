# Welcome Filament CTA Implementation Plan

> **For agentic workers:** Steps use checkbox (`- [ ]`) syntax. Spec: `docs/superpowers/specs/2026-05-09-welcome-filament-cta-design.md`.

**Goal:** Add primary “Open admin” link on welcome pages that routes to Filament panel dashboard when routes exist.

**Architecture:** Blade-only `<a>` styled as button; `route('filament.admin.pages.dashboard')` behind `Route::has`. Root welcome hides CTA when Filament not installed; example always registers panel so CTA shows. Footer animation step shifts to `animate-welcome-delay-3` so stagger stays ordered (hero → body → CTA → footer).

**Tech stack:** Laravel 13, Blade, Tailwind v4 tokens (`welcome-*`), Filament panel id `admin`.

---

## File map

| File | Action |
|------|--------|
| `resources/css/app.css` | Add `.animate-welcome-delay-3` |
| `examples/basic-laravel-filamentphp/resources/css/app.css` | Same |
| `resources/views/welcome.blade.php` | CTA block + footer class |
| `examples/basic-laravel-filamentphp/resources/views/welcome.blade.php` | Same |

---

### Task 1: CSS stagger for footer

**Files:** `resources/css/app.css`, `examples/basic-laravel-filamentphp/resources/css/app.css`

- [ ] **Step 1:** After `.animate-welcome-delay-2`, add:

```css
    .animate-welcome-delay-3 {
        animation: welcome-in 0.78s cubic-bezier(0.16, 1, 0.3, 1) 0.36s both;
    }
```

- [ ] **Step 2:** Commit optional (or batch with Task 2).

---

### Task 2: Welcome templates

**Files:** `resources/views/welcome.blade.php`, `examples/basic-laravel-filamentphp/resources/views/welcome.blade.php`

- [ ] **Step 1:** After the body `<p>...</p>` (Filament docs copy), before `</main>`, insert:

```blade
                @if (Route::has('filament.admin.pages.dashboard'))
                    <p
                        class="mt-8 max-w-[36rem] motion-reduce:animate-none motion-reduce:opacity-100 sm:mt-9 animate-welcome-delay-2"
                    >
                        <a
                            href="{{ route('filament.admin.pages.dashboard') }}"
                            class="inline-flex min-h-11 items-center justify-center rounded-md bg-welcome-accent px-6 py-3 text-sm font-semibold text-welcome-bg [-webkit-tap-highlight-color:transparent] transition-colors duration-200 ease-out hover:bg-welcome-accent-hover active:translate-y-px motion-reduce:transition-none focus:outline-none focus-visible:ring-2 focus-visible:ring-welcome-accent/50 focus-visible:ring-offset-2 focus-visible:ring-offset-welcome-bg"
                        >Open admin</a>
                    </p>
                @endif
```

- [ ] **Step 2:** Change footer class from `animate-welcome-delay-2` to `animate-welcome-delay-3`.

- [ ] **Step 3:** Manual check in example app: `php artisan serve`, open `/`, click “Open admin”, expect `/admin` or login.

- [ ] **Step 4:** Manual check root app: CTA absent (no Filament routes).

- [ ] **Step 5:** Run `npm run build` in each app root that ships assets (root + example) if CI expects built CSS.

- [ ] **Step 6:** Commit, e.g. `feat(welcome): add Open admin CTA for Filament panel`.
