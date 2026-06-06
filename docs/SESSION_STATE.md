# Session state — read this first (2026-06-01)

**Purpose:** Single handoff so new chats do not re-ask goals, workflow, or what is already done.

**User default phrase:** `continue` or `take controller` → pick actions from **Default next work** below; do not re-scaffold either MVP.

---

## Locked decisions (do not revisit)

| Topic | Decision |
|-------|----------|
| Study source | `docs/study/180-laravel-project-types-study-packet.md` (180+ catalog) |
| Booking vertical | **General appointment scheduling** (#125 style), not hotel/salon marketplace |
| Booking path | `examples/booking-v1` |
| Login path | `examples/kindly-login-1122` (standalone session-auth demo) |
| E-commerce path | `examples/kindly-e-commerce-1122` (catalog + cart + checkout MVP) |
| SDD at greenfield | **Spec-Kit + Superpowers (TDD)** only — **no OpenSpec at init** |
| OpenSpec | Post-MVP change orders only |
| Booking external review | **Grok** — done (`docs/GROK_REVIEW_*.md`) |
| Login external review | **Arena.ai only** — no Grok (`docs/ARENA_LOOP.md`) |
| Arena mode | **Direct** + **claude-sonnet-4-6** (not Battle, not Max) |

---

## Projects at a glance

| Project | MVP | Tests | Auth | External AI |
|---------|-----|-------|------|-------------|
| `examples/kindly-login-1122` | **Complete (100%)** | 30/30 | Breeze session (web) | Arena A+B done; browser verified (`docs/BROWSER_VERIFICATION.md`) |
| `examples/kindly-e-commerce-1122` | **MVP + Phase 2 + 3a Stripe** | 49/49 | Breeze session (web) | Stripe Checkout + webhooks; stub pay removed; :8012 |
| `examples/booking-v1` | **Complete** | 15/15 | Sanctum Bearer API | Grok A+B done; Prompt C optional |
| `examples/clone-the-fb-nav` | **MVP complete** | 6/6 | None (static UI) | FB desktop top-nav study; Spec-Kit `001-fb-top-nav` |
| `examples/dashboard-v1` | **MVP complete** | 40/40 | Breeze + Livewire | DB metrics, Chart.js, 30s KPI poll |

---

## Environment (Windows + Herd)

**Git Bash:** `php` is not on PATH by default. Either:

```bash
# One-time per terminal (from repo root)
export PATH="/d/laravel13.x/bin:$PATH"
php -v
php artisan test
```

Or full paths:

```bash
PHP=/c/Users/vitou/.config/herd/bin/php.bat
COMPOSER="/c/Users/vitou/.config/herd/bin/php.bat /c/ProgramData/ComposerSetup/bin/composer.phar"
```

**Permanent (recommended):** add to `~/.bashrc`:

```bash
export PATH="/d/laravel13.x/bin:$PATH"
```

Full troubleshooting: **`docs/WINDOWS_HERD_GITBASH.md`**. **Lessons / pitfalls:** **`docs/EXAMPLE_DEV_LESSONS.md`**. Cursor rule: `.cursor/rules/windows-herd-gitbash.mdc`.

```bash
# Spec-Kit on Windows
export PYTHONIOENCODING=utf-8
```

Verify both apps:

```bash
cd d:/laravel13.x/examples/kindly-login-1122 && $PHP artisan test
cd d:/laravel13.x/examples/kindly-e-commerce-1122 && $PHP artisan test
cd d:/laravel13.x/examples/booking-v1 && $PHP artisan test
cd d:/laravel13.x/examples/clone-the-fb-nav && $PHP artisan test
cd d:/laravel13.x/examples/dashboard-v1 && $PHP artisan test
```

---

## `examples/kindly-login-1122`

**What it is:** Laravel 13 + Breeze Blade — register, login, logout, dashboard “Kindly Login”.

**Spec-Kit:** `.specify/specs/001-kindly-login/` (`spec.md` status: MVP complete)

**Key tests:** `tests/Feature/Auth/*`, `LoginSecurityTest`, `KindlyLoginBrandingTest`, `SessionSecurityTest`

**Arena**

- Prompt A + B (Sonnet 4.6): https://arena.ai/c/019e83a3-aa87-7557-bbe4-face33a778ca — `docs/ARENA_REVIEW_SPEC.md`, `docs/ARENA_REVIEW_PLAN.md`
- `docs/ARENA_LOOP.md` — prompts + mode defaults
- Browser proof: `docs/BROWSER_VERIFICATION.md` (port 8011)

**Do not redo:** Breeze scaffold, Arena paste, or browser smoke unless auth/session routes change.

**Post-MVP only:** OAuth, 2FA, Sanctum API, mandatory email verify, OpenSpec.

**Detail file:** `examples/kindly-login-1122/docs/NEXT_SESSION.md`

---

## `examples/kindly-e-commerce-1122`

**What it is:** Laravel 13 + Breeze — catalog, session cart, Stripe Checkout (test), webhook-finalized `paid`, order history, coupons, admin CRUD.

**Spec-Kit:** `.specify/specs/001-kindly-ecommerce/` (MVP); `.specify/specs/003-stripe-checkout/` (Phase 3a done)

**Key tests:** `CheckoutTest`, `StripeCheckoutTest`, `StripeWebhookTest`, `StripeCheckoutExpiredTest`, `CouponTest`, `AdminProductTest`

**Arena:** Phase 3 roadmap `docs/ARENA_DEEP_REVIEW_PHASE3.md`; Stripe 3a `docs/ARENA_REVIEW_STRIPE_PHASE3A.md`

**Browser:** `docs/BROWSER_VERIFICATION.md` — http://127.0.0.1:8012 (cart UI; live Stripe needs `.env` + `stripe listen`)

**Do not redo:** Breeze scaffold, Phase 2 coupons/admin, Stripe 3a wiring, stub pay route.

**Post-MVP only:** Phase 3b lifecycle email/shipped, multi-vendor, OpenSpec change orders.

**Detail file:** `examples/kindly-e-commerce-1122/docs/NEXT_SESSION.md`

---

## `examples/booking-v1`

**What it is:** JSON API — provider setup, slots, hold/confirm/cancel, reminders, expired-hold scheduler.

**Spec-Kit:** `.specify/specs/001-appointment-booking/` — **all tasks T001–T020 checked**

**API auth (Sanctum)**

```
POST /api/register | /api/login  → data.token
Authorization: Bearer {token}
POST /api/logout
```

**Protected routes:** `routes/api.php` — middleware `auth:sanctum`

**Policies:** `BookingPolicy` — confirm/cancel owner only

**Jobs / schedule:** `SendBookingReminder` on confirm; `bookings:release-expired-holds` every minute in `bootstrap/app.php`

**Tests:** `AppointmentBookingTest`, `SendBookingReminderTest`, `ApiTokenAuthTest`, `BookingPolicyTest`

**Grok:** `docs/GROK_REVIEW_SPEC.md`, `docs/GROK_REVIEW_PLAN.md`, loop `docs/GROK_LOOP.md` — Prompt C not run

**Do not redo:** Sanctum install, reminder job, T003, policy on confirm/cancel, provider TZ on cancel.

**Post-MVP only:** Grok Prompt C, Filament admin, OpenSpec features, prod cron/`schedule:work`.

**Detail file:** `examples/booking-v1/docs/NEXT_SESSION.md`

---

## Default next work (if user says “continue”)

1. **kindly-e-commerce:** Phase **3a Stripe done** (49/49). Next: **3b order lifecycle** or OpenSpec — see `examples/kindly-e-commerce-1122/docs/NEXT_SESSION.md`. Autonomous loop OK until blocker (Arena login, missing Stripe keys for live E2E).

2. **kindly-login:** Arena/browser complete — no work unless auth routes change.

3. **booking-v1:** Grok **Prompt C** (`docs/GROK_LOOP.md`) → merge into `tasks.md`; or start **OpenSpec** change for Filament provider UI.

4. **clone-the-fb-nav:** MVP done — pixel polish or OpenSpec only if user asks.

5. **dashboard-v1:** Livewire polling done (40/40) — Filament admin next.

6. **New example scaffold:** `./bin/new-example <slug> "Name"` → `docs/NEW_EXAMPLE_SCAFFOLD.md`

7. **Do not** start another greenfield app without explicit user pick from 180+ catalog (or `new-example`).

---

## `examples/clone-the-fb-nav`

**What it is:** Laravel 13 + Blade + Tailwind 4 — educational clone of Facebook desktop top navigation (dark bar).

**Spec-Kit:** `.specify/specs/001-fb-top-nav/` (MVP complete)

**Key tests:** `tests/Feature/FbTopNavTest.php` (nav structure, active tab, a11y labels)

**Reference:** `docs/reference/fb-desktop-top-nav.png`

**Do not redo:** Spec-Kit init, five tab routes, route-based `aria-current`.

**Post-MVP only:** Icon pixel tuning, responsive tab strip, OpenSpec changes.

**Detail file:** `examples/clone-the-fb-nav/docs/NEXT_SESSION.md`

---

## `examples/dashboard-v1`

**What it is:** Laravel 13 + Breeze — authenticated analytics dashboard with four KPI cards and recent orders table backed by the `orders` table.

**OpenSpec:** `add-order-metrics`, `add-dashboard-charts`, `add-livewire-polling` — archive when ready

**Browser:** http://dashboard-v1.test — KPIs poll every 30s; charts on first load

**Post-MVP only:** Filament admin for orders.

**Detail file:** `examples/dashboard-v1/docs/NEXT_SESSION.md`

---

## Agent transcript (full chat)

`C:\Users\vitou\.cursor\projects\d-laravel13-x/agent-transcripts/b7d613e8-d8c2-4ba4-b7ca-c5b437273cc8/b7d613e8-d8c2-4ba4-b7ca-c5b437273cc8.jsonl`

---

## Related docs (parent repo)

- `LARAVEL_SPECIALIST_CAPABILITIES.md`
- `docs/study/180-laravel-project-types-study-packet.md`
- `docs/guides/ui-adoption-workflow/` — UI/GitHub design adoption (8-principle study, impeccable context, pro runbook)
- `docs/CURSOR_SKILLS_SYNC.md` — Spec-Kit / OpenSpec / Superpowers skills on any PC (same Cursor account)
- Skill: Spec-Kit + Superpowers (not Spec-Kit + OpenSpec together at init)
