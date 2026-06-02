# booking-v1 — session resume

> **Parent handoff:** [`../../../docs/SESSION_STATE.md`](../../../docs/SESSION_STATE.md) — read that first in new chats.

**Updated:** 2026-06-01 | **MVP:** complete | **Tests:** 15/15

---

## Identity

| | |
|--|--|
| Path | `examples/booking-v1` (Laravel app at repo root of this folder) |
| Catalog | #3 Booking → **general appointment scheduling** |
| Workflow | Spec-Kit + Superpowers; **Grok** for reviews (not Arena) |
| Spec-Kit | `.specify/specs/001-appointment-booking/` |
| Tasks | **T001–T020 all [x]** including T003 Sanctum, T018 reminders |

---

## What is done (do not rebuild)

| Feature | Location |
|---------|----------|
| Migrations + models + `SlotCalculator` / `BookingService` | `app/` |
| Sanctum API auth | `POST /api/register`, `/login`, `/logout`; `auth:sanctum` on API group |
| Hold / confirm / cancel / slots / my bookings | `routes/api.php`, controllers |
| `BookingPolicy` (owner confirm/cancel) | `app/Policies/BookingPolicy.php` |
| Cancel cutoff in provider TZ | `BookingService::cancel` |
| `SendBookingReminder` job (T-24h) | `app/Jobs/SendBookingReminder.php` |
| Schedule expired holds | `bootstrap/app.php` → `bookings:release-expired-holds` every minute |
| Grok spec/plan reviews | `docs/GROK_REVIEW_*.md` (merged into spec) |

**Tests:** `tests/Feature/Booking/*`, `tests/Feature/Auth/ApiTokenAuthTest.php`

---

## API quick reference

```bash
# Auth
POST /api/register   # name, email, password, password_confirmation, role optional
POST /api/login
POST /api/logout     # Bearer required

# Booking (Bearer required)
POST /api/provider/setup
GET  /api/services/{service}/slots?from=&to=
POST /api/services/{service}/bookings/hold   # starts_at
POST /api/bookings/{id}/confirm
POST /api/bookings/{id}/cancel
GET  /api/my/bookings
```

Tests use `actingAsApi($user)` → `Sanctum::actingAs` in `tests/TestCase.php`.

---

## Post-MVP only

1. Grok **Prompt C** — `docs/GROK_LOOP.md` → audit `tasks.md`
2. Filament provider admin (plan phase 2)
3. OpenSpec change orders for new scope
4. Production: cron or `php artisan schedule:work` for hold release

---

## Commands

```bash
cd d:/laravel13.x/examples/booking-v1
/c/Users/vitou/.config/herd/bin/php.bat artisan migrate
/c/Users/vitou/.config/herd/bin/php.bat artisan test
/c/Users/vitou/.config/herd/bin/php.bat artisan bookings:release-expired-holds
```

Composer must use Herd PHP (see parent `SESSION_STATE.md`).

---

## Grok chat (signed-in)

https://grok.com/c/3f0a7a99-d5e1-435b-95b6-8081adc734b4
