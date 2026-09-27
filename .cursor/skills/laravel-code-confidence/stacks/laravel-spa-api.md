# Laravel + API SPA

## Detect
- `routes/api.php` + SPA (Sanctum/Passport/token) · little/no Inertia as primary

## First-party inventory
| Piece | First-party? | Where |
|---|---|---|
| Framework, Sanctum, Passport | Yes | `vendor/laravel/*` |
| SPA (React/Vue/etc.) | No (unless starter) | app / separate package |
| Community SPA kits | Community | named publisher only |

## Thin shape
```
routes/api → Controller → Form Request / API Resource → JSON
SPA client → UI
```

## Scaffold
1. `laravel new` + Sanctum (or docs API stack)
2. Place SPA where *you* decided (same repo app/ or sibling package) — record in CONTEXT if Matt grilling

## Dial-up
- Auth cookies/CORS · token scopes · public JSON shape changes

## Slop smells
- Second auth mechanism beside existing Sanctum/Passport
- Duplicating Laravel validation only on the client
- Treating SPA folder structure as “official Laravel”
