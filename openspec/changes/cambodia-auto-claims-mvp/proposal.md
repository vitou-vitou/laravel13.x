## Why

Auto-insurance claims in Cambodia are slow, manual, and paper-driven: a policyholder who has an accident typically phones an agent, waits for a physical inspection, hand-carries documents, and waits weeks for a payout with little visibility. This hurts the insurer too — high adjuster cost, fraud exposure, and churn. This change proposes a **mobile-first, Khmer + English claims SaaS** that the insurer (payer) buys to let their policyholders file a claim (FNOL) from their phone, upload photos/documents, track status, and receive a decision — while giving adjusters a review console.

Scope of THIS change: an **investigation + spec proposal** (spec-first, no implementation). It defines MVP scope, non-goals, data model, and open questions that need user/insurer confirmation before build.

### Problem framing — Cambodia context (assumptions, flagged)

| # | Assumption | Confidence | Needs user confirmation? |
|---|-----------|-----------|--------------------------|
| A1 | Users are mobile-first; low desktop usage | High | No |
| A2 | Primary UI language must be **Khmer**, with English fallback | High | Confirm exact default |
| A3 | Currency is **KHR** (Riel); some policies denominated in **USD** (dual-currency economy) | High | **Confirm** whether MVP must support both KHR + USD |
| A4 | Card penetration is low; payouts land via **ABA / Wing / Bakong** rails | Medium | **CONFIRM — do not assume.** Which rails does the insurer actually use for payout? |
| A5 | Intermittent connectivity — app must tolerate slow uploads / retries | High | No |
| A6 | Regulator = Insurance Regulator of Cambodia (IRC); claims retention & reporting rules apply | Low | **CONFIRM** regulator data-retention, e-signature, and reporting requirements |
| A7 | Fraud is a real cost; MVP needs basic controls (photo metadata, duplicate detection) not ML | Medium | Confirm depth expected for MVP |
| A8 | Buyer is a single insurer initially; SaaS should be **multi-tenant capable** so more insurers can onboard later | Medium | **Confirm** single-insurer MVP vs multi-tenant from day one |

Open items A3, A4, A6, A8 are **blocking design decisions** — flagged as open questions, not assumed.

## What Changes

- **NEW** policyholder mobile-first claim intake (FNOL): report an incident, capture location/time, describe damage.
- **NEW** document & photo upload with retry-tolerant, low-bandwidth handling.
- **NEW** claim status tracking for the policyholder (submitted → under review → approved/rejected → paid).
- **NEW** adjuster review console: queue, claim detail, request-more-info, approve/reject with reason.
- **NEW** payout record + status tracking (payout **execution** via external rail is a non-goal for MVP; we record intent + mark paid).
- **NEW** bilingual (Khmer/English) content layer across UI and notifications.
- **NEW** multi-tenant foundation (per-insurer isolation) — scoped as data-model-ready even if only one tenant is live at launch.
- Leverages existing **Laravel 13 + Passport (OAuth2)** in this repo for API auth.

## Capabilities

### New Capabilities
- `claim-intake`: policyholder FNOL submission, incident detail capture, bilingual forms, draft/submit lifecycle.
- `claim-documents`: photo/document upload, low-bandwidth retry handling, per-claim document listing and metadata.
- `claim-review`: adjuster queue, claim detail review, request-more-info, approve/reject with reasons and audit trail.
- `claim-status`: policyholder-facing status timeline and notifications (Khmer/English).
- `payout-tracking`: record payout intent, currency, rail reference, and mark-paid status (no rail execution in MVP).
- `tenant-isolation`: per-insurer tenancy scoping of policies, claims, adjusters, and users.

### Modified Capabilities
<!-- No existing specs in openspec/specs/ — greenfield. -->
- (none)

## Impact

- **Code**: new domain models (Tenant/Insurer, Policy, Claim, ClaimDocument, Adjuster, Payout, User), migrations, API controllers, policies, notifications, and mobile-first Blade/inertia or API-for-mobile surface.
- **Auth**: uses existing `laravel/passport` for API tokens; role split policyholder vs adjuster.
- **Storage**: object storage for claim photos/documents (S3-compatible; local disk for dev).
- **Dependencies**: no new heavy deps required for MVP; payment-rail SDKs deferred until A4 confirmed.
- **Non-goals (MVP)**: automated fraud ML, actual payment-rail money movement, policy sales/underwriting, premium billing, offline-first sync, native app (mobile web first).
