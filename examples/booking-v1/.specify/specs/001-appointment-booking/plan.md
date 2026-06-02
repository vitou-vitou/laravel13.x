# Implementation Plan: General Appointment Booking

**Branch**: `001-appointment-booking` | **Date**: 2026-06-01 | **Spec**: [spec.md](./spec.md)

## Summary

Laravel 13 API-first MVP for general appointment scheduling: providers publish services and availability; customers book slots with hold/confirm flow; Pest tests lock invariants (no overlap, hold expiry, cancellation window).

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13.x  
**Primary Dependencies**: Laravel Breeze (auth), Pest, optional Filament later  
**Storage**: SQLite (dev), Eloquent  
**Testing**: Pest feature tests per user story / invariant  
**Target Platform**: Local Herd/Valet or `php artisan serve`  
**Project Type**: Web application (monolith)  
**Performance Goals**: Slot search < 200ms for 7-day window (single provider)  
**Constraints**: UTC storage, provider timezone for display; pessimistic lock on confirm  
**Scale/Scope**: MVP single-tenant businesses (each provider isolated)

## Constitution Check

- [x] Invariants in service layer + tests  
- [x] TDD required  
- [x] YAGNI — no payments  
- [x] Spec/plan separation maintained  

## Project Structure

```text
examples/booking-v1/
├── .specify/                    # Spec-Kit artifacts
├── app/
│   ├── Enums/BookingStatus.php
│   ├── Models/
│   ├── Policies/
│   └── Services/Booking/
├── database/migrations/
├── routes/web.php
├── tests/Feature/Booking/
└── docs/GROK_LOOP.md            # Optional Grok review prompts
```

## Architecture

### Layering

- **SlotCalculator** — given resource + service duration + date range → candidate starts  
- **BookingService** — hold, confirm, cancel; wraps `DB::transaction` + `lockForUpdate` on resource row or booking overlap query  
- **BookingPolicy** — customer vs provider access  

### Data model

| Table | Purpose |
|-------|---------|
| users | auth + role enum |
| provider_profiles | user_id, business_name, timezone |
| bookable_resources | provider_profile_id, name |
| services | provider_profile_id, name, duration_minutes |
| availability_rules | bookable_resource_id, day_of_week, start_time, end_time |
| bookings | resource_id, service_id, customer_id, starts_at, ends_at, status, cancelled_at |
| booking_holds | booking_id or slot fields + expires_at (or status=held on bookings) |

Overlap check: `starts_at < other.ends_at AND ends_at > other.starts_at` for statuses `confirmed` and active `held`.

### API / Routes (MVP)

| Method | Path | Action |
|--------|------|--------|
| GET | /provider/services | list/create services (auth provider) |
| POST | /provider/availability | set rules |
| GET | /services/{service}/slots | public/customer slot list |
| POST | /bookings/hold | create hold |
| POST | /bookings/{id}/confirm | confirm |
| POST | /bookings/{id}/cancel | cancel |
| GET | /my/bookings | customer list |

Web UI: minimal Blade pages or JSON-only for first slice; Breeze auth scaffolding.

### Concurrency

On confirm: transaction + query existing overlapping confirmed/held (non-expired) + insert.

### Reminders

`SendBookingReminder` job dispatched on confirm; `schedule` every minute to release expired holds.

## Grok integration

Reviews captured in `docs/GROK_REVIEW_SPEC.md` and `docs/GROK_REVIEW_PLAN.md` (Cursor browser, signed-in session).

### Post-review backlog (from Grok)

- [ ] Policies: provider-only setup; customer-only cancel own booking; provider sees own calendar
- [ ] API throttle + Form Request validation on all POST bodies
- [ ] Breeze or Sanctum for real auth (tests use `actingAs` today)
- [ ] DB: composite index `(bookable_resource_id, starts_at, ends_at)`; consider exclusion constraint v2
- [ ] Confirm: lock overlapping booking rows `FOR UPDATE` (partially done via `lockForUpdate` on booking)
- [ ] Cancellation cutoff uses provider TZ (implement in `BookingService::cancel`)

## Phase 0 — Research

- Use hospital appointment lifecycle from capabilities doc as pattern for status enum only.  
- No external APIs.

## Phase 1 — Delivery order

1. Auth + roles  
2. Provider profile, resource, service, availability CRUD  
3. SlotCalculator + tests  
4. Hold + confirm + overlap tests  
5. Cancel policy + tests  
6. Holds expiry command + reminder job  
