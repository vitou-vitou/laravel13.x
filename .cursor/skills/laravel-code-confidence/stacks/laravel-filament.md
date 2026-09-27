# Laravel + Filament

## Detect
- `filament/filament` · `app/Filament` · admin Resources/Pages

## First-party inventory
| Piece | First-party? | Where |
|---|---|---|
| Filament packages | First-party **Filament** (not Laravel core) | `vendor/filament/*` · filamentphp.com docs |
| Laravel framework | Yes | `vendor/laravel/framework` |
| Your Resources / forms / tables | No | `app/Filament` |

## Thin shape
```
Filament Resource / Page → Form·Table schema → Eloquent model
(optional) one domain action for heavy writes
```

Prefer Filament Resource patterns over hand-rolled admin MVC.

## Scaffold
1. Install Filament per **Filament docs** for the Laravel major you run
2. `filament:resource` (or current artisan) — don’t invent parallel admin CRUD

## Dial-up
- Policies/tenancy · custom Livewire pages · money fields · bulk actions

## Slop smells
- Rebuilding Filament features with custom Blade admin “because MVC”
- Ignoring Resource conventions for a one-off controller CRUD
- Mixing Filament v2/v3 APIs from blogs vs installed major
- Giant custom Livewire page when a Resource+form would do

## Last lesson
- Filament idiomatic ≠ Laravel Blade idiomatic — use this pack, not laravel-blade
