# Tasks: General Appointment Booking

**Input**: [spec.md](./spec.md) + [plan.md](./plan.md)

## Phase 1 — Setup

- [x] T001 Create Laravel 13 app in `examples/booking-v1` (composer), configure SQLite `.env`
- [x] T002 Add `UserRole` enum on users (customer, provider)
- [x] T003 Laravel Sanctum API tokens (`/api/register`, `/api/login`, `auth:sanctum`)

## Phase 2 — Foundational

- [x] T004 Migrations: provider_profiles, bookable_resources, services, availability_rules, bookings
- [x] T005 Models + factories + relationships
- [x] T006 Provider onboarding route + policy scaffolds

## Phase 3 — User Story 1 (Provider publishes)

- [x] T007 [P1] Feature test: provider creates service + availability → slots exist
- [x] T008 [P1] Implement `AvailabilityRule` CRUD + `SlotCalculator` service
- [x] T009 [P1] GET `/services/{service}/slots` endpoint

## Phase 4 — User Story 2 (Customer books)

- [x] T010 [P1] Feature test: confirm booking removes slot from availability
- [x] T011 [P1] Feature test: second hold on same slot fails
- [x] T012 [P1] `BookingService::confirm` with transaction + overlap query
- [x] T013 [P1] POST hold/confirm routes + customer `GET /my/bookings`

## Phase 5 — User Story 3 (Holds)

- [x] T014 [P2] Feature test: hold blocks second confirm; expiry releases
- [x] T015 [P2] `BookingService::hold` + `ReleaseExpiredHolds` artisan command (scheduled)

## Phase 6 — User Story 4 (Cancel)

- [x] T016 [P2] Feature test: cancel within policy frees slot; late cancel fails
- [x] T017 [P2] `BookingService::cancel` with configurable cutoff hours

## Phase 7 — User Story 5 (Reminders)

- [x] T018 [P3] `SendBookingReminder` job + test with `Bus::fake` or log assertion

## Phase 8 — Polish

- [x] T019 Run full `php artisan test`; fix Pint on `app/` and `tests/`
- [x] T020 Update `.cursor/rules/specify-rules.mdc` with plan summary (agent context)

## Dependencies

- T004 blocks T007–T018  
- T007 before T010  
- T012 before T014  

## Parallel opportunities

- T007 and T010 tests can be written in parallel (both fail first)  
- Policies (T006) parallel with T005 after migrations  
