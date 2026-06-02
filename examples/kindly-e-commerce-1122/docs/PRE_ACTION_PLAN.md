# Pre-action plan — kindly-e-commerce-1122 (Phase 3+)

**Date:** 2026-06-01  
**Status:** Research complete — **no Phase 3 code until gates below pass**  
**Workflow:** [Spec-Kit + Superpowers](https://github.com/github/spec-kit) · Arena.ai external review · **OpenSpec for change orders** (not Spec-Kit re-init)

---

## Current baseline (frozen)

| Layer | State |
|-------|--------|
| MVP + Phase 2 | **44/44** tests, browser verified @ :8012 |
| SDD at greenfield | Spec-Kit artifacts under `.specify/specs/` |
| Arena | Prompts A/B done; **Prompt C (deep)** → `docs/ARENA_DEEP_REVIEW_PHASE3.md` |
| Constitution | `.specify/memory/constitution.md` (amend before Phase 3 implement) |

**Do not re-scaffold:** Breeze, commerce tables, cart/checkout, coupons, admin CRUD.

---

## Triad decision (Spec-Kit · OpenSpec · Superpowers)

| Tool | Role for Phase 3+ |
|------|-------------------|
| **Spec-Kit** | Keep existing `001` / `002` specs as historical truth; optional new folder `003-*` for greenfield-style feature packets |
| **OpenSpec** | **Preferred** for each change order (`/opsx:new`, `/opsx:ff`, `/opsx:apply`) — matches constitution “post-MVP change orders” |
| **Superpowers** | **Required** on every task: TDD → implement → `php artisan test` → browser spot-check → `verification-before-completion` |

**Rule:** Do **not** run Spec-Kit and OpenSpec in parallel on the same change. Pick OpenSpec per feature; use Superpowers for execution.

---

## Pre-implementation gates (all required)

1. Read `docs/ARENA_DEEP_REVIEW_PHASE3.md` and merge accepted items into draft spec.
2. User picks **one** Phase 3 track (see ranked options in deep review).
3. Run Arena **Prompt C** in Direct + **claude-sonnet-4-6**; save reply into `ARENA_DEEP_REVIEW_PHASE3.md` § Live Arena (or confirm synthesis).
4. Create change artifacts:
   - **OpenSpec path:** `openspec init` (once) → `/opsx:new <change-name>` → spec → design → tasks  
   - **Spec-Kit path (alt):** `.specify/specs/003-<name>/spec.md` + `plan.md` + `tasks.md` (if user declines OpenSpec)
5. **No `/speckit.implement` or `/opsx:apply` until tasks.md exists and user says “implement Phase 3”.**

---

## Recommended Phase 3 sequence (after research)

| Order | Change | Why |
|-------|--------|-----|
| **3a** | Order lifecycle + email | Builds on stub pay; low external deps; closes fulfillment gap |
| **3b** | Stock locking / reservation | Addresses Arena oversell race without payment vendor |
| **3c** | Stripe Checkout (test mode) | Real gateway; needs `.env` + webhook tests |
| **Defer** | Multi-vendor, Sanctum API | Different product; large surface |

Draft spec (not implemented): `.specify/specs/003-order-lifecycle/spec.md`

---

## Arena loop (Phase 3)

See `docs/ARENA_LOOP.md` — **Prompt C** added for deep roadmap review.

---

## Commands (unchanged)

```bash
cd d:/laravel13.x/examples/kindly-e-commerce-1122
/c/Users/vitou/.config/herd/bin/php.bat artisan test
```

---

## Your next message (to start implementation)

Pick one:

- `implement Phase 3a` — order lifecycle + confirmation email  
- `implement Phase 3c` — Stripe test mode  
- `init OpenSpec` — scaffold change-order workflow first  

Until then: **research-only mode** (this document).
