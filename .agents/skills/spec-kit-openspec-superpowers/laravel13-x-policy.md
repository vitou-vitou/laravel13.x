# laravel13.x — Spec-Kit / OpenSpec / Superpowers policy

**Repo:** `laravel13.x`  
**Handoff:** `docs/SESSION_STATE.md` (always read on `continue`)

## Locked decisions

| Phase | Tool | Rule |
|-------|------|------|
| Greenfield MVP | **Spec-Kit** + **Superpowers** (+ **Caveman** optional) | Constitution → specify → plan → tasks → implement with TDD |
| Post-MVP changes | **OpenSpec** + **Superpowers** (+ **Caveman** optional) | `/opsx:new` → continue/apply → archive |
| Never | Spec-Kit **and** OpenSpec on same feature | Pick one SDD layer |
| Optional | **Caveman** | Voice / token compression only — `/caveman`, `Use caveman:`; not SDD |
| Post-MVP UI | **laravel-ui-phase** + **impeccable** + **design-taste-frontend** | After tests green; see `docs/FRONTEND_REAL_WORLD_GATE.md` |

## Active example apps (MVP complete — do not re-scaffold)

| Path | Spec-Kit | OpenSpec | Tests (approx.) | UI |
|------|----------|----------|-----------------|-----|
| `examples/kindly-login-1122` | `.specify/specs/001-kindly-login/` | post-MVP only | 30 | optional polish |
| `examples/kindly-e-commerce-1122` | `001`, `003-stripe-checkout`, `003-order-lifecycle` | post-MVP only | 53 | optional polish |
| `examples/booking-v1` | `001-appointment-booking/` (T001–T020 done) | optional Filament change | 15 | optional polish |
| `examples/marketplace-v2` | roadmap complete | post-MVP only | 39 | **UI phase done** — `docs/DESIGN.md` |
| `examples/clone-the-fb-nav` | MVP complete | post-MVP only | 6 | has `docs/DESIGN.md` |

## Superpowers skills to prefer during implement

1. `test-driven-development` — before production code  
2. `verification-before-completion` — before claiming done  
3. `systematic-debugging` — on test failures  
4. `requesting-code-review` — after major steps  
5. `subagent-driven-development` — parallel independent tasks  

**Optional:** **caveman** — terse voice during long sessions; **cavecrew** subagents for compressed investigator/builder/reviewer output (plugin: `~/.cursor/plugins/cache/caveman/`).

## External review (not Superpowers)

| Project | Review channel |
|---------|----------------|
| booking-v1 | Grok (`docs/GROK_LOOP.md`) |
| kindly-login | Arena Direct + claude-sonnet-4-6 |
| kindly-e-commerce | Arena (`docs/ARENA_*.md`) |

## PHP (Windows Herd)

```bash
/c/Users/vitou/.config/herd/bin/php.bat artisan test
```

Run from each `examples/*` directory.

## UI phase (post-MVP presentation)

When user says **AI pick my UI** or UI looks like default Breeze:

1. Read **laravel-ui-phase** (`.agents/skills/laravel-ui-phase/SKILL.md`)
2. Read `docs/GITHUB_UI_RESOURCE_INDEX.md` + `docs/FRONTEND_REAL_WORLD_GATE.md`
3. Write/update `examples/<slug>/docs/DESIGN.md`
4. **impeccable** (`audit` / `polish`) + **design-taste-frontend** for Blade storefronts
5. `npm run build`; `php artisan test` — same green bar as functional MVP

Reference implementation: `examples/marketplace-v2` (shared `x-store-page`, `.btn-brand`, full page pass).

## Study docs (optional)

After major features, refresh with **system-study-packet** skill → `docs/study-packets/`.
