# Laravel + Blade

## Detect
- `laravel/framework` · `resources/views` · no Inertia/Livewire/Filament as primary UI

## First-party inventory
| Piece | First-party? | Where |
|---|---|---|
| Framework | Yes | `vendor/laravel/framework` |
| Starters | Yes | `laravel/breeze` (Blade), docs starters |
| Views/controllers | No | app code |

## Thin shape
```
routes → Controller → Form Request → view / redirect
```

## Scaffold
1. `laravel new` per docs for target major
2. Optional Breeze Blade
3. First feature: no Services forest

## Dial-up
- Auth, uploads, multi-tenant views

## Slop smells
- Repositories for one Eloquent model
- Blade + Inertia for the same screen
