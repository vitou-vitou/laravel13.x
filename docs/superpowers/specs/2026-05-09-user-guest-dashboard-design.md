# Design: User guest dashboard example (Laravel + Filament)

**Date:** 2026-05-09  
**Path:** `examples/user-guest-dashboard`  
**Purpose:** Teach Filament patterns (panel auth, resource scoping, widgets). Fake domain acceptable.

## Scope

- **Continue as Guest** entry: exactly **2** core features (see below).
- **Dashboard** (Filament home for guest panel): exactly **2** core features.
- Stack: Laravel + FilamentPHP, aligned with `examples/basic-laravel-filamentphp` versions where practical.
- **v1 panel:** Guest-only Filament panel — `authGuard('guest')`, Eloquent provider `guests`. Avoids dual-guard-on-one-panel complexity.
- **Out of v1:** Real registration flow, second panel for full users, email recovery, production-grade guest migration to user.

## Architecture

- New Laravel app under `examples/user-guest-dashboard`.
- **Models:** `Guest` (`id`, `uuid` string unique, `timestamps`; optional `last_seen_at` for future use). Demo resource `DemoItem` with `guest_id` FK, fields e.g. `title` (string), `body` (text, nullable).
- **Auth:** `config/auth.php` guard `guest`, provider `guests` → `Guest` model implementing `Authenticatable` (no password — use `loginUsingId` only).
- **Filament:** One panel (e.g. `GuestPanelProvider`) with `path('guest')`, `authGuard('guest')`, `login` enabled. Login view or custom page exposes **Continue as guest**.
- **Session:** After guest login, call `session()->regenerate()` to reduce fixation risk.

## Continue as Guest — core features

1. **Guest bootstrap:** Primary action **Continue as guest** creates a `Guest` row, authenticates via `Auth::guard('guest')->loginUsingId($id)`, regenerates session, redirects to panel dashboard. Flash notice: short “Guest mode (demo)” message.
2. **Limits explainer:** On same surface, static teaching block (section + bullets): guest = demo session, no account recovery; registering (when implemented) would mean cross-device persistence — copy only in v1.

## Dashboard — core features

1. **`DemoItem` resource:** Filament resource — list/create/edit/delete. Query scoped so current `Guest` sees only rows where `demo_items.guest_id = auth()->id()` (guest guard). No `user_id` required in v1; column can exist nullable for future migration story, or omit until needed (prefer **omit** until user panel exists — YAGNI).
2. **Stat widget:** Dashboard widget showing count of current guest’s `DemoItem` records, link to resource index, optional one-line CTA that registration is placeholder in this demo.

## Data flow

- Unauthenticated visit to `/guest/login` (or Filament default login path) → user reads explainer → clicks Continue as guest → POST/Action → `Guest` created → logged in → `/guest` dashboard.
- Dashboard loads widget + navigation to `DemoItem` resource.

## Error handling

- If `Guest` creation fails (DB): show Filament notification or flash error; remain on login; no partial auth.
- Unauthorized access to dashboard: Filament default redirect to guest panel login.

## Testing (minimal)

- Feature test: continue-as-guest action creates exactly one guest, asserts `Auth::guard('guest')->check()`, follows redirect to dashboard.
- Feature test: two guests each with items; when authenticated as guest A, `DemoItem` index query / HTTP response does not expose guest B’s titles.

## Non-goals

- Production legal/consent flows, rate limiting, CAPTCHA, account linking, GDPR export.
- Filament admin panel for superusers, multi-tenancy, teams.

## Self-review

- No unresolved TBDs for v1.
- Single-guest-panel decision consistent with Filament constraints and approved `guests` table.
- Feature count capped at 2 + 2 as requested.
