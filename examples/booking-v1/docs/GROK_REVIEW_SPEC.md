# Grok review — Prompt A (spec)

**Chat:** https://grok.com/c/3f0a7a99-d5e1-435b-95b6-8081adc734b4  
**Date:** 2026-06-01

## 1. Missing invariants

1. A booking must always reference a valid Service linked to the correct BookableResource owned by the ProviderProfile.
2. Customer and Provider cannot book overlapping slots on the same resource even across different Services if they share the same time window.
3. All slot times must respect the system's timezone and the Provider's configured timezone without silent conversion errors.
4. No Booking can transition from `confirmed` to `held` state after initial confirmation.
5. Booking completion status (`completed` / `no_show`) must only be set after the actual appointment datetime has passed.

## 2. Ambiguous acceptance criteria

1. Definition of "weekly availability" — recurring rules, exceptions, buffer times between slots unclear.
2. Exact mechanism and UI flow for handling concurrent booking attempts and which user receives clear feedback.
3. Whether Providers can modify AvailabilityRule after slots are listed but before any bookings.
4. Precise timing for "24h cutoff" on cancellations (appointment start time, booking creation time, or timezone specifics).
5. Requirements for slot listing when AvailabilityRule has gaps or overlaps with Service duration.

## 3. One P1 user story to cut / simplify for 1-week MVP

**Simplify (not remove):** "Provider publishes services + weekly availability" — ship basic recurring rules first; defer full availability editor, exceptions, and buffers to v2.

*(Grok also suggested exploring optimistic locking, indexing, concurrency — moved to plan backlog.)*
