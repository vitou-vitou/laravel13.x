---
name: laravel-ui-phase
description: >-
  Post-MVP UI polish for laravel13.x examples/* — agent-picked references, DESIGN.md,
  Blade/Tailwind tokens, tests stay green. Use when user says "AI pick my UI", UI too basic,
  Breeze looks gray, polish storefront, or FRONTEND_REAL_WORLD_GATE UI phase after functional MVP.
---

# laravel13.x — UI phase (functional MVP → PRD look)

**When:** Tests green, flows work, UI still default Breeze/gray — on an **existing Laravel** `examples/<slug>/` (user already chose Laravel).  
**Not:** Greenfield scaffold (do **not** run `new-example` unless user explicitly picks Laravel). **Not:** Business-logic changes.

---

## Trigger phrases

```text
AI pick my UI
UI too basic
polish the storefront
make it look real-world
FRONTEND_REAL_WORLD_GATE
whole storefront / all pages
```

---

## Read first (repo root)

| Doc | Purpose |
|-----|---------|
| [`docs/GITHUB_UI_RESOURCE_INDEX.md`](../../docs/GITHUB_UI_RESOURCE_INDEX.md) | Agent auto-pick rules + copy-paste prompt |
| [`docs/FRONTEND_REAL_WORLD_GATE.md`](../../docs/FRONTEND_REAL_WORLD_GATE.md) | Functional vs PRD UI; acceptance |
| `examples/<slug>/docs/DESIGN.md` | Tokens + page scope (create/update every UI pass) |

---

## Skill stack (use together)

| Order | Skill | Role |
|-------|-------|------|
| 1 | **laravel-ui-phase** (this) | Routing, scope, laravel13.x constraints |
| 2 | [`docs/GITHUB_UI_RESOURCE_INDEX.md`](../../docs/GITHUB_UI_RESOURCE_INDEX.md) § Agent auto-pick | Pick references; user does not paste Dribbble |
| 3 | **impeccable** | Audit-first polish, a11y, hierarchy (`audit` / `polish` / `layout`) |
| 4 | **design-taste-frontend** | Anti-slop for marketing/catalog surfaces |
| 5 | **superpowers** | `verification-before-completion` — `php artisan test` after UI |
| 6 | **caveman** (optional) | Terse voice; does not skip DESIGN.md |

**Blade marketplace reference:** `examples/marketplace-v2/docs/DESIGN.md` + shared components (`x-store-page`, `.btn-brand`, `.store-panel`).

---

## Agent workflow

1. **Classify** app (marketplace, admin, landing, B2B portal) per GITHUB_UI_RESOURCE_INDEX § Step 1.
2. **Write/update** `examples/<slug>/docs/DESIGN.md` — inspiration URLs, tokens, page list, do-not-touch (checkout/Stripe/auth logic).
3. **Audit** existing Blade/Vue with **impeccable** `audit` or read DESIGN.md + one representative page.
4. **Implement** — same routes/forms/POST actions; Tailwind tokens + shared Blade components.
5. **Build** — `npm run build` when CSS changed.
6. **Verify** — from `examples/<slug>`: `php artisan test`; `./bin/verify-example <slug>` from repo root.
7. **Browser** — user opens `APP_URL` from `.env` (e.g. `http://marketplace-v2.test`), not `:5173`.

---

## Scope rules

| Do | Don't |
|----|--------|
| Shared layout, nav, guest auth, all customer-facing pages | Change validation, policies, webhooks |
| Admin Blade polish (when asked or "all pages") | Re-scaffold Breeze/Sanctum |
| Unsplash placeholders via model helper | Commit secrets |
| Reuse component classes across pages | New npm UI libs unless user asks |

---

## Windows Herd

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd examples/<slug> && php artisan test
```

---

## Invocation

```text
Use laravel-ui-phase: AI pick my UI for examples/marketplace-v2 — all pages, tests must stay green.
```

```text
Use laravel-ui-phase + impeccable: audit catalog and cart in examples/marketplace-v2.
```
