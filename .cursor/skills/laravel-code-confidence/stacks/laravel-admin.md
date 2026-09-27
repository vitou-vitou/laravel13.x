# Stack: Laravel admin package

Covers **admin panels** bolted on Laravel (Filament, Nova, Backpack, Orchid, Open-Admin / laravel-admin, etc.). Name the **exact** package from `composer.json` in Mode A/B.

## Primary sources

- **That package’s** official docs + GitHub (not generic “Laravel admin” blogs)
- Laravel docs only for underlying HTTP/Eloquent/auth
- First-party is still `laravel/*`; most admin UIs are **third-party** — idiomatic ≠ official Laravel

## Detect

| Package hints | Label |
|---|---|
| `filament/filament` | Filament |
| `laravel/nova` | Nova |
| `backpack/crud` | Backpack |
| `orchid/platform` | Orchid |
| `encore/laravel-admin` / open-admin | laravel-admin family |

## Thin shape

Follow **package resource/CRUD conventions** first (Resource, CRUD controller, generators).

```
Admin route → Package Resource/CRUD → Model / Form · Tables
```

Don’t invent a parallel REST + Inertia admin beside the package for the same models unless the repo already does.

## Dial up

Custom pages outside generators; auth/gates/policies; multi-tenancy; file fields.

## Slop smells

- Rebuilding the admin in Blade/Inertia “cleaner” mid-feature
- Ignoring package form/table APIs for one-off HTML
- Mixing two admin packages in one app
- Treating package scaffold as Laravel-core official

## Scaffold

Install via **package** docs for the Laravel major in lockfile. Pin versions. Generate one resource end-to-end before custom UI.

## Patch note

After each real admin project, add a short “Invariants we learned” bullet list **named by package** at the bottom of this file (paths, not ticket IDs).
