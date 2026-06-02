# Grok review — Prompt B (plan)

**Same chat:** `3f0a7a99-d5e1-435b-95b6-8081adc734b4`

## (1) Security holes

1. Missing authorization on endpoints (provider setup, hold/confirm/cancel) — enforce customer vs provider ownership.
2. Validate `starts_at`, duration, resource/service IDs on POST (no mass assignment / time injection).
3. `role` on users needs guards until Sanctum/Breeze — privilege escalation risk.
4. Cancellation must re-validate 24h cutoff server-side in provider TZ (not trust client).
5. Rate-limit slot listing and hold attempts (enumeration / spam).

## (2) Concurrency risks

1. Overlap query should use pessimistic locking (`SELECT FOR UPDATE`) on conflicting rows.
2. `releaseExpiredHolds` can race with confirm on expiring holds — run in transaction or serialize per resource.
3. Concurrent holds need atomic “hold if free” (we block second hold in tests; keep DB-level unique constraint as v2).
4. AvailabilityRule edits during active holds can skew slot views — lock edits when future bookings exist (spec updated).
5. Cancel should re-check overlaps atomically before status update.

## (3) Simplified schema (Grok suggestion — optional v2)

- Merge `provider_profiles` + first `bookable_resource` for tiny MVP.
- Inline availability on `services` as JSON.
- Use `created_at` + status for hold expiry instead of `hold_expires_at`.

**Decision:** Keep current schema for clarity; add indexes + policies + throttle in v1.1.
