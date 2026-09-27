# Stack registry

Detect **one primary** (+ optional secondary). Then read **only** that pack.

| Signals (any strong match) | Label | Pack |
|---|---|---|
| `filament/filament` or `app/Filament` | Laravel + Filament | [laravel-filament.md](laravel-filament.md) |
| Nova / Backpack / Orchid / open-admin / `encore/laravel-admin` | Laravel + admin package | [laravel-admin.md](laravel-admin.md) (name exact package) |
| `inertiajs/inertia-laravel` + `@inertiajs/react` | Laravel + Inertia React | [laravel-inertia.md](laravel-inertia.md) |
| `inertiajs/inertia-laravel` + `@inertiajs/vue3` | Laravel + Inertia Vue | [laravel-inertia.md](laravel-inertia.md) |
| API `routes/api.php` + detached SPA (Sanctum/Passport) · little Inertia | Laravel + API SPA | [laravel-spa-api.md](laravel-spa-api.md) |
| `livewire/livewire` primary UI | Laravel + Livewire | [laravel-livewire.md](laravel-livewire.md) |
| Blade views · no Inertia/Livewire/Filament as primary | Laravel + Blade | [laravel-blade.md](laravel-blade.md) |
| `keycloakify` / `keycloakify-starter` / Keycloak theme build | Keycloakify | [keycloakify.md](keycloakify.md) |
| `pnpm-workspace.yaml` / `nx.json` / `turbo.json` / workspaces in root `package.json` | Node monorepo | [node-monorepo.md](node-monorepo.md) |
| `laravel/framework` but UI unclear | Laravel (detect deeper) | re-scan Filament → Inertia → Livewire → API → Blade |

## Mixed repos

Example: Laravel Inertia app **and** a `packages/` JS workspace → primary = Inertia pack; secondary = [node-monorepo.md](node-monorepo.md) for workspace rules only.

Example: Filament admin + public Inertia site → name both; change only the side the ticket touches.

## Pure React / Vue (no Laravel)?

Do **not** stretch this skill. Use sibling **`/frontend-code-confidence`** (`~/.cursor/skills/frontend-code-confidence/`) — packs for React, React+Next, Vue 2, Vue 3.

## Missing stack?

Mode D: add a row here + new pack from [_pack-template.md](_pack-template.md).

## Asked, not detected

Not a primary UI. Do **not** pick from `composer.json` alone. UI pack stays primary.

| Ask | Pack |
|---|---|
| “All Laravel concepts?” / 5-year admin roadmap | [admin-5y-roadmap.md](admin-5y-roadmap.md) |
