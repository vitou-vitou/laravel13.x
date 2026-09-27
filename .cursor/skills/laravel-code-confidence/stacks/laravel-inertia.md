# Laravel + Inertia (React or Vue)

## Detect
- `inertiajs/inertia-laravel` + `resources/js/Pages`
- Client: `@inertiajs/react` **or** `@inertiajs/vue3` (not both without reason)

## First-party inventory
| Piece | First-party? | Where |
|---|---|---|
| Laravel framework / Breeze·Jetstream Inertia starters | Yes (Laravel org / docs) | `vendor/laravel/*`, starter kits |
| `inertiajs/inertia-laravel` | Adapter (Inertia org) | lock + Inertia docs |
| `@inertiajs/*` | Inertia org | package.json |
| Pages/components | No | `resources/js/Pages` |

## Thin shape
```
routes → Controller → Form Request → Inertia::render(Page, props)
      → Pages/* thin → small components
```

## Scaffold
1. Official starter with Inertia React or Vue (docs for major)
2. Match starter page/layout layout; don’t invent a second router

## Dial-up
- New prop contract · auth shared props · file uploads

## Slop smells
- `useEffect`/axios refetch of props already passed
- Redux/Pinia for server state the controller sent
- Next.js App Router patterns in Vite+Inertia
- Mixing React + Vue Inertia clients
