# Laravel 11 — 10-layer HTTP architecture for CRUD

**Date:** 2026-09-27  
**Scope:** Laravel **11.x** request pipeline for resource CRUD, grounded in official docs (`docs` branch `11.x`), framework tag [`v11.56.1`](https://github.com/laravel/framework/tree/v11.56.1), app skeleton [`laravel/laravel` `11.x`](https://github.com/laravel/laravel/tree/11.x), and the most-installed first-party starter (Breeze). Companion to [`laravel-11-crud-primary-sources.md`](./laravel-11-crud-primary-sources.md) (CRUD action map — not overwritten).

**Verdict:** A Laravel 11 CRUD request does **not** traverse inventable DDD strata (Repository / Application Service / Domain). It traverses the official HTTP lifecycle: **front controller → Application → HTTP Kernel / bootstrappers → middleware → routing → controller → (optional) Form Request validation → Eloquent → database connection → response send**. The most popular *starter* by Packagist install volume is **Laravel Breeze**; it scaffolds auth + profile RU/D, not generic domain CRUD. Domain CRUD sits in the same ten layers once you add `Route::resource` + a resource controller + Eloquent model.

**Local workspace caveat:** `d:\laravel13.x` may run Laravel 13 locally. All framework citations below pin **upstream** `11.x` / tag `v11.56.1`, not local `vendor/`.

---

## 1. What “most popular” means (first-party metrics)

Measured **2026-09-27** from Packagist package JSON + GitHub API (first-party packages only).

| Artifact | Ownership | GitHub stars | Packagist total / monthly | Role |
|---|---|---:|---|---|
| [laravel/laravel](https://github.com/laravel/laravel) | Laravel | **85,019** | **65,140,029 / 674,022** | Default app skeleton (`11.x` branch exists; default branch today is `13.x`) |
| [laravel/framework](https://github.com/laravel/framework) | Laravel | **34,932** | **584,400,711 / 15,400,803** | Framework (tag `v11.56.1` = `Application::VERSION`) |
| [laravel/breeze](https://github.com/laravel/breeze) | Laravel | **3,059** | **41,956,210 / 2,217,847** | Minimal auth + profile starter |
| [laravel/jetstream](https://github.com/laravel/jetstream) | Laravel | **4,060** | **24,107,080 / 859,770** | Auth + 2FA + Sanctum + optional teams |

Sources: [packagist.org/packages/laravel/breeze.json](https://packagist.org/packages/laravel/breeze.json), […/jetstream.json](https://packagist.org/packages/laravel/jetstream.json), […/framework.json](https://packagist.org/packages/laravel/framework.json), […/laravel.json](https://packagist.org/packages/laravel/laravel.json); GitHub API `GET /repos/laravel/{laravel,framework,breeze,jetstream}`.

**Chosen “most popular web app / starter” for this note: Laravel Breeze**

| Criterion | Winner | Why |
|---|---|---|
| Packagist monthly / total downloads | **Breeze** | ~2.6× Jetstream monthly; ~1.7× total |
| GitHub stars (starters only) | Jetstream | Slight lead (4,060 vs 3,059) |
| Official 11.x docs positioning | **Breeze first** | “minimal… authentication”; Bootcamp walks newcomers through Breeze ([starter-kits.md `11.x`](https://github.com/laravel/docs/blob/11.x/starter-kits.md)) |

**Base stack for layers:** Breeze *publishes into* `laravel/laravel` 11.x. Layers 1–10 are framework + skeleton paths; Breeze only adds auth/profile controllers on top of the same pipeline.

**Limitations**

- Packagist totals span major versions (Breeze/Jetstream constraints historically allow multiple Laravel majors).
- `laravel/laravel` is a skeleton, not a domain CRUD product; stars measure the *framework app template*, not a shipped CRUD app.
- No official “most popular CRUD app” metric exists.

---

## 2. The 10 layers (request strata for CRUD)

Cut matches official lifecycle + the natural CRUD-specific split after the controller (validation → Eloquent → SQL → response). Docs overview: [Request Lifecycle (`11.x`)](https://github.com/laravel/docs/blob/11.x/lifecycle.md) · rendered [laravel.com/docs/11.x/lifecycle](https://laravel.com/docs/11.x/lifecycle).

```
Browser
  → 1 Front controller
  → 2 Application bootstrap / container
  → 3 HTTP Kernel + framework bootstrappers (providers)
  → 4 Middleware pipeline (global + route group)
  → 5 Routing (match + resource verbs)
  → 6 Controller dispatch (CRUD action)
  → 7 Form Request / validation (store / update)
  → 8 Eloquent / Query Builder
  → 9 Database connection (SQL)
  → 10 Response (View / Redirect / JSON) + send / terminate
```

---

### Layer 1 — Front controller (`public/index.php`)

**Role for CRUD:** Single HTTP entry for every C/R/U/D request. Loads Composer autoload, builds `Request`, hands off to the application. No domain logic.

**Primary sources**

| File / class | Link |
|---|---|
| App entry | [`laravel/laravel` `11.x` `public/index.php`](https://github.com/laravel/laravel/blob/11.x/public/index.php) |
| Docs “First Steps” | [lifecycle.md](https://github.com/laravel/docs/blob/11.x/lifecycle.md) |

**Flow:** Maintenance short-circuit → `vendor/autoload.php` → `require bootstrap/app.php` → `$app->handleRequest(Request::capture())`.

---

### Layer 2 — Application bootstrap / service container

**Role for CRUD:** Creates the Application (IoC container), registers routing paths (`routes/web.php`), middleware/exception configuration. Resource routes and CRUD controllers become reachable only after this configure/create step.

**Primary sources**

| File / class | Link |
|---|---|
| App bootstrap | [`bootstrap/app.php` (laravel/laravel 11.x)](https://github.com/laravel/laravel/blob/11.x/bootstrap/app.php) |
| `Application::configure` / builder | [`ApplicationBuilder.php` @ `v11.56.1`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Foundation/Configuration/ApplicationBuilder.php) |
| `Application` (VERSION `11.56.1`) | [`Application.php` @ `v11.56.1`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Foundation/Application.php) |
| App providers list | [`bootstrap/providers.php`](https://github.com/laravel/laravel/blob/11.x/bootstrap/providers.php) |

**Flow:** `Application::configure(…)->withRouting(web: …)->withMiddleware(…)->withExceptions(…)->create()` returns `$app`. Skeleton wires **web** routes only (no default `api.php` in 11 — optional later).

---

### Layer 3 — HTTP Kernel + framework bootstrappers

**Role for CRUD:** Central `Request` → `Response` box. Runs bootstrappers (env, config, exceptions, facades, **register + boot service providers** — database, validation, routing bindings, etc.) before any CRUD middleware or controller runs.

**Primary sources**

| File / class | Link |
|---|---|
| `Application::handleRequest` | [`Application.php` L1216–1223 @ `v11.56.1`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Foundation/Application.php#L1216-L1223) |
| HTTP Kernel | [`Http/Kernel.php` @ `v11.56.1`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Foundation/Http/Kernel.php) |
| Bootstrappers array | same file `$bootstrappers` (LoadEnvironmentVariables → … → RegisterProviders → BootProviders) |
| Docs | [lifecycle.md — HTTP / Console Kernels + Service Providers](https://github.com/laravel/docs/blob/11.x/lifecycle.md) |

**Flow:** `handleRequest` → `$kernel->handle($request)` → `sendRequestThroughRouter` → `bootstrap()` → `bootstrapWith($bootstrappers)`. Providers register DB / Eloquent / Validator so later CRUD layers can resolve them.

---

### Layer 4 — Middleware pipeline

**Role for CRUD:** Session, CSRF (web forms for create/update/delete), cookies, auth gates, throttling, and **implicit route-model binding** (`SubstituteBindings`) for `show` / `edit` / `update` / `destroy`.

**Primary sources**

| File / class | Link |
|---|---|
| Kernel pipeline | [`Kernel::sendRequestThroughRouter`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Foundation/Http/Kernel.php#L165-L177) |
| Default `web` / `api` groups | [`Configuration/Middleware.php` `getMiddlewareGroups`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Foundation/Configuration/Middleware.php#L481-L498) |
| Binding middleware | [`SubstituteBindings.php` @ `v11.56.1`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Routing/Middleware/SubstituteBindings.php) |
| Implicit binding | [`ImplicitRouteBinding.php` @ `v11.56.1`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Routing/ImplicitRouteBinding.php) |
| Docs | [middleware.md `11.x`](https://github.com/laravel/docs/blob/11.x/middleware.md), [lifecycle.md — Routing](https://github.com/laravel/docs/blob/11.x/lifecycle.md) |

**Default `web` group (relevant to Blade CRUD forms):** EncryptCookies → AddQueuedCookies → StartSession → ShareErrorsFromSession → **ValidateCsrfToken** → **SubstituteBindings**.

**CRUD notes**

| Action | Middleware interest |
|---|---|
| C/U/D (POST/PUT/PATCH/DELETE) | CSRF on `web`; often `auth` on resource routes |
| R (`show` / `edit`) | `SubstituteBindings` resolves `{photo}` → Eloquent model or 404 |
| API C/R/U/D | `api` group: throttle + SubstituteBindings (no session/CSRF by default) |

Breeze profile: routes wrapped in `middleware('auth')` ([breeze `2.x` stubs `web.php`](https://github.com/laravel/breeze/blob/2.x/stubs/default/routes/web.php)).

---

### Layer 5 — Routing (match + resource registration)

**Role for CRUD:** Maps HTTP verb + URI to controller action. `Route::resource` / `apiResource` register the seven (or five) CRUD endpoints via `ResourceRegistrar`.

**Primary sources**

| File / class | Link |
|---|---|
| `Router::dispatch` / `dispatchToRoute` / `runRouteWithinStack` | [`Router.php` @ `v11.56.1`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Routing/Router.php) |
| `Router::resource` / `apiResource` | same file |
| `ResourceRegistrar::$resourceDefaults` | [`ResourceRegistrar.php` L21 @ `v11.56.1`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Routing/ResourceRegistrar.php#L21) |
| Docs resource table | [controllers.md `11.x`](https://github.com/laravel/docs/blob/11.x/controllers.md) |
| Skeleton routes (no domain CRUD) | [`routes/web.php` laravel/laravel 11.x](https://github.com/laravel/laravel/blob/11.x/routes/web.php) |

**Canonical verbs** (`$resourceDefaults`): `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`. API omits HTML `create` / `edit`.

**Flow after match:** gather route middleware → Pipeline → `$route->run()` → prepare response ([`runRouteWithinStack`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Routing/Router.php)).

---

### Layer 6 — Controller dispatch (CRUD actions)

**Role for CRUD:** Application code that orchestrates each C/R/U/D method. Framework dispatcher resolves method dependencies (Form Requests, route models) and invokes the action.

**Primary sources**

| File / class | Link |
|---|---|
| `ControllerDispatcher::dispatch` | [`ControllerDispatcher.php` @ `v11.56.1`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Routing/ControllerDispatcher.php) |
| Framework base controller | [`Illuminate\Routing\Controller`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Routing/Controller.php) |
| App base (11.x empty abstract) | [`App\Http\Controllers\Controller` laravel/laravel 11.x](https://github.com/laravel/laravel/blob/11.x/app/Http/Controllers/Controller.php) |
| Resource stub with model + requests | [`controller.model.stub` @ `v11.56.1`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Routing/Console/stubs/controller.model.stub) |
| Breeze profile (RU/D example) | [`ProfileController` breeze `2.x`](https://github.com/laravel/breeze/blob/2.x/stubs/default/app/Http/Controllers/ProfileController.php) |

**CRUD mapping (app-authored bodies; stubs ship empty):**

| Method | Typical job |
|---|---|
| `index` / `show` | Read list / one model → view or JSON |
| `create` / `edit` | Return form views (no write) |
| `store` | Create after validation |
| `update` | Update bound model after validation |
| `destroy` | Delete bound model |

---

### Layer 7 — Form Request / validation

**Role for CRUD:** Authorize + validate input on **store** / **update** before Eloquent mass assignment. Optional but first-party recommended; stub type-hints Form Requests when generated with `--requests`.

**Primary sources**

| File / class | Link |
|---|---|
| `FormRequest` | [`FormRequest.php` @ `v11.56.1`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Foundation/Http/FormRequest.php) |
| `ValidatesWhenResolvedTrait` | [`ValidatesWhenResolvedTrait.php` @ `v11.56.1`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Validation/ValidatesWhenResolvedTrait.php) |
| Docs | [validation.md — Form Request Validation](https://github.com/laravel/docs/blob/11.x/validation.md) |
| Breeze profile update request usage | [`ProfileController::update`](https://github.com/laravel/breeze/blob/2.x/stubs/default/app/Http/Controllers/ProfileController.php) |

**Flow when controller type-hints a Form Request:** container resolves request → `validateResolved()` → `prepareForValidation` → `authorize` → validator from `rules()` → fail (exception) or `passedValidation` → controller uses `$request->validated()`.

Inline alternative (also first-party): `$request->validate([...])` inside the controller (common in Breeze auth controllers).

---

### Layer 8 — Eloquent / Query Builder

**Role for CRUD:** ORM API for create / read / update / delete. Controllers call these; they do not open SQL connections directly.

**Primary sources**

| Operation | API | Source |
|---|---|---|
| Create | `Model::create` / `Builder::create` → `save` | [`Builder::create` @ `v11.56.1`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Database/Eloquent/Builder.php#L1125-L1130) |
| Read | `find` / `findOrFail` / query | [`Builder::find`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Database/Eloquent/Builder.php#L471) |
| Update | `Model::update` / `fill` + `save` | [`Model.php` @ `v11.56.1`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Database/Eloquent/Model.php) |
| Delete | `delete` / `destroy` | same Model; soft deletes: [`SoftDeletes.php`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Database/Eloquent/SoftDeletes.php) |
| Docs | Inserting / Updating / Deleting | [eloquent.md `11.x`](https://github.com/laravel/docs/blob/11.x/eloquent.md) |

Mass assignment gated by `$fillable` / `$guarded` before `create` / `update` with request arrays.

**Breeze example:** `$request->user()->fill($request->validated()); …->save();` and `$user->delete()` in ProfileController.

---

### Layer 9 — Database connection / schema

**Role for CRUD:** Executes SQL produced by Eloquent/Query Builder (`select` / `insert` / `update` / `delete`). Schema/migrations define tables that models map to — not on the hot path of every request, but the persistence contract for CRUD.

**Primary sources**

| File / class | Link |
|---|---|
| `Connection::select` / `insert` / `update` / `delete` | [`Connection.php` @ `v11.56.1`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Database/Connection.php) |
| DB service provider (booted in layer 3) | framework `DatabaseServiceProvider` (registered via providers bootstrap) |
| Docs | [database.md `11.x`](https://github.com/laravel/docs/blob/11.x/database.md), [migrations.md `11.x`](https://github.com/laravel/docs/blob/11.x/migrations.md) |

---

### Layer 10 — Response (View / Redirect / JSON) + send / terminate

**Role for CRUD:** Controller return value becomes HTTP response (Blade view, redirect after store/update/destroy, or JSON for API). Travels back through middleware; Kernel returns to `handleRequest`, which **`send()`s** to the browser and runs **`terminate`**.

**Primary sources**

| File / class | Link |
|---|---|
| `Application::handleRequest` (`handle` → `send` → `terminate`) | [`Application.php` L1216–1223](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Foundation/Application.php#L1216-L1223) |
| `Router::prepareResponse` (via `runRouteWithinStack`) | [`Router.php` @ `v11.56.1`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Routing/Router.php) |
| `ResponseFactory` | [`ResponseFactory.php` @ `v11.56.1`](https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Routing/ResponseFactory.php) |
| Docs finishing | [lifecycle.md — Finishing Up](https://github.com/laravel/docs/blob/11.x/lifecycle.md) |

**Typical CRUD returns**

| Action | Common return |
|---|---|
| `index` / `show` / `create` / `edit` | `view(…)` or JSON resource |
| `store` / `update` / `destroy` | `redirect(…)` (web) or `response()->json(…)` / `204` (API) |

---

## 3. End-to-end CRUD walkthroughs

Assumes: Breeze-installed `laravel/laravel` 11.x app + app-authored `Route::resource('photos', PhotoController::class)` with Form Requests (same stack Breeze already uses for profile).

### 3.1 Create — `POST /photos` → `store`

| # | Layer | What happens |
|---:|---|---|
| 1 | Front controller | `public/index.php` captures POST, calls `handleRequest` |
| 2 | Application | Container already configured; web routes loaded |
| 3 | HTTP Kernel + bootstrappers | Providers boot; Kernel `handle` begins |
| 4 | Middleware | `web`: session + **CSRF** + …; optional `auth` |
| 5 | Routing | Match `photos.store` (`ResourceRegistrar` → POST `/photos`) |
| 6 | Controller | `PhotoController::store(StorePhotoRequest $request)` |
| 7 | Form Request | `validateResolved()` → rules / authorize → `validated()` |
| 8 | Eloquent | `Photo::create($validated)` → `Builder::create` → `save` |
| 9 | Database | `Connection::insert(…)` (via Eloquent) |
| 10 | Response | `redirect()->route('photos.show', $photo)` → middleware outward → `send()` → `terminate()` |

### 3.2 Update — `PUT/PATCH /photos/{photo}` → `update`

| # | Layer | What happens |
|---:|---|---|
| 1 | Front controller | Same entry |
| 2 | Application | Same container |
| 3 | HTTP Kernel + bootstrappers | Same bootstrap (once per process) |
| 4 | Middleware | CSRF + **`SubstituteBindings`** resolves `{photo}` → `Photo` model (or 404) |
| 5 | Routing | Match `photos.update` (`match(['PUT','PATCH'])`) |
| 6 | Controller | `update(UpdatePhotoRequest $request, Photo $photo)` |
| 7 | Form Request | Validate patch payload |
| 8 | Eloquent | `$photo->update($validated)` or `fill` + `save` |
| 9 | Database | `Connection::update(…)` |
| 10 | Response | Redirect / JSON → `send` / `terminate` |

**Breeze profile analogue (not `Route::resource`):** `PATCH /profile` → layers 1–5 (explicit route) → `ProfileController::update` → `ProfileUpdateRequest` → `User::fill` / `save` → redirect — same layers 6–10 pattern ([ProfileController](https://github.com/laravel/breeze/blob/2.x/stubs/default/app/Http/Controllers/ProfileController.php)).

---

## 4. What is NOT a framework layer

These appear in many blog “Laravel architectures” but are **not** strata in the official 11.x skeleton, lifecycle docs, or Breeze stubs:

| Myth layer | Status in primary sources |
|---|---|
| Repository pattern | Not in `laravel/laravel` 11.x, Breeze, or framework HTTP lifecycle docs |
| Application / Domain Service folder | Not required; Jetstream *optionally* uses action contracts for teams — app choice, not Kernel layers |
| DTO / Transformer as required pipeline stage | Optional packages / API Resources are helpers, not Kernel bootstrappers |
| “CRUD module” / admin generator | No first-party generic CRUD admin; `make:controller --resource` stubs are empty comments |
| Separate “ViewModel” layer | Blade/Inertia views are **Layer 10** return values, not a Kernel stage |

Official lifecycle names only: entry → Application → Kernel/bootstrappers/providers → middleware → router/controller → response send ([lifecycle.md](https://github.com/laravel/docs/blob/11.x/lifecycle.md)). Layers 7–9 in this note are the **CRUD-specific continuation inside/after the controller**, still owned by first-party Validation + Eloquent + Database components — not third-party architecture fashion.

---

## 5. Source index

### Docs (`11.x`)

- https://github.com/laravel/docs/blob/11.x/lifecycle.md
- https://github.com/laravel/docs/blob/11.x/controllers.md
- https://github.com/laravel/docs/blob/11.x/routing.md
- https://github.com/laravel/docs/blob/11.x/middleware.md
- https://github.com/laravel/docs/blob/11.x/validation.md
- https://github.com/laravel/docs/blob/11.x/eloquent.md
- https://github.com/laravel/docs/blob/11.x/database.md
- https://github.com/laravel/docs/blob/11.x/migrations.md
- https://github.com/laravel/docs/blob/11.x/starter-kits.md
- https://laravel.com/docs/11.x/lifecycle (rendered)

### Framework tag `v11.56.1`

- https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Foundation/Application.php
- https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Foundation/Http/Kernel.php
- https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Foundation/Configuration/ApplicationBuilder.php
- https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Foundation/Configuration/Middleware.php
- https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Foundation/Http/FormRequest.php
- https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Validation/ValidatesWhenResolvedTrait.php
- https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Routing/Router.php
- https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Routing/ResourceRegistrar.php
- https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Routing/ControllerDispatcher.php
- https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Routing/Middleware/SubstituteBindings.php
- https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Routing/ImplicitRouteBinding.php
- https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Routing/Console/stubs/controller.model.stub
- https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Database/Eloquent/Builder.php
- https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Database/Eloquent/Model.php
- https://github.com/laravel/framework/blob/v11.56.1/src/Illuminate/Database/Connection.php
- https://github.com/laravel/framework/tree/v11.56.1

### App skeleton + starter

- https://github.com/laravel/laravel/blob/11.x/public/index.php
- https://github.com/laravel/laravel/blob/11.x/bootstrap/app.php
- https://github.com/laravel/laravel/blob/11.x/bootstrap/providers.php
- https://github.com/laravel/laravel/blob/11.x/routes/web.php
- https://github.com/laravel/breeze/blob/2.x/stubs/default/routes/web.php
- https://github.com/laravel/breeze/blob/2.x/stubs/default/app/Http/Controllers/ProfileController.php

### Metrics (fetched 2026-09-27)

- https://packagist.org/packages/laravel/breeze.json
- https://packagist.org/packages/laravel/jetstream.json
- https://packagist.org/packages/laravel/framework.json
- https://packagist.org/packages/laravel/laravel.json
- https://api.github.com/repos/laravel/breeze
- https://api.github.com/repos/laravel/jetstream
- https://api.github.com/repos/laravel/framework
- https://api.github.com/repos/laravel/laravel

### Related local note

- [`docs/research/laravel-11-crud-primary-sources.md`](./laravel-11-crud-primary-sources.md) — resource action ↔ Eloquent map (not layers)
