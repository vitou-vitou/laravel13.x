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
| Session handoff | **Git** (`SESSION_STATE.md`, `NEXT_SESSION.md`) — canonical; **Notion** optional for tasks/notes via Cursor plugin |
| Booking external review | **Grok** — done (`docs/GROK_REVIEW_*.md`) |
| Login external review | **Arena.ai only** — no Grok (`docs/ARENA_LOOP.md`) |
| Arena mode | **Direct** + **claude-sonnet-4-6** (not Battle, not Max) |
| **Greenfield stack** | **Do not default to Laravel** — confirm stack or wait until user says pick Laravel / `new-example` (see `.cursor/rules/greenfield-stack-choice.mdc`) |

---

## Projects at a glance

| Project | MVP | Tests | Auth | External AI |
|---------|-----|-------|------|-------------|
| `examples/kindly-login-1122` | **Complete (100%)** | 30/30 | Breeze session (web) | Arena A+B done; browser verified (`docs/BROWSER_VERIFICATION.md`) |
| `examples/kindly-e-commerce-1122` | **MVP + Phase 2 + 3a Stripe** | 49/49 | Breeze session (web) | Stripe Checkout + webhooks; stub pay removed; :8012 |
| `examples/booking-v1` | **Complete** | 15/15 | Sanctum Bearer API | Grok A+B done; Prompt C optional |
| `examples/clone-the-fb-nav` | **MVP complete** | 6/6 | None (static UI) | FB desktop top-nav study; Spec-Kit `001-fb-top-nav` |
| `examples/dashboard-v1` | **MVP complete** | 115/115 | Breeze + Filament + Reverb + Socialite | Commerce + email + Echo + Google SSO + theme modes |
| `examples/dashboard-v2` | **GitHub OAuth MVP** | 35/35 | Breeze + GitHub Socialite | Session auth + optional GitHub login |
| `examples/creator-operator-v1` | **MVP + Mode D slices 1–5** | 44/44 | Breeze session (operator + creator) | Publish log, metrics, settlement, import, billing mock, webhooks |
| `examples/uploadfile` | **MVP complete** | 13/13 | None (teaching API) | File upload/list/download/delete via vendor `Storage` APIs; Spec-Kit `001-uploadfile` |

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

**OCP (Claude Pro in any OpenAI-compatible IDE):** **`ocp/README.md`** — new PC: `ocp\install.bat` then `ocp\start.bat`; API key = any string (e.g. `ocp-local`); models: `ocp/MODEL-MAP.txt`. **Images:** `ocp\start.bat` then **`ocp-vision-bridge\start.bat`** — Cursor Base URL → `http://127.0.0.1:3457/v1` (bridge); OCP stays on `:3456`.

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
cd d:/laravel13.x/examples/dashboard-v2 && $PHP artisan test
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

5. **dashboard-v1:** Google SSO done (100/100) — optional customer receipt email.

6. **dashboard-v2:** GitHub OAuth MVP — Breeze + Socialite; set `GITHUB_CLIENT_*` for live login.

7. **creator-operator-v1:** Mode D W1–W6 **done** (44/44) — optional Track B Stripe, CLI subprocess, OpenSpec; see `examples/creator-operator-v1/docs/NEXT_SESSION.md`.

8. **New example scaffold:** only after user picks Laravel → `./bin/new-example <slug> "Name"` → `docs/NEW_EXAMPLE_SCAFFOLD.md`

9. **Do not** start another greenfield app without explicit user pick from 180+ catalog (or `new-example`).

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

**What it is:** Analytics dashboard + commerce admin — categories/products (translatable en/es), cart, payments, Spatie roles/permissions, Filament CRUD, optional Google SSO.

**Tests:** 100/100 · **OpenSpec:** archived under `openspec/changes/archive/2026-06-06-*`

**Browser:** http://dashboard-v1.test · `/cart` · `/admin`

**Post-MVP only:** Cart checkout → order + payment; archive OpenSpec changes.

**Detail file:** `examples/dashboard-v1/docs/NEXT_SESSION.md`

---

## `examples/dashboard-v2`

**What it is:** Laravel 13 + Breeze — session auth with optional GitHub OAuth (Socialite). Dashboard shows profile avatar when signed in via GitHub.

**Spec-Kit:** `.specify/specs/001-dashboard_v2/` (GitHub OAuth MVP)

**Key tests:** `tests/Feature/Auth/GitHubLoginTest.php`, Breeze auth tests

**Browser:** http://dashboard-v2.test · `/login` · `/dashboard`

**Env:** `GITHUB_CLIENT_ID`, `GITHUB_CLIENT_SECRET` (button hidden until both set)

**OAuth via ngrok:** `ngrok-traffic-policy.yml` + `trustProxies` — **not** `ngrok http http://dashboard-v2.test`. See `docs/NEXT_SESSION.md` § GitHub OAuth via ngrok and `docs/EXAMPLE_DEV_LESSONS.md`.

**Detail file:** `examples/dashboard-v2/docs/NEXT_SESSION.md`

---

## `examples/creator-operator-v1`

**What it is:** Laravel 13 + Breeze — operator/creator roles, weekly batch publish log, approvals, metrics, settlement (S÷T), TikTok JSONL import, mock billing limits, outbound webhooks.

**Spec-Kit:** `.specify/specs/001-creator_operator_v1/` (MVP complete)

**Mode D:** `docs/ROADMAP.md` — T001–T032, **6 waves complete** (Track A mock billing)

**Key tests:** `PublishLogFlowTest`, `WeeklyMetricsTest`, `MonthlySettlementTest`, `TikTokImportTest`, `OperatorBillingTest`, `IntegrationWebhookTest`, `PublishLogFieldsTest` + Breeze (44 total)

**Browser:** http://creator-operator-v1.test

**Demo:** `operator@creator-operator.local` / `creator@creator-operator.local` — password `password`

**Do not redo:** Breeze scaffold, Mode D W1–W6 slices, Spec-Kit MVP tasks.

**Post-MVP only:** Track B Stripe (`/settings/subscription`), Python CLI subprocess for `tools/tiktok-metadata`, CSV export, weekly email, interactive checklist dashboard, OpenSpec.

**Detail file:** `examples/creator-operator-v1/docs/NEXT_SESSION.md` · **UX:** `docs/DESIGN.md`

---

## Agent transcript (full chat)

`C:\Users\vitou\.cursor\projects\d-laravel13-x/agent-transcripts/b7d613e8-d8c2-4ba4-b7ca-c5b437273cc8/b7d613e8-d8c2-4ba4-b7ca-c5b437273cc8.jsonl`

---

## Related docs (parent repo)

- `LARAVEL_SPECIALIST_CAPABILITIES.md`
- `docs/study/180-laravel-project-types-study-packet.md`
- `docs/guides/ui-adoption-workflow/` — UI/GitHub design adoption (8-principle study, impeccable context, pro runbook)
- `docs/CURSOR_SKILLS_SYNC.md` — Spec-Kit / OpenSpec / Superpowers skills on any PC (same Cursor account)
- `docs/MCP_SERVERS.md` — Cursor MCP stack (playwright, context7, 21st magic, browsermcp, **Notion plugin**)
- `ocp/README.md` — OCP proxy (Claude subscription in Cursor / Cline / Continue); `install.bat` + `start.bat`
- Skill: Spec-Kit + Superpowers (not Spec-Kit + OpenSpec together at init)
