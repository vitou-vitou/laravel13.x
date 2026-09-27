# Laravel 11 CRUD — primary-source map

**Date:** 2026-09-27  
**Scope:** Framework `11.x` + app skeleton `laravel/laravel` `11.x` + first-party starters (Breeze / Jetstream) + official 11.x docs. No third-party CRUD generators.

**Verdict:** Laravel 11’s canonical web CRUD is not a packaged admin module. It is a *convention stack*: `Route::resource` / `apiResource` → seven (or five) controller actions → Eloquent `create` / `find*` / `update` / `save` / `delete` (optional `SoftDeletes`), with Form Requests as the recommended validation hook for `store` / `update`. The app skeleton ships **no** domain CRUD. Official starters scaffold **auth (+ profile, and for Jetstream teams/API tokens)** — not generic resource admin. Domain CRUD is generated empty via `php artisan make:controller --resource` (+ optional `--model` / `--requests`) and filled by the app.

---

## 1. What “most popular” means here

Measured 2026-09-27 from GitHub API + Packagist (first-party packages only).

| Package | Ownership | GitHub stars | Packagist downloads (total / monthly) | Role vs CRUD |
|---|---|---:|---|---|
| [laravel/breeze](https://github.com/laravel/breeze) | Laravel (Taylor Otwell) | **3,059** | **41,956,210 / 2,217,847** | Auth + profile RU/D |
| [laravel/jetstream](https://github.com/laravel/jetstream) | Laravel (Taylor Otwell) | **4,060** | **24,107,080 / 859,770** | Auth + profile + optional **Teams** C/R/U/D + API tokens |

**Choice for this note:**

- **By install volume (Packagist):** Breeze is clearly more popular.
- **By GitHub stars:** Jetstream leads slightly.
- **Official docs positioning (11.x starter kits):** Breeze = “minimal… authentication”; Jetstream = “augments that functionality” with 2FA, Sanctum API, optional teams — and docs recommend Breeze first for newcomers. ([starter-kits.md](https://github.com/laravel/docs/blob/11.x/starter-kits.md))

Neither starter is a generic CRUD scaffold. For **domain** CRUD patterns, the framework + docs (`Route::resource` + Eloquent) are the primary source; Jetstream’s **Team** controllers are the richest first-party *application-level* CRUD-shaped example beyond User profile.

**Limitation:** Stars/downloads change daily; Packagist totals include historical installs across Laravel major versions (Breeze/Jetstream composer constraints allow `^11\|^12\|^13`). No official “CRUD scaffold popularity” metric exists.

**Local workspace note:** `d:\laravel13.x\vendor\laravel\framework` is **Laravel 13.6.0** (`Application::VERSION`). Claims below cite **upstream `11.x` / tag `v11.56.1`**, not local vendor.

---

## 2. Canonical CRUD map

Docs table (Photo example) and framework registration agree on seven resource actions. ([controllers.md — Actions Handled by Resource Controllers](https://github.com/laravel/docs/blob/11.x/controllers.md); [`ResourceRegistrar::$resourceDefaults`](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Routing/ResourceRegistrar.php))

| HTTP | URI pattern | Route name | Controller method | Typical Eloquent call (app-authored) |
|---|---|---|---|---|
| `GET` | `/photos` | `photos.index` | `index` | `Photo::query()->…->get()` / `paginate()` (list; not a single primitive) |
| `GET` | `/photos/create` | `photos.create` | `create` | *(none — form view only)* |
| `POST` | `/photos` | `photos.store` | `store` | `Photo::create($validated)` or `(new Photo)->fill(…)->save()` |
| `GET` | `/photos/{photo}` | `photos.show` | `show` | route-model binding / `Photo::findOrFail($id)` |
| `GET` | `/photos/{photo}/edit` | `photos.edit` | `edit` | same as show (load for form) |
| `PUT`/`PATCH` | `/photos/{photo}` | `photos.update` | `update` | `$photo->update($validated)` or `fill` + `save` |
| `DELETE` | `/photos/{photo}` | `photos.destroy` | `destroy` | `$photo->delete()` / `Photo::destroy($ids)` |

**API variant:** `Route::apiResource` registers only `index`, `show`, `store`, `update`, `destroy` (excludes HTML `create` / `edit`). ([`Router::apiResource`](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Routing/Router.php))

**Registration entry points:**

```php
Route::resource('photos', PhotoController::class);
Route::apiResource('photos', PhotoController::class);
```

Facade → `Router::resource` → `PendingResourceRegistration` → `ResourceRegistrar::register`. ([`Route` facade](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Support/Facades/Route.php), [`Router::resource`](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Routing/Router.php))

**HTTP verbs as wired in framework** (`ResourceRegistrar`):

| Method | Registrar helper | Router call |
|---|---|---|
| `index` | `addResourceIndex` | `get` |
| `create` | `addResourceCreate` | `get` (`…/create`) |
| `store` | `addResourceStore` | `post` |
| `show` | `addResourceShow` | `get` |
| `edit` | `addResourceEdit` | `get` (`…/edit`) |
| `update` | `addResourceUpdate` | `match(['PUT','PATCH'])` |
| `destroy` | `addResourceDestroy` | `delete` |

Source: [ResourceRegistrar.php (11.x)](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Routing/ResourceRegistrar.php) — `$resourceDefaults` at lines 21; `addResource*` ~296–423.

---

## 3. Resource controller seven methods — confirmed

### Framework defaults

```php
protected $resourceDefaults = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
```

[ResourceRegistrar.php L21](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Routing/ResourceRegistrar.php)  
Pin: branch tip `11.x` @ `0a4034df73e6a432bad0b4a699b7922d5b6010ba` (fetched 2026-09-27); latest 11 tag observed `v11.56.1`.

### Generator stubs (empty bodies — no Eloquent calls)

`php artisan make:controller PhotoController --resource` uses `controller.stub` with all seven methods. ([controller.stub](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Routing/Console/stubs/controller.stub))

`--api` → five methods (`controller.api.stub`). ([controller.api.stub](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Routing/Console/stubs/controller.api.stub))

`--model=Photo --requests` → `controller.model.stub` with Form Request type-hints on `store`/`update` and model binding on `show`/`edit`/`update`/`destroy`. ([controller.model.stub](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Routing/Console/stubs/controller.model.stub); [`ControllerMakeCommand`](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Routing/Console/ControllerMakeCommand.php) options `--resource`, `--api`, `--model`, `--requests`)

### Docs

Same seven-row table under “Actions Handled by Resource Controllers”. ([laravel.com/docs/11.x/controllers#actions-handled-by-resource-controllers](https://laravel.com/docs/11.x/controllers#actions-handled-by-resource-controllers) / [docs repo](https://github.com/laravel/docs/blob/11.x/controllers.md))

### Related (not the classic 7)

| Pattern | Methods | Source |
|---|---|---|
| Singleton resource | default `show`, `edit`, `update` | `$singletonResourceDefaults` in ResourceRegistrar; docs singleton section |
| Invokable / single-action | `__invoke` only | [controllers.md — Single Action Controllers](https://github.com/laravel/docs/blob/11.x/controllers.md); [controller.invokable.stub](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Routing/Console/stubs/controller.invokable.stub) |
| Soft-deleted route models | `withTrashed()` on resource registration | [PendingResourceRegistration::withTrashed](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Routing/PendingResourceRegistration.php); docs `Route::resource(…)->withTrashed()` |

### Base controller classes (important 11.x split)

| Class | Role |
|---|---|
| `Illuminate\Routing\Controller` | Framework base: middleware bag + `callAction` — **no CRUD methods**. ([Controller.php](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Routing/Controller.php)) |
| `App\Http\Controllers\Controller` (skeleton) | Empty `abstract class Controller {}` — **does not** extend framework Controller (11.x simplification). ([laravel/laravel 11.x](https://github.com/laravel/laravel/blob/11.x/app/Http/Controllers/Controller.php); [releases.md](https://github.com/laravel/docs/blob/11.x/releases.md)) |

---

## 4. Eloquent CRUD primitives (framework)

Docs: [Inserting / Updating](https://laravel.com/docs/11.x/eloquent#inserting-and-updating-models), [Deleting](https://laravel.com/docs/11.x/eloquent#deleting-models), [Soft Deleting](https://laravel.com/docs/11.x/eloquent#soft-deleting).

| Operation | Primary API | Where defined (11.x) |
|---|---|---|
| Create (mass assign + insert) | `Model::create($attrs)` → Builder `create` → `newModelInstance` + `save` | [Builder::create](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Database/Eloquent/Builder.php) |
| Create/update instance | `save()` / `saveOrFail()` | [Model::save](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Database/Eloquent/Model.php) |
| Update existing | `update($attrs)` → `fill` + `save` | [Model::update](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Database/Eloquent/Model.php) |
| Read by key | `find` / `findOrFail` | [Builder::find](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Database/Eloquent/Builder.php) (via `Model::__callStatic` + `ForwardsCalls`) |
| Delete instance | `delete()` | [Model::delete](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Database/Eloquent/Model.php) |
| Delete by ids | `destroy($ids)` | [Model::destroy](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Database/Eloquent/Model.php) |
| Soft delete | `SoftDeletes` trait: `delete` → `runSoftDelete`; `restore`; `forceDelete` | [SoftDeletes.php](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Database/Eloquent/SoftDeletes.php) |

Mass assignment is gated by `$fillable` / `$guarded` (docs + model attributes) — required before `create`/`update` with request arrays.

---

## 5. Form Request hooks used in CRUD

Canonical path for `store` / `update`:

1. Type-hint `StorePhotoRequest` / `UpdatePhotoRequest` on controller method.
2. `FormRequest` implements `ValidatesWhenResolved`; container resolves request → `validateResolved()`.
3. Trait flow: `prepareForValidation` → `authorize` (if present) → validator from `rules()` → fail or `passedValidation`.
4. Controller uses `$request->validated()`.

Sources:

- [FormRequest.php](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Foundation/Http/FormRequest.php) — `validationRules()` calls user `rules()`
- [ValidatesWhenResolvedTrait](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Validation/ValidatesWhenResolvedTrait.php)
- Docs: [Form Request Validation](https://laravel.com/docs/11.x/validation#form-request-validation)
- Generator: `make:controller … --resource --requests` ([docs](https://github.com/laravel/docs/blob/11.x/controllers.md), ControllerMakeCommand `--requests`)

Inline alternative (still first-party): `$request->validate([...])` inside the controller (used heavily in Breeze auth controllers).

---

## 6. Application skeleton (`laravel/laravel` 11.x) — no domain CRUD

| File | What it shows |
|---|---|
| [routes/web.php](https://github.com/laravel/laravel/blob/11.x/routes/web.php) | Single `GET /` → `welcome` view. **No** `Route::resource`. |
| [bootstrap/app.php](https://github.com/laravel/laravel/blob/11.x/bootstrap/app.php) | `withRouting(web: …)` only — **no** default `api.php` (removed in 11; optional via Artisan). ([releases.md](https://github.com/laravel/docs/blob/11.x/releases.md)) |
| [app/Models/User.php](https://github.com/laravel/laravel/blob/11.x/app/Models/User.php) | Auth model with `$fillable`; not a resource controller demo |
| [composer.json](https://github.com/laravel/laravel/blob/11.x/composer.json) | `laravel/framework: ^11.31` — no Breeze/Jetstream required by default |

Skeleton tip SHA (fetched): `ecf6de4992d70dd37c21676be6b8ba4743151e63` on branch `11.x`.

---

## 7. Starters: how they do (and do not) scaffold domain CRUD

### 7.1 Laravel Breeze (winner by Packagist volume; 2.x for Laravel 11)

- **Self-description:** “Minimal Laravel authentication scaffolding…” ([composer.json](https://github.com/laravel/breeze/blob/2.x/composer.json)); README redirects newer kits but still states kit is for Laravel 11.x and prior.
- **Docs:** login, registration, password reset, email verification, password confirmation, plus a **profile** page. ([starter-kits.md](https://github.com/laravel/docs/blob/11.x/starter-kits.md))
- **Tree inspection (branch `2.x`):** Controllers under `stubs/*/app/Http/Controllers/Auth/*` + `ProfileController` only — **no** Photo/Post/Product sample models or `Route::resource` for domain entities.

**Closest CRUD-shaped code — Profile (Read form / Update / Delete self):**

Routes ([stubs/default/routes/web.php](https://github.com/laravel/breeze/blob/2.x/stubs/default/routes/web.php)):

| Verb | Path | Action |
|---|---|---|
| `GET` | `/profile` | `ProfileController@edit` |
| `PATCH` | `/profile` | `ProfileController@update` |
| `DELETE` | `/profile` | `ProfileController@destroy` |

Not registered via `Route::resource` — explicit routes (singleton-ish profile).

`update` uses Form Request + Eloquent `fill` / `save`:

```php
public function update(ProfileUpdateRequest $request): RedirectResponse
{
    $request->user()->fill($request->validated());
    // …
    $request->user()->save();
    return Redirect::route('profile.edit')->with('status', 'profile-updated');
}
```

([ProfileController.php](https://github.com/laravel/breeze/blob/2.x/stubs/default/app/Http/Controllers/ProfileController.php))

`destroy` → `$user->delete()`.  
Registration `store` → `User::create([...])` ([RegisteredUserController](https://github.com/laravel/breeze/blob/2.x/stubs/default/app/Http/Controllers/Auth/RegisteredUserController.php)).

**Conclusion:** Breeze demonstrates auth + **user profile RU/D**, not generic admin CRUD.

### 7.2 Laravel Jetstream (more stars; richer app features)

- **Docs:** auth + 2FA + session management + Sanctum API + **optional team management**. ([starter-kits.md](https://github.com/laravel/docs/blob/11.x/starter-kits.md); [jetstream.laravel.com](https://jetstream.laravel.com))
- **TeamController** (Inertia) is the clearest first-party multi-entity CRUD-shaped controller: `create` / `store` / `show` / `update` / `destroy` with `findOrFail`, Gates, and action contracts (`CreatesTeams`, `UpdatesTeamNames`, `DeletesTeams`). ([TeamController.php](https://github.com/laravel/jetstream/blob/5.x/src/Http/Controllers/Inertia/TeamController.php))
- Still **not** a generic resource admin: teams/API tokens/profile only; no `Route::resource` generator for arbitrary models.

**Conclusion:** Prefer Jetstream when studying first-party **application** CRUD (teams). Prefer framework resource routing when studying **canonical** CRUD convention. Prefer Breeze when measuring **most-installed** starter.

### 7.3 First-party demo apps

| Artifact | Status | Notes |
|---|---|---|
| `laravel/quickstart` | **Gone** (GitHub API 404, 2026-09-27) | Historical tutorial app; not available as primary source today |
| Laravel Bootcamp | Live site ([bootcamp.laravel.com](https://bootcamp.laravel.com)) | Docs point newcomers here with Breeze; teaching app, not a CRUD framework |
| `laravel/breeze-next` | First-party Next.js frontend twin | Auth UI parity with Breeze; not domain CRUD ([starter-kits.md](https://github.com/laravel/docs/blob/11.x/starter-kits.md)) |

---

## 8. Gaps — what Laravel 11 does **not** provide OOTB for generic admin CRUD

From primary sources above:

1. **No domain resource controllers** in the default skeleton — only welcome route.
2. **No admin CRUD UI generator** in framework or free starters — `make:controller --resource` stubs are empty comments.
3. **No automatic mapping** from Eloquent model → HTML admin (list filters, bulk delete, relation forms, media, etc.).
4. **Starters ≠ CRUD kits** — Breeze/Jetstream keywords and docs center on **auth** (Jetstream adds teams/tokens).
5. **No default `api.php`** in 11 skeleton — API resources are opt-in.
6. **App base Controller** no longer includes `AuthorizesRequests` / `ValidatesRequests` by default — policies/validation are per-controller or Form Request.
7. **Soft deletes** are opt-in traits + schema (`deleted_at`); not default on models.
8. **Commercial first-party admin** (Laravel Nova) is outside free OOTB stack; no `nova.md` in `laravel/docs` 11.x tree (404) — Nova has its own product docs, not part of the free CRUD convention.

Developers assemble CRUD: migration + model (`$fillable`) + `make:controller -rmR` + `Route::resource` + views/Inertia pages + policies.

---

## 9. End-to-end mental model (canonical)

```
HTTP request
  → routes/web.php: Route::resource('photos', PhotoController::class)
  → ResourceRegistrar maps verb/URI → PhotoController@{action}
  → (optional) FormRequest validateResolved → rules/authorize
  → Controller method
  → Eloquent create | findOrFail | update | delete [| SoftDeletes]
  → Response (view / redirect / JSON)
```

That chain is the official CRUD implementation. Starters only show the same Eloquent/validation primitives on **User** (and Jetstream **Team**).

---

## Sources appendix

### Docs (11.x)

- https://laravel.com/docs/11.x/controllers — Resource Controllers  
- https://laravel.com/docs/11.x/eloquent — Insert / Update / Delete / Soft Deletes  
- https://laravel.com/docs/11.x/validation#form-request-validation  
- https://laravel.com/docs/11.x/routing — (resource helpers also documented via controllers)  
- https://github.com/laravel/docs/blob/11.x/controllers.md  
- https://github.com/laravel/docs/blob/11.x/eloquent.md  
- https://github.com/laravel/docs/blob/11.x/validation.md  
- https://github.com/laravel/docs/blob/11.x/starter-kits.md  
- https://github.com/laravel/docs/blob/11.x/releases.md  

### Framework (branch `11.x`, tip `0a4034d…`; tag `v11.56.1`)

- https://github.com/laravel/framework/blob/11.x/src/Illuminate/Routing/ResourceRegistrar.php  
- https://github.com/laravel/framework/blob/11.x/src/Illuminate/Routing/Router.php  
- https://github.com/laravel/framework/blob/11.x/src/Illuminate/Routing/PendingResourceRegistration.php  
- https://github.com/laravel/framework/blob/11.x/src/Illuminate/Routing/Controller.php  
- https://github.com/laravel/framework/blob/11.x/src/Illuminate/Support/Facades/Route.php  
- https://github.com/laravel/framework/blob/11.x/src/Illuminate/Routing/Console/ControllerMakeCommand.php  
- https://github.com/laravel/framework/blob/11.x/src/Illuminate/Routing/Console/stubs/controller.stub  
- https://github.com/laravel/framework/blob/11.x/src/Illuminate/Routing/Console/stubs/controller.api.stub  
- https://github.com/laravel/framework/blob/11.x/src/Illuminate/Routing/Console/stubs/controller.model.stub  
- https://github.com/laravel/framework/blob/11.x/src/Illuminate/Database/Eloquent/Model.php  
- https://github.com/laravel/framework/blob/11.x/src/Illuminate/Database/Eloquent/Builder.php  
- https://github.com/laravel/framework/blob/11.x/src/Illuminate/Database/Eloquent/SoftDeletes.php  
- https://github.com/laravel/framework/blob/11.x/src/Illuminate/Foundation/Http/FormRequest.php  
- https://github.com/laravel/framework/blob/11.x/src/Illuminate/Validation/ValidatesWhenResolvedTrait.php  

### Application skeleton (`11.x`)

- https://github.com/laravel/laravel/blob/11.x/routes/web.php  
- https://github.com/laravel/laravel/blob/11.x/bootstrap/app.php  
- https://github.com/laravel/laravel/blob/11.x/app/Http/Controllers/Controller.php  
- https://github.com/laravel/laravel/blob/11.x/app/Models/User.php  
- https://github.com/laravel/laravel/blob/11.x/composer.json  

### Starters

- https://github.com/laravel/breeze (branch `2.x`) — Profile + Auth stubs  
- https://github.com/laravel/jetstream (branch `5.x`) — TeamController et al.  
- https://jetstream.laravel.com — Jetstream product docs  
- Packagist: https://packagist.org/packages/laravel/breeze · https://packagist.org/packages/laravel/jetstream  
- GitHub API (stars): https://api.github.com/repos/laravel/breeze · https://api.github.com/repos/laravel/jetstream  

### Explicitly unavailable / out of scope

- `laravel/quickstart` — API 404 (2026-09-27)  
- Third-party CRUD generator packages — excluded by research rules  
- Local `vendor/laravel/framework` in this workspace — Laravel **13**, not used as 11.x evidence  
