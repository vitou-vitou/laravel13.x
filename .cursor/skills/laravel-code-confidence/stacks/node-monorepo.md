# Node monorepo

## Detect
- Root: `pnpm-workspace.yaml` · `nx.json` · `turbo.json` · `package.json` `workspaces`
- Multiple packages under `packages/` / `apps/`

## First-party inventory
| Piece | First-party? | Where |
|---|---|---|
| Package manager / Nx / Turbo | Tooling vendors | their docs + lockfile |
| Each package’s framework | Per-package | open that stack’s pack too |
| App business code | No | package source |

## Thin shape
```
change one package → use THAT package’s scripts/lint/test
shared packages → treat public exports as API (dial up)
```

## Scaffold
1. Prefer the org’s existing generator (`pnpm dlx …`, Nx generate, turbo) — don’t invent a second workspace layout
2. New app/package: match existing `apps/*` neighbor

## Dial-up
- Changing shared library public API · cross-package types · release versioning

## Slop smells
- Hoisting random deps to root “for convenience”
- Importing deep internals across packages instead of public entry
- Applying Laravel folder religion inside a pure JS package
- One giant root ESLint override that fights per-package configs

## Mixed with Laravel
- Root may be JS workspace + `apps/web` Laravel — primary pack = UI inside the app; this pack only for workspace rules
