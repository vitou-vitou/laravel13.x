# UI Design Adoption — Pro Dev Runbook

**Status:** Reference  
**Owner:** Project maintainer  
**Applies to:** Laravel Blade + Tailwind storefronts in this monorepo  
**Last updated:** 2026-06-03

---

## TL;DR

1. **LICENSE → STACK → PORT MAP** (before any file copy).
2. Port **tokens → components → pages**; never merge foreign app logic.
3. **One PR / one surface**; `artisan test` + `npm run build` + browser smoke.
4. E-commerce checkout stays **product register**, not marketing clone.

---

## Definition of done (UI PR)

- [ ] License recorded in PR description or `DESIGN.md` source log
- [ ] Only intended pages/components changed
- [ ] No route name / Stripe / webhook / CSRF regressions
- [ ] `npm run build` succeeds
- [ ] `php artisan test` passes (project directory)
- [ ] Manual: mobile width, empty cart, validation error, logged-out guard where applicable

---

## Phase 0 — Intake (mandatory, ≤30 min)

| Step | Action | Output |
|------|--------|--------|
| 0.1 | Read `LICENSE` | go / no-go |
| 0.2 | Compare stack: Blade vs React, Tailwind major | port type A or B |
| 0.3 | Run reference demo locally or read screenshots | page list |
| 0.4 | Write **port map** (theirs → ours) | markdown table in PR |
| 0.5 | Classify scope: Speed 1 / 2 / 3 | prevents checkout hero |

**Port type A:** Same stack — config + Blade components.  
**Port type B:** Different stack — tokens + wireframe only; rewrite Blade.

---

## Phase 1 — Extract design system

Inspect only — do not commit their `vendor/` or `node_modules`. Copy patterns, not trees.

Extract in order:

1. `tailwind.config.*`, PostCSS, `app.css`
2. Font faces / variables
3. Component markup patterns (buttons, cards, inputs)
4. Layout grid (nav, main, footer)

**Do not extract:** `routes/`, payment services, `.env.example` secrets, entire `app/Http`.

---

## Phase 2 — Integrate (your repo)

1. Merge Tailwind config intentionally (diff, don't overwrite blind).
2. Create Blade components; wire with existing `@vite` and layouts.
3. Apply pages: **shop → cart → checkout → orders** (admin separate).
4. Keep Breeze auth components unless replacing all usages.

---

## Phase 3 — Verify & ship

```bash
cd examples/<project>
npm run build
/c/Users/vitou/.config/herd/bin/php.bat artisan test
# Browser: shop, add to cart, checkout cancel/success paths (test mode)
```

---

## Decision log (ADR-lite)

| ID | Decision | Rationale |
|----|----------|-----------|
| ADR-UI-001 | No full-repo merge into existing MVP | Preserves tests + Stripe behavior |
| ADR-UI-002 | `DESIGN.md` required for AI UI sessions | Stops skill roulette |
| ADR-UI-003 | Checkout excluded from brand/marketing skills | Trust + conversion |
| ADR-UI-004 | One UI kit per app | Avoids conflicting tokens |
| ADR-UI-005 | Dribbble = reference rules, not pixel clone | Reduces back-and-forth |

---

## Skill selection matrix

| Task | Use | Avoid |
|------|-----|-------|
| Token + component port | `impeccable`, `redesign-existing-projects` | `image-to-code` on checkout |
| a11y/UX audit | `web-design-guidelines`, Arena/Grok review | Full regen from screenshot |
| Calm shop UI | `minimalist-ui`, product register | `gpt-taste`, brutalist |
| Marketing page only | `image-to-code` (landing scope) | Same on cart |
| Admin | `filament-pro` if Filament | Storefront theme on admin |

---

## DO NOT (hard rules)

### Legal
- Copy unlicensed repos
- Remove copyright headers required by license

### Technical
- Paste React/Vue into Blade
- Copy `node_modules` / `vendor` from reference
- Mix two UI kits in one PR
- Tailwind major upgrade as side effect of port
- Change Stripe/webhook/checkout controller in design PR

### Process
- “Clone this repo into my project” in one shot
- Redesign shop + cart + checkout in one AI chat
- Ship UI with failing tests
- Skip port map
- Use Dribbble as sole spec for payment pages

---

## Escalation

| Situation | Action |
|-----------|--------|
| GPL or unclear license | Stop; pick MIT alternative or buy theme |
| Reference is full SPA storefront | New greenfield fork, not graft |
| Tailwind v3 vs v4 mismatch | Plan migration issue; don't partial copy v4 config |
| Design breaks Stripe redirect | Revert UI PR; isolate layout-only change |

---

## Related docs in this repo

- [`docs/SESSION_STATE.md`](../../SESSION_STATE.md) — MVP complete; UI polish is post-MVP scoped work
- [`examples/kindly-e-commerce-1122/docs/study-packets/`](../../../examples/kindly-e-commerce-1122/docs/study-packets/) — app-specific study packets
- `.cursor/skills/8-principle-study/` — packet format spec
- `impeccable` skill — `PRODUCT.md` / `DESIGN.md` workflow
