# Billing SaaS — Design Spec

**Date:** 2026-05-31
**App:** `examples/billing-saas`
**Status:** Approved

---

## Overview

Link Tracker SaaS with Stripe subscription billing. Teams create short URLs, track clicks, and subscribe to plans that control link limits, analytics retention, and seat count.

Showcases: Laravel Cashier, Stripe webhooks, usage metering, multi-tenant scoping, plan enforcement, queued jobs.

---

## Stack

- Laravel 13 + Breeze (Blade)
- Laravel Cashier Stripe (`laravel/cashier-stripe`)
- SQLite (dev) / MySQL (prod)
- Redis queue (prod) / database queue (dev)
- Pest for testing

---

## Subscription Tiers

| Plan     | Links       | Analytics Retention | Seats |
|----------|-------------|---------------------|-------|
| Free     | 10          | 7 days              | 1     |
| Pro      | 500         | 90 days             | 5     |
| Business | Unlimited   | 365 days            | 20    |

---

## Data Models

### User
- Billable via Cashier (`HasFactory`, `Billable`)
- Belongs to many Teams via `team_user` pivot (role: `owner` | `member`)

### Team
- Workspace that owns the subscription
- Has many Links, many Users

### Link
- `team_id`, `slug` (unique), `original_url`, `title`, `active`
- Has many Clicks

### Click
- `link_id`, `ip_hash`, `country`, `referrer`, `user_agent`, `clicked_at`
- Purged by scheduler per plan retention window

### UsageRecord
- `team_id`, `metric` (e.g. `clicks`), `value`, `period` (YYYY-MM)
- Synced to Stripe Meters API hourly

---

## Core Invariants

- Link count checked before creation — `PlanLimitService` is single source of truth
- Click data purged daily past plan's retention window
- Subscription cancel → 3-day grace period → access revoked, downgrade to Free
- Stripe webhook handlers idempotent — check `stripe_event_id`, skip duplicates
- `ProductVariant` stock / plan limit cannot go negative
- Vendor (team) cannot see another team's data — global scope on Link/Click

---

## HTTP Routes

```
POST /register                    → Breeze auth
GET  /dashboard                   → team switcher + usage widget
GET  /links                       → paginated links (team-scoped)
POST /links                       → CreateLinkService (enforces plan limit)
DELETE /links/{link}              → soft delete
GET  /links/{link}/stats          → click analytics (respects retention window)
GET  /r/{slug}                    → public redirect + RecordClickJob dispatch
POST /billing/subscribe           → Stripe Checkout session
POST /billing/portal              → Stripe Customer Portal redirect
POST /billing/webhook             → signed, idempotent webhook handler
```

---

## Service Layer

### CreateLinkService
- Checks `PlanLimitService::canCreateLink($team)`
- Generates unique slug (retry on collision)
- Creates Link, fires `LinkCreated` event

### RecordClickService (queued job)
- Stores Click record
- Increments `UsageRecord` for current period
- Retries: 3×, backoff: 60s

### BillingService
- Wraps Cashier: `subscribe()`, `swap()`, `cancel()`, `resume()`
- Creates Stripe Checkout sessions and Customer Portal sessions

### PlanLimitService
- `canCreateLink(Team $team): bool`
- `linkLimit(Team $team): int|null`
- `analyticsRetentionDays(Team $team): int`
- `seatLimit(Team $team): int`

---

## Webhook Handlers

Extend Cashier's `WebhookController`:

| Event | Action |
|-------|--------|
| `customer.subscription.updated` | Sync local subscription state |
| `customer.subscription.deleted` | Downgrade team to Free |
| `invoice.payment_failed` | Notify owner, set grace period flag |
| `invoice.payment_succeeded` | Extend subscription, clear grace flag |

All handlers: check `processed_webhook_events` table for duplicate `stripe_event_id` before acting.

---

## Middleware

- `EnsureTeamSubscribed` — blocks link creation if subscription lapsed past grace period
- `ScopedToTeam` — resolves active team from session, applies global scope to Link/Click queries

---

## Scheduled Jobs

| Schedule | Job | Purpose |
|----------|-----|---------|
| Daily | `PurgeExpiredClicksJob` | Delete clicks past team's retention window |
| Hourly | `SyncUsageToStripeJob` | Report metered click usage to Stripe Meters API |

---

## Error Handling

| Scenario | Response |
|----------|----------|
| Plan limit exceeded | `PlanLimitExceededException` → 422 + upgrade prompt |
| Invalid webhook signature | 400, logged, no retry |
| Duplicate webhook event | 200, skipped silently |
| Payment failed | `PaymentFailed` event → mail + DB notification to owner |
| Click recording failure | Job retries 3×, dead-letters to `failed_jobs` |
| Subscription lapsed past grace | `EnsureTeamSubscribed` middleware → redirect to billing |

---

## Testing Plan

| Test file | Coverage |
|-----------|----------|
| `Feature/CreateLinkTest` | Plan limit enforcement, slug uniqueness, collision retry |
| `Feature/RecordClickTest` | Queued correctly, stored, purge respects retention |
| `Feature/BillingTest` | Subscribe, upgrade, cancel, resume (Stripe faked) |
| `Feature/WebhookTest` | Idempotency, signature verification, all state transitions |
| `Feature/PlanLimitTest` | All three tiers, exactly-at-limit edge cases |
| `Unit/PlanLimitServiceTest` | Pure unit, no DB |
| `Unit/SlugGeneratorTest` | Collision handling |

Target: >85% coverage. Stripe interactions use `Http::fake()` + fixture payloads from `tests/fixtures/stripe/`.

---

## Directory Layout

```
examples/billing-saas/
├── app/
│   ├── Enums/PlanTier.php
│   ├── Events/LinkCreated.php
│   ├── Events/PaymentFailed.php
│   ├── Exceptions/PlanLimitExceededException.php
│   ├── Http/Controllers/
│   │   ├── LinkController.php
│   │   ├── RedirectController.php
│   │   └── Billing/
│   │       ├── CheckoutController.php
│   │       ├── PortalController.php
│   │       └── WebhookController.php
│   ├── Http/Middleware/
│   │   ├── EnsureTeamSubscribed.php
│   │   └── ScopedToTeam.php
│   ├── Jobs/
│   │   ├── RecordClickJob.php
│   │   ├── PurgeExpiredClicksJob.php
│   │   └── SyncUsageToStripeJob.php
│   ├── Models/
│   │   ├── Team.php
│   │   ├── Link.php
│   │   ├── Click.php
│   │   └── UsageRecord.php
│   └── Services/
│       ├── BillingService.php
│       ├── CreateLinkService.php
│       ├── PlanLimitService.php
│       └── RecordClickService.php
├── database/migrations/
├── routes/web.php
├── tests/
│   ├── Feature/
│   └── Unit/
└── tests/fixtures/stripe/
```

---

## Setup Commands

```bash
cd examples/billing-saas
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan cashier:install
# set STRIPE_KEY, STRIPE_SECRET, STRIPE_WEBHOOK_SECRET in .env
npm install && npm run build
php artisan serve
```
