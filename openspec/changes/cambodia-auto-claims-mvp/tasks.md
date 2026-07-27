## 1. Foundation & tenancy

- [x] 1.1 Add `tenants` migration + Tenant model (name, default_currency, settings)
- [x] 1.2 Extend `users`: tenant_id, phone, locale (default `km`), role (policyholder/adjuster/finance/insurer_admin)
- [x] 1.3 Add a global tenant Eloquent scope + trait `BelongsToTenant`; bind current tenant from authenticated user
- [x] 1.4 Seed one launch Tenant + roles; factory support for tenants/users
- [x] 1.5 Configure Passport clients + token guard for API; policyholder vs adjuster token scopes

## 2. Policies

- [x] 2.1 `policies` migration + Policy model (policy_number, vehicle info, coverage, currency, active window, status)
- [x] 2.2 Policy factory + seeder; relation Policy → policyholder User and Tenant
- [x] 2.3 Query: active policies for a policyholder (used by intake validation)

## 3. Claim intake (capability: claim-intake)

- [ ] 3.1 `claims` migration + Claim model with status enum + guarded state-machine transitions (design D4)
- [ ] 3.2 API: create draft, update draft, submit FNOL (validate active policy ownership)
- [ ] 3.3 Generate human-friendly claim reference on submit
- [ ] 3.4 Tests for intake scenarios (valid submit, no active policy, draft resume)

## 4. Claim documents (capability: claim-documents)

- [ ] 4.1 `claim_documents` migration + ClaimDocument model (type, path, mime, size, content_hash, captured_at, geo, duplicate_suspected)
- [ ] 4.2 Filesystem config: `s3` (prod) / `local` (dev); upload endpoint with size + mime validation
- [ ] 4.3 Idempotency-key handling for retry-safe uploads; server-side downscale
- [ ] 4.4 Content-hash duplicate detection → set `duplicate_suspected`
- [ ] 4.5 Tests: upload, reject oversized/unsupported, retry idempotency, duplicate-hash flag

## 5. Claim review (capability: claim-review)

- [ ] 5.1 Adjuster queue endpoint (tenant-scoped, filter by status, sort by age)
- [ ] 5.2 Claim detail (incident + documents) for adjusters
- [ ] 5.3 Actions: needs_more_info (with note), approve, reject (require reason)
- [ ] 5.4 `claim_status_events` migration + record actor/timestamp on every transition (audit trail)
- [ ] 5.5 Tests: queue tenant isolation, request-more-info, approve, reject-requires-reason

## 6. Claim status & notifications (capability: claim-status)

- [ ] 6.1 Policyholder claim timeline endpoint (own claims only) from claim_status_events
- [ ] 6.2 Localized notifications (km/en) on submitted, needs_more_info, approved, rejected, paid
- [ ] 6.3 Tests: timeline ordering, access control (only own claims), notification locale

## 7. Payout tracking (capability: payout-tracking)

- [ ] 7.1 `payouts` migration + Payout model (amount_minor, currency, rail, external_reference, status)
- [ ] 7.2 Create payout only for approved claim; mark-paid transitions claim → paid + notify
- [ ] 7.3 `PayoutGateway` interface stub (record-only impl) to isolate future rail integration
- [ ] 7.4 Tests: block payout for non-approved, create pending, mark paid → claim paid

## 8. Bilingual layer & mobile-first UX

- [ ] 8.1 `lang/km` + `lang/en` strings for forms, statuses, validation, notifications
- [ ] 8.2 Per-user locale switch; Khmer default; verify Khmer Unicode rendering on low-end Android (QA)
- [ ] 8.3 Mobile-first client for FNOL + upload + status timeline (Blade/Inertia — decide at build)

## 9. Hardening & verification

- [ ] 9.1 Cross-tenant read/write denial tests (tenant-isolation spec scenarios)
- [ ] 9.2 Policies/gates for policyholder vs adjuster vs finance
- [ ] 9.3 `php artisan test` green; `pint` clean
- [ ] 9.4 Seed demo data + document local run in README (verify commands before writing — team norm)

## 10. Blocked-pending-confirmation (do NOT build until answered)

- [ ] 10.1 Payment-rail execution integration — blocked on A4 (ABA/Wing/Bakong choice)
- [ ] 10.2 Regulator retention / e-signature / reporting rules — blocked on A6 (IRC)
- [ ] 10.3 KHR-only vs KHR+USD final decision — blocked on A3
