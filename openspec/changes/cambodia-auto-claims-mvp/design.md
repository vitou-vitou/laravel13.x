## Context

The repo is a fresh Laravel 13.6 app (PHP 8.3) with `laravel/passport` (OAuth2) already installed — so API token auth is available out of the box. There is no existing domain code, no starter kit (no Breeze/Jetstream), only the default `welcome` view. This is greenfield.

The product is a claims SaaS bought by an auto-insurer to let Cambodian policyholders file and track claims from a phone, with an adjuster console on the insurer side. Constraints: Khmer-first bilingual, KHR (and likely USD) currency, low-bandwidth mobile users, low card penetration, and payment rails / regulator rules that are **not yet confirmed** (see Open Questions).

Stakeholders: insurer (payer/buyer), insurer's adjusters/finance, and Cambodian policyholders (end users). Build candidates: jack, tom. Review: alice.

## Goals / Non-Goals

**Goals:**
- Ship a tight MVP claim lifecycle: FNOL → documents → adjuster review → decision → payout status.
- Mobile-first, Khmer/English bilingual UX with slow-network tolerance.
- Multi-tenant-ready data model so more insurers onboard without migration.
- Lean into Laravel 13 conventions + existing Passport auth; minimal new dependencies.

**Non-Goals (MVP):**
- Real money movement through ABA/Wing/Bakong (record intent + mark paid only).
- ML fraud scoring (basic metadata + duplicate-hash flag only).
- Policy sales, underwriting, premium billing, renewals.
- Native mobile apps (mobile web / API-first; native can follow).
- Offline-first sync.

## Decisions

**D1 — Multi-tenant via a `tenant_id` column (row-level scoping), not DB-per-tenant.**
Rationale: one insurer live at launch, but onboarding more must not require migration. A `tenant_id` FK on every domain table + a global Eloquent scope (bound to the authenticated user's tenant) is the lowest-complexity path. Alternative considered: schema-per-tenant / DB-per-tenant — rejected as overkill for MVP and heavier ops.

**D2 — API-first backend with Passport; thin mobile-web frontend.**
Rationale: Passport is already installed. Expose a JSON API for claim intake/status and adjuster actions; serve a mobile-first web client (Blade + minimal JS, or Inertia — decide at build time). Alternative: session-only monolith — rejected because a future native app will want the same API. Keep the API the source of truth.

**D3 — Roles: `policyholder`, `adjuster`, `finance`, `insurer_admin`.**
Rationale: enforce with Laravel policies/gates. Policyholders see only their own claims; adjusters/finance are tenant-scoped. Keep RBAC simple (a `role` on the tenant-user pivot), no external package for MVP.

**D4 — Claim status is an explicit state machine.**
States: `draft → submitted → under_review → needs_more_info → (approved | rejected) → paid`. Rationale: transitions gate actions (e.g. payout only after `approved`) and drive notifications + timeline. Implement as guarded transition methods on the Claim model; avoid a heavy workflow package for MVP.

**D5 — Object storage for documents via Laravel filesystem (`s3` driver in prod, `local` in dev).**
Rationale: photos/docs don't belong in the DB. Store metadata (type, size, content hash, capture timestamp/geo) in `claim_documents`. Content hash enables the duplicate-suspected fraud flag (D unspecified ML). Client-side image compression + idempotency key on upload handles low bandwidth / retries.

**D6 — Bilingual via Laravel localization (`lang/km`, `lang/en`) + a per-user `locale`, default `km`.**
Rationale: native framework feature, no dependency. Notifications localized to the user's stored locale. Khmer as default per A2.

**D7 — Money stored as integer minor units + explicit `currency` (KHR/USD).**
Rationale: avoid float rounding; support dual-currency economy. Payout carries amount, currency, intended rail (string, pending confirmation), and settlement reference.

**D8 — Payout is record-only in MVP.**
No rail SDK dependency until the insurer's actual rails (A4) and regulator rules (A6) are confirmed. `payouts` table + manual mark-paid keeps us unblocked and defers integration risk.

## Data model (high level)

- **Tenant (Insurer)**: id, name, default_currency, settings.
- **User**: id, tenant_id, name, phone, email?, locale (`km`/`en`), role.
- **Policy**: id, tenant_id, policyholder_user_id, policy_number, vehicle info, coverage, currency, active_from/active_to, status.
- **Claim**: id, tenant_id, policy_id, reference, incident_type, incident_at, location, description, status, decided_by (adjuster user_id)?, decided_at?, rejection_reason?.
- **ClaimDocument**: id, tenant_id, claim_id, type, path, filename, mime, size, content_hash, captured_at?, geo?, duplicate_suspected (bool), uploaded_by.
- **ClaimStatusEvent** (timeline/audit): id, claim_id, from_status, to_status, actor_user_id, note?, created_at.
- **Payout**: id, tenant_id, claim_id, amount_minor, currency, rail?, external_reference?, status (`pending`/`paid`/`failed`), paid_at?, created_by.
- Adjuster = a User with role `adjuster` (no separate table needed unless adjuster-specific attributes emerge).

## Risks / Trade-offs

- [Unconfirmed payment rails A4] → Keep payout record-only; isolate any future rail behind a `PayoutGateway` interface so integration is additive.
- [Unconfirmed regulator/retention rules A6] → Do not hard-code retention/deletion; make document retention a config value; flag for legal review before launch.
- [Row-level multi-tenancy leak risk] → Enforce a global tenant scope + policies + tests that assert cross-tenant reads/writes fail (per tenant-isolation spec scenarios).
- [Low-bandwidth uploads] → Idempotency keys + client compression; risk of large photos → enforce size limits and server-side downscaling.
- [Dual currency confusion] → Always store currency alongside amount; never mix in aggregates without conversion (out of MVP scope — report per currency).
- [Khmer rendering/fonts on older Android] → Verify Khmer Unicode rendering during QA on low-end devices.

## Migration Plan

Greenfield — no data migration. Deploy = run migrations + seed one Tenant (the launch insurer) + seed roles. Rollback = drop the new tables (no prior schema to preserve). Onboarding a second insurer later is a data operation (new Tenant row), not a schema migration, per D1.

## Open Questions

1. **A4 — Payment rails**: Which payout rails does the insurer actually use (ABA / Wing / Bakong / bank transfer)? Blocks any future payout execution scope. **Needs user/insurer confirmation.**
2. **A3 — Currency**: Must MVP support both KHR and USD, or one? **Needs confirmation.**
3. **A6 — Regulator (IRC)**: Data-retention period, e-signature validity, and any mandatory claims reporting format? **Needs confirmation.**
4. **A8 — Tenancy**: Single insurer at launch confirmed? Any near-term second insurer? Affects how hard we push multi-tenant hardening in MVP.
5. **A2 — Default language**: Confirm Khmer as default with English toggle (vs. auto-detect).
6. **A7 — Fraud depth**: Is metadata + duplicate-hash flagging enough for MVP, or is more expected?
7. Notification channel: SMS vs in-app vs Telegram/other — what do Cambodian users expect and what can the insurer send?
