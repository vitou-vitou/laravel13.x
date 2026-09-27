# Laravel + Livewire

## Detect
- `livewire/livewire` · `app/Livewire` or Volt · primary UI

## First-party inventory
| Piece | First-party? | Where |
|---|---|---|
| Livewire | Yes (Livewire / Laravel ecosystem first-party-ish) | lock + livewire docs |
| Framework | Yes | `vendor/laravel/framework` |
| Components | No | app code |

## Thin shape
```
routes → Livewire component (or Volt) → Eloquent / one action
```

## Scaffold
1. Docs starter with Livewire for target major
2. Match existing component style

## Dial-up
- Entangle/JS bridge complexity · auth · uploads

## Slop smells
- Dual Livewire + Inertia for same screen
- Fat components that are secretly service containers
