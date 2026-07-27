# laravel13.x — Spec-Kit / OpenSpec / Superpowers policy

**Repo:** `laravel13.x`  
**Handoff:** `docs/SESSION_STATE.md` (always read on `continue`)

## Locked decisions

| Phase | Tool | Rule |
|-------|------|------|
| Greenfield MVP | **Spec-Kit** + **Superpowers** | Constitution → specify → plan → tasks → implement with TDD |
| Post-MVP changes | **OpenSpec** + **Superpowers** | `/opsx:new` → continue/apply → archive |
| Never | Spec-Kit **and** OpenSpec on same feature | Pick one SDD layer |

## Active example apps (MVP complete — do not re-scaffold)

| Path | Spec-Kit | OpenSpec | Tests (approx.) |
|------|----------|----------|-----------------|
| `examples/kindly-login-1122` | `.specify/specs/001-kindly-login/` | post-MVP only | 30 |
| `examples/kindly-e-commerce-1122` | `001`, `003-stripe-checkout`, `003-order-lifecycle` | post-MVP only | 53 |
| `examples/booking-v1` | `001-appointment-booking/` (T001–T020 done) | optional Filament change | 15 |

## Superpowers skills to prefer during implement

1. `test-driven-development` — before production code  
2. `verification-before-completion` — before claiming done  
3. `systematic-debugging` — on test failures  
4. `requesting-code-review` — after major steps  
5. `subagent-driven-development` — parallel independent tasks  

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

## Study docs (optional)

After major features, refresh with **system-study-packet** skill → `docs/study-packets/`.
