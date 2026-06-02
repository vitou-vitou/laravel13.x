# Feature Specification: General Appointment Booking

**Feature Branch**: `001-appointment-booking`

**Created**: 2026-06-01

**Status**: Draft

**Input**: Greenfield booking & reservation system (catalog #125 — general appointment scheduling). Source decomposition: `LARAVEL_SPECIALIST_CAPABILITIES.md` (hospital appointment lifecycle pattern, simplified).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Provider publishes bookable services (Priority: P1)

A business owner (provider) registers, defines one or more **services** (name, duration in minutes), and sets **weekly availability** (e.g. Mon–Fri 09:00–17:00) for a **resource** (the provider themselves or a room).

**Why this priority**: Without services and availability, customers cannot book.

**Independent Test**: Provider can create a service and availability; system exposes at least one bookable slot in the future.

**Acceptance Scenarios**:

1. **Given** a registered provider, **When** they create a 30-minute service and set weekday availability, **Then** the system lists available start times for the next 7 days.
2. **Given** a service duration of 60 minutes, **When** availability ends at 17:00, **Then** the last bookable slot starts at 16:00.

---

### User Story 2 - Customer books an available slot (Priority: P1)

A customer selects a service, picks an open slot, and receives a **confirmed** booking.

**Why this priority**: Core product value.

**Independent Test**: Customer completes booking; provider sees it on their schedule; slot no longer offered to others.

**Acceptance Scenarios**:

1. **Given** an open slot, **When** customer confirms booking, **Then** booking status is `confirmed` and the slot is unavailable to others.
2. **Given** two customers attempt the same slot concurrently, **When** both submit, **Then** exactly one receives confirmation and the other receives a clear conflict error.

---

### User Story 3 - Temporary hold before confirm (Priority: P2)

While checkout, a slot can be **held** for a short window (e.g. 10 minutes) so another user cannot take it during payment-less confirmation.

**Why this priority**: Prevents double-book race without requiring payment.

**Independent Test**: Hold blocks second booking; expired hold releases slot.

**Acceptance Scenarios**:

1. **Given** an open slot, **When** customer starts hold, **Then** another customer cannot confirm the same slot until hold expires or completes.
2. **Given** an active hold past expiry, **When** scheduler runs, **Then** hold is released and slot is bookable again.

---

### User Story 4 - Cancel with policy (Priority: P2)

Customer or provider can cancel a confirmed booking if outside the cancellation cutoff (e.g. 24 hours before start).

**Why this priority**: Real operations require policy, not hard deletes.

**Independent Test**: Cancellation within policy frees slot; late cancellation is rejected or flagged per rules.

**Acceptance Scenarios**:

1. **Given** a booking 48 hours in the future, **When** customer cancels, **Then** status is `cancelled` and slot reopens.
2. **Given** a booking 2 hours in the future and 24h cutoff, **When** customer cancels, **Then** system rejects with reason.

---

### User Story 5 - Reminders (Priority: P3)

System queues email (or log in dev) reminder before appointment.

**Why this priority**: Operational value after core booking works.

**Independent Test**: Creating a confirmed booking schedules a reminder job for T-24h (or configurable).

**Acceptance Scenarios**:

1. **Given** confirmed booking tomorrow, **When** reminder job runs, **Then** notification is sent once.

### Edge Cases

- Booking in the past → rejected.
- Provider edits availability → existing confirmed bookings unchanged; new slots follow new rules; **v1:** provider may edit rules only when no future confirmed bookings exist on affected resource (otherwise reject).
- Provider timezone vs UTC storage → all `starts_at`/`ends_at` stored UTC; display and slot boundaries use `ProviderProfile.timezone` (no silent offset bugs).
- Service deleted with future bookings → block delete if future confirmed/held bookings exist.
- **Cross-service overlap:** same resource cannot have overlapping confirmed/held windows for any service (shared time window).
- **State guards:** `confirmed` must never return to `held`; `completed` / `no_show` only after `starts_at` has passed.
- **Referential integrity:** every booking’s `service_id` must belong to the same provider as the `bookable_resource_id`.

### Clarifications (from review)

- **Weekly availability (v1):** recurring `day_of_week` + `start_time`/`end_time` only; no exceptions, buffers, or blackout dates in v1.
- **Concurrent attempts:** second hold on same slot fails with explicit error (not two holds then race on confirm).
- **Cancellation cutoff:** measured from **appointment `starts_at`** in provider timezone, converted to UTC for comparison.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST authenticate users as `customer` or `provider` (role).
- **FR-002**: Providers MUST manage services, resources, and availability rules.
- **FR-003**: System MUST compute available slots from availability minus confirmed bookings and active holds.
- **FR-004**: System MUST prevent overlapping confirmed or active held bookings for the same resource (any service).
- **FR-008**: System MUST store instants in UTC and compute policy boundaries using provider timezone.
- **FR-009**: System MUST reject illegal status transitions (`confirmed` → `held`; early `completed`/`no_show`).
- **FR-005**: System MUST support booking lifecycle: `held` → `confirmed` | `expired`, `confirmed` → `cancelled` | `completed` | `no_show`.
- **FR-006**: Customers MUST only view and cancel their own bookings.
- **FR-007**: Providers MUST only manage their own services, resources, and bookings.

### Key Entities *(conceptual)*

- **User** (role: customer | provider)
- **ProviderProfile** (business display, timezone)
- **BookableResource** (person, room, equipment)
- **Service** (name, duration_minutes, provider)
- **AvailabilityRule** (day_of_week, start_time, end_time, resource)
- **Booking** (resource, service, customer, starts_at, ends_at, status)
- **BookingHold** (optional row or status with expires_at)

## Success Criteria

- **SC-001**: A new developer can run tests and see green for overlap, hold expiry, and cancellation policy.
- **SC-002**: P1 stories completable in one vertical demo (provider setup → customer books → provider sees booking).

## Out of Scope (v1)

- Payments, deposits, invoices
- Multi-location enterprise, staff pools, recurring series
- Google/Outlook calendar sync
- SMS (email/log only acceptable for MVP)
