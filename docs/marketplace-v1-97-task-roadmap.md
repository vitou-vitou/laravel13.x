# Multi-vendor marketplace — 97-task / 3-month / ~10-hour roadmap

> **Source:** `LARAVEL_SPECIALIST_CAPABILITIES.md` → E-Commerce → **Multi-vendor marketplace**  
> **Greenfield slug:** `marketplace-v1`  
> **Reference (do not re-scaffold):** `examples/kindly-e-commerce-1122` (single-vendor Stripe patterns)

---

## Project pick

From **180+ Buildable Project Types**:

> **Multi-vendor marketplace** — Customer buys; multiple Vendors sell; platform takes commission; `Order` splits into per-vendor `OrderGroup`s; Stripe payouts; disputes.

Aligns with the live decomposition in `LARAVEL_SPECIALIST_CAPABILITIES.md` (Customer / Vendor / Admin, invariants, domain events).

**Stack (Laravel Specialist):** Laravel 12, Breeze, Sanctum, Eloquent, Stripe Connect + webhooks, queues, Scout, Pest (>85%), Filament or Blade admin, git worktrees per slice.

---

## 3-month roadmap (12 weeks)

| Phase | Weeks | Outcome |
|-------|-------|---------|
| **A — Domain foundation** | 1–2 | Migrations, models, factories, tenant scopes, demo seed |
| **B — Identity & vendors** | 3 | RBAC, vendor onboarding, approval, payout profile |
| **C — Catalog** | 4 | Vendor products, variants, stock, public catalog |
| **D — Cart** | 5 | Add/update/remove, totals preview, abandoned cart |
| **E — Checkout & orders** | 6 | Multi-vendor checkout, price snapshots, commission lock |
| **F — Payments** | 7 | Stripe Checkout + webhooks, payment audit |
| **G — Fulfillment** | 8 | Vendor confirm/ship, customer tracking, emails |
| **H — Payouts** | 9 | Hold timer, Connect transfers, dispute freeze |
| **I — Trust** | 10 | Reviews, disputes, admin resolution |
| **J — Platform admin** | 11 | Commission rates, vendor suspend, audit log |
| **K — Scale & ship** | 12 | Scout search, GDPR, CI, docs, full test green |

**Calendar:** 12 weeks if built sequentially.  
**Claude Code:** same 97 tasks in **10 waves × ~1 hour** (4–5 Agent Team mates or Dynamic Workflow batches).

---

## Domain invariants (never violate)

- Order total = sum of all OrderGroup totals
- Commission locked at order time (on OrderGroup)
- Vendor cannot fulfill another Vendor's OrderGroup
- Payout cannot release while Dispute is open on that OrderGroup
- Refund cannot exceed original Payment amount
- ProductVariant stock cannot go negative
- Customer cannot review a Product they didn't purchase
- OrderLine price is snapshot — immutable after order placed
- Cart cannot checkout with out-of-stock variants

---

## 97-task assignment portal

Columns: **ID | Slice | Agent role | Week | Deps | Branch | Definition of done**

### Weeks 1–2 — Foundation (T001–T020)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T001 | Scaffold `marketplace-v1` + Breeze + Herd | bootstrap | 1 | — | `feat/T001-scaffold` | App loads at `.test`, Breeze auth works |
| T002 | Pest + baseline test green | tester | 1 | T001 | `feat/T002-pest` | `artisan test` passes |
| T003 | User role enum (customer/vendor/admin) | backend | 1 | T001 | `feat/T003-roles` | Migration + cast on User |
| T004 | Vendor model + migration | backend | 1 | T003 | `feat/T004-vendor` | Vendor belongsTo User |
| T005 | Category model + migration | backend | 1 | T001 | `feat/T005-category` | Nested or flat categories |
| T006 | Product model + migration (vendor_id, status) | backend | 1 | T004,T005 | `feat/T006-product` | draft/active/suspended states |
| T007 | ProductVariant + stock_qty | backend | 1 | T006 | `feat/T007-variant` | SKU unique per variant |
| T008 | Cart + CartLine migrations | backend | 2 | T003,T007 | `feat/T008-cart` | Cart per customer session/user |
| T009 | Order + OrderGroup + OrderLine | backend | 2 | T006 | `feat/T009-order` | OrderGroup has vendor_id |
| T010 | Payment migration + states | backend | 2 | T009 | `feat/T010-payment` | pending→completed/failed/refunded |

### Week 2 — Remaining domain (T011–T020)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T011 | Payout migration + states | backend | 2 | T009 | `feat/T011-payout` | pending→completed/failed |
| T012 | Review migration | backend | 2 | T006,T009 | `feat/T012-review` | product_id, customer_id, rating |
| T013 | Dispute + DisputeMessage | backend | 2 | T009 | `feat/T013-dispute` | ties to OrderGroup |
| T014 | Platform commission config | backend | 2 | T001 | `feat/T014-commission` | Config table or settings |
| T015 | Factories User/Vendor/Product | tester | 2 | T006 | `feat/T015-factories-a` | Factory states usable in tests |
| T016 | Factories Order chain/Payment | tester | 2 | T010 | `feat/T016-factories-b` | Can create paid order in test |
| T017 | Eloquent relations Product→Variant | backend | 2 | T007 | `feat/T017-relations-a` | HasMany + scopes |
| T018 | Order aggregate relations | backend | 2 | T009 | `feat/T018-relations-b` | Order→groups→lines |
| T019 | VendorTenantScope global scope | backend | 2 | T004 | `feat/T019-tenant-scope` | Vendor queries auto-filtered |
| T020 | DatabaseSeeder demo marketplace | backend | 2 | T016 | `feat/T020-seed` | 2 vendors, 10 products, 1 admin |

### Week 3 — Auth & vendor onboarding (T021–T028)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T021 | ProductPolicy vendor ownership | backend | 3 | T006 | `feat/T021-policy-product` | Vendor edits own only |
| T022 | OrderGroupPolicy isolation | backend | 3 | T009 | `feat/T022-policy-order` | Cross-vendor 403 |
| T023 | EnsureVendor middleware | backend | 3 | T003 | `feat/T023-mw-vendor` | Routes protected |
| T024 | EnsureAdmin middleware | backend | 3 | T003 | `feat/T024-mw-admin` | Admin routes protected |
| T025 | Vendor registration + pending | frontend | 3 | T004 | `feat/T025-vendor-reg` | Apply → pending approval |
| T026 | Admin approve vendor | admin | 3 | T024,T025 | `feat/T026-approve-vendor` | Status active after approve |
| T027 | Vendor store profile (name, slug) | frontend | 3 | T025 | `feat/T027-vendor-profile` | Public vendor page slug |
| T028 | Payout details storage (encrypted) | backend | 3 | T004 | `feat/T028-payout-profile` | Stripe account id field ready |

### Week 4 — Catalog (T029–T037)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T029 | CategoryCRUD service | backend | 4 | T005 | `feat/T029-category-crud` | Admin manages categories |
| T030 | Vendor product create (draft) | frontend | 4 | T021 | `feat/T030-product-create` | Form + validation |
| T031 | Publish product (draft→active) | backend | 4 | T030 | `feat/T031-publish` | State transition tested |
| T032 | Variant CRUD + stock | frontend | 4 | T007 | `feat/T032-variant-crud` | Stock cannot go negative on edit guard |
| T033 | Product image upload + queue resize | backend | 4 | T006 | `feat/T033-images` | Storage + job |
| T034 | Catalog index paginated | frontend | 4 | T031 | `feat/T034-catalog` | Public listing |
| T035 | Category filter on catalog | frontend | 4 | T034 | `feat/T035-filter` | Query param filter works |
| T036 | Product detail page | frontend | 4 | T034 | `feat/T036-pdp` | Variants selectable |
| T037 | Admin suspend product | admin | 4 | T024 | `feat/T037-suspend` | active→suspended |

### Week 5 — Cart (T038–T043)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T038 | AddToCart command + stock check | backend | 5 | T008 | `feat/T038-add-cart` | Feature test pass |
| T039 | Update cart line qty | backend | 5 | T038 | `feat/T039-update-cart` | Validates stock |
| T040 | Remove cart line | backend | 5 | T038 | `feat/T040-remove-cart` | Empty cart OK |
| T041 | Cart totals by vendor preview | backend | 5 | T038 | `feat/T041-cart-totals` | Grouped subtotals |
| T042 | Abandoned cart expiry job | backend | 5 | T008 | `feat/T042-abandon-job` | Scheduled job |
| T043 | Cart feature test suite | tester | 5 | T041 | `feat/T043-cart-tests` | 4+ tests green |

### Week 6 — Checkout (T044–T050)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T044 | Checkout → Order + OrderGroups | backend | 6 | T041 | `feat/T044-checkout` | One group per vendor |
| T045 | OrderLine price snapshot | backend | 6 | T044 | `feat/T045-snapshot` | Immutable after order |
| T046 | Commission lock on OrderGroup | backend | 6 | T044,T014 | `feat/T046-commission-lock` | Rate stored on group |
| T047 | Stock decrement w/ DB lock | backend | 6 | T044 | `feat/T047-stock-lock` | Concurrency test |
| T048 | Checkout feature tests | tester | 6 | T047 | `feat/T048-checkout-tests` | Multi-vendor case |
| T049 | Customer order history UI | frontend | 6 | T044 | `feat/T049-orders-ui` | Lists orders + status |
| T050 | Order status transitions | backend | 6 | T044 | `feat/T050-order-states` | Enum + guards |

### Week 7 — Stripe (T051–T057)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T051 | Stripe Connect vendor onboarding | backend | 7 | T028 | `feat/T051-connect-onboard` | OAuth/link flow stubbed in test |
| T052 | Multi-vendor Checkout session | backend | 7 | T044 | `feat/T052-stripe-session` | Redirect to Stripe |
| T053 | Webhook checkout.session.completed | backend | 7 | T052 | `feat/T053-webhook-paid` | Order → paid |
| T054 | Failed/expired session stock restore | backend | 7 | T053 | `feat/T054-webhook-fail` | Stock restored |
| T055 | Payment state machine service | backend | 7 | T010 | `feat/T055-payment-sm` | All transitions tested |
| T056 | Payment audit log | backend | 7 | T055 | `feat/T056-audit` | Append-only log |
| T057 | FakeStripe for PHPUnit | tester | 7 | T052 | `feat/T057-fake-stripe` | No live keys in CI |

### Week 8 — Vendor fulfillment (T058–T063)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T058 | Vendor dashboard order list | frontend | 8 | T022 | `feat/T058-vendor-dash` | Own OrderGroups only |
| T059 | Vendor confirm OrderGroup | backend | 8 | T058 | `feat/T059-confirm` | pending→confirmed |
| T060 | Mark shipped + tracking number | backend | 8 | T059 | `feat/T060-ship` | Emits event hook |
| T061 | OrderGroupShipped mail job | backend | 8 | T060 | `feat/T061-ship-mail` | Queued mailable |
| T062 | Cross-vendor isolation test | tester | 8 | T022 | `feat/T062-isolation-test` | 403 on other vendor id |
| T063 | Order tracking poll endpoint | backend | 8 | T049 | `feat/T063-tracking-api` | JSON status for customer |

### Week 9 — Payouts (T064–T068)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T064 | Payout hold timer job | backend | 9 | T061 | `feat/T064-hold-timer` | Starts on delivered |
| T065 | ReleasePayout command | backend | 9 | T064 | `feat/T065-release` | Creates Payout row |
| T066 | Stripe Connect transfer | backend | 9 | T065,T051 | `feat/T066-transfer` | Test doubles transfer |
| T067 | Vendor payout history page | frontend | 9 | T065 | `feat/T067-payout-ui` | Lists payouts |
| T068 | Freeze payout if dispute open | backend | 9 | T013 | `feat/T068-freeze` | Invariant test |

### Week 10 — Reviews & disputes (T069–T073)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T069 | Post review (verified purchase) | backend | 10 | T012 | `feat/T069-review` | Rejects non-buyers |
| T070 | Vendor rating aggregate | backend | 10 | T069 | `feat/T070-rating` | Updated on review |
| T071 | File dispute on OrderGroup | frontend | 10 | T013 | `feat/T071-dispute-file` | Opens dispute |
| T072 | Admin resolve dispute | admin | 10 | T071 | `feat/T072-resolve` | buyer/vendor outcomes |
| T073 | Dispute message thread | frontend | 10 | T071 | `feat/T073-thread` | Messages persisted |

### Week 11 — Admin (T074–T077)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T074 | Admin commission rate CRUD | admin | 11 | T014 | `feat/T074-commission-crud` | Changes affect new orders only |
| T075 | Admin vendor list + suspend | admin | 11 | T026 | `feat/T075-vendor-admin` | Suspend blocks new listings |
| T076 | Admin orders/disputes dashboard | admin | 11 | T072 | `feat/T076-admin-dash` | Queue visible |
| T077 | Admin audit log viewer | admin | 11 | T056 | `feat/T077-audit-ui` | Payment/payout entries |

### Week 12 — Search, compliance, ship (T078–T097)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T078 | Scout index Product | backend | 12 | T031 | `feat/T078-scout` | Index on publish |
| T079 | Catalog search route | frontend | 12 | T078 | `feat/T079-search` | Query returns hits |
| T080 | Search filters (category, price) | frontend | 12 | T079 | `feat/T080-facets` | Combined filters |
| T081 | OrderPlaced notify vendors job | backend | 12 | T044 | `feat/T081-notify-placed` | Job dispatched |
| T082 | PaymentCompleted vendor notify | backend | 12 | T053 | `feat/T082-notify-paid` | Email/notification |
| T083 | Horizon/queue config | devops | 12 | T081 | `feat/T083-horizon` | Queues documented |
| T084 | Failed job retry policy | backend | 12 | T083 | `feat/T084-retries` | Backoff configured |
| T085 | GDPR customer data export | backend | 12 | T003 | `feat/T085-gdpr-export` | JSON export endpoint |
| T086 | GDPR erasure request job | backend | 12 | T085 | `feat/T086-gdpr-erase` | Anonymize PII |
| T087 | Lifecycle feature tests | tester | 12 | T050 | `feat/T087-lifecycle-tests` | End-to-end path |
| T088 | Webhook feature tests | tester | 12 | T057 | `feat/T088-webhook-tests` | All webhook cases |
| T089 | Payout + dispute tests | tester | 12 | T068 | `feat/T089-payout-tests` | Freeze + release |
| T090 | N+1 audit catalog/orders | perf | 12 | T034 | `feat/T090-nplus1` | eager loads fixed |
| T091 | GitHub Actions CI | devops | 12 | T002 | `feat/T091-ci` | Pest on push |
| T092 | API v1 route prefix scaffold | backend | 12 | T063 | `feat/T092-api-v1` | `/api/v1` health |
| T093 | Route/API doc in README | docs | 12 | T092 | `feat/T093-docs-api` | Main endpoints listed |
| T094 | README + verify script | docs | 12 | T001 | `feat/T094-readme` | `./bin/verify-example marketplace-v1` |
| T095 | Index migration review | perf | 12 | T009 | `feat/T095-indexes` | FK indexes added |
| T096 | Security headers middleware | backend | 12 | T001 | `feat/T096-security-headers` | Headers on responses |
| T097 | Full suite green + ship notes | lead | 12 | T088,T089 | `feat/T097-ship` | All tests pass, deploy checklist |

**Total: 97 tasks.**

---

## ~10 hour Claude Code execution plan

**Not** 97 simultaneous agents — **10 waves**, ~10 tasks each, **4–5 teammates** (or Dynamic Workflow ~16 concurrent on independent slices).

| Hour | Wave | Task IDs | Teammate focus |
|------|------|----------|----------------|
| H1 | 1 | T001–T010 | bootstrap + core schema |
| H2 | 2 | T011–T020 | rest schema + seed |
| H3 | 3 | T021–T028 | policies + vendor onboarding |
| H4 | 4 | T029–T037 | catalog |
| H5 | 5 | T038–T043 | cart |
| H6 | 6 | T044–T050 | checkout |
| H7 | 7 | T051–T057 | Stripe |
| H8 | 8 | T058–T068 | fulfillment + payouts |
| H9 | 9 | T069–T077 | trust + admin |
| H10 | 10 | T078–T097 | search, GDPR, CI, ship |

### Wave rules

1. Lead creates/updates this task board; marks `blocked` / `ready` / `done`.
2. One git worktree per active task (`feat/T0xx-*`).
3. No two agents touch the same file.
4. Lead runs `artisan test` + merge after each wave.
5. Wave N+1 starts only when wave N tests are green.

### Claude Code setup

Enable Agent Teams in `settings.json`:

```json
{
  "env": {
    "CLAUDE_CODE_EXPERIMENTAL_AGENT_TEAMS": "1"
  }
}
```

---

## Claude Code opener (paste to start build)

```markdown
Build greenfield Laravel app: multi-vendor marketplace (marketplace-v1).

Read roadmap: docs/marketplace-v1-97-task-roadmap.md

Enable Agent Teams. Create shared task board with all rows T001–T097.

Project rules (from LARAVEL_SPECIALIST_CAPABILITIES.md):
- Order splits into OrderGroups per Vendor; OrderLine price snapshot immutable
- Commission locked at checkout on OrderGroup
- Vendor tenant isolation (global scopes + policies)
- Stripe Checkout + webhooks only mark paid (never success URL)
- Pest feature tests for every command; FakeStripe in CI

Execute in 10 waves (~1 hour each, max 5 teammates):
Wave 1: T001–T010 … Wave 10: T078–T097

Each teammate: claim one ready task → worktree branch → implement DoD → tests green → mark done.

Start Wave 1 now. After T010, run full test suite and report board status before Wave 2.
```

For massive fan-out after the board exists:

```markdown
Run as a dynamic workflow: implement each ready row in parallel with reviewer verification.
Use /effort ultracode or include the word "workflow" when scale warrants it.
Do not stop until all 97 rows are Done or explicitly blocked.
```

---

## Realistic scope note

In **~10 hours of agent time**, expect a **strong MVP** through roughly T057–T068 if slices stay atomic and merges stay clean. Tasks T078–T097 (Scout, GDPR, full admin polish) may need an **11th hour** or a follow-up session.

The **12-week** calendar is the product planning horizon; the **10-hour** plan is the parallel Claude Code build horizon.

---

## Scaffold command (when ready)

```bash
export PATH="/d/laravel13.x/bin:$PATH"
./bin/new-example marketplace-v1 "Multi-vendor Marketplace"
./bin/verify-example marketplace-v1
```

Then run Claude Code with the opener above against `examples/marketplace-v1`.
