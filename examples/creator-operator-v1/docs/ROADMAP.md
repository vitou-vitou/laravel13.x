# Creator Operator — Mode D slice roadmap

**Generated:** 2026-06-14 (Step D4) · **Mode:** D — Docs + UX + parallel · **Track:** A (mock/CI)  
**UX map:** [DESIGN.md](DESIGN.md) · **Zero-miss:** [`docs/ZERO-MISS-97-TASK-ROADMAP-PROMPT.md`](../../../docs/ZERO-MISS-97-TASK-ROADMAP-PROMPT.md#mode-d--docs--ux--parallel-post-mvp-slices)

**Baseline:** MVP done · **34 tests** green · **Slices:** 1–5 + gap fixes · **Tasks:** T001–T032 · **Waves:** 6

---

## Realistic scope note

| Horizon | Delivers |
|---------|----------|
| **W1–W6 (~8–10h parallel agent time)** | Slices 1–5, gap P2 fields, webhooks, billing config + limit gate, feature tests |
| **Out of this program (OOS / Phase 6)** | Live Stripe Checkout, Python subprocess for CLI, weekly email, CSV export download, interactive checklist dashboard, `/settings/subscription` Cashier portal |

---

## Step D1 — Inventory

### 1. Doc map

| Path | Purpose |
|------|---------|
| `examples/creator-operator-v1/docs/DESIGN.md` | UX map, IA, gap matrix, slice specs |
| `docs/superpowers/specs/2026-06-13-creator-commission-tiktok-first-design.md` | Asset workflow, settlement attribution |
| `docs/creator-commission/README.md` | Pilot kit, status values, settlement formulas |
| `docs/creator-commission/weekly-batch-checklist.md` | Steps 1–7 operator cadence |
| `docs/creator-commission/templates/publish-log.csv` | Publish log columns |
| `docs/creator-commission/templates/weekly-metrics.csv` | Weekly metrics columns |
| `docs/creator-commission/templates/monthly-settlement.csv` | Settlement columns + formula |
| `tools/tiktok-metadata/README.md` | JSONL metadata for BUILD LIST import |

### 2. IA / routes (DESIGN vs `routes/web.php`)

| Route | DESIGN | Code today | Task |
|-------|--------|------------|------|
| `/`, `/login`, `/dashboard` | MVP | Built | verify |
| `/operator` | MVP | Built | verify |
| `/operator/creators*` | MVP | Built | verify |
| `/operator/creators/{id}/publish-log/*` | MVP | Built | T008 enhance |
| `/creator/approvals` | MVP | Built | T015 webhook |
| `/operator/creators/{id}/metrics` | Slice 1 | Missing | T009, T017 |
| `/operator/creators/{id}/metrics/create` | Slice 1 | Missing | T009, T017 |
| `/creator/reports` | Slice 1 | Missing | T014, T022 |
| `/operator/creators/{id}/settlement` | Slice 2 | Missing | T010, T018 |
| `/operator/creators/{id}/settlement/create` | Slice 2 | Missing | T010, T018 |
| `/creator/settlement` | Slice 2 | Missing | T014, T022 |
| `/operator/creators/{id}/import` | Slice 3 | Missing | T011, T019 |
| `/operator/billing` | Slice 4 | Missing | T012, T020 |
| `/operator/integrations` | Slice 5 | Missing | T013, T021 |
| `/settings/subscription` | Slice 4 optional | Missing | **OOS** (Track B Stripe) |

### 3. CSV column map

**publish-log.csv**

| Column | UI today | Task |
|--------|----------|------|
| `date` | `logged_on` create/edit | verify |
| `tiktok_url` | create/edit | verify |
| `yt_url`, `ig_url` | edit + publish form | verify |
| `yt_video_id` | model only, not in Blade | T008 |
| `title_variant` | create/edit | verify |
| `posted_time` | model only, not in Blade | T008 |
| `status` | edit dropdown | verify |
| `views_yt_7d`, `views_ig_7d` | model only, not in Blade | T008 |
| `notes` | create/edit | T023 IG structure |

**weekly-metrics.csv**

| Column | Task |
|--------|------|
| `week_start` | T002, T009, T017 |
| `videos_published` | T002, T009, T017 |
| `best_video_url` | T002, T009, T017 |
| `best_video_views` | T002, T009, T017 |
| `experiment` | T002, T009, T017 |
| `experiment_result` | T002, T009, T017 |
| `operator_notes` | T002, T009, T017 |

**monthly-settlement.csv**

| Column | Task |
|--------|------|
| `period_start`, `period_end` | T002, T010, T018 |
| `platform` | T002, T010 |
| `gross_payout_local`, `currency` | T002, T010 |
| `payout_status` | T002, T010 |
| `s_views`, `t_views` | T002, T010 |
| `attributed_revenue` | T004, T010 (computed) |
| `commission_rate_pct` | T002, T010 |
| `monthly_ops_fee` | T002, T010 |
| `commission_amount` | T004, T010 (computed) |
| `creator_net` | T004, T010 (computed) |
| `notes` | T002, T010 |

### 4. Checklist steps → portal

| Step | Portal mapping | Task |
|------|----------------|------|
| Pre-batch | dashboard KPIs | verify |
| 1 BUILD LIST | `last_run_date` + import | T005, T011, T019 |
| 2 ELIGIBILITY | status `skipped_*` on edit | verify |
| 3 ASSET | onboarding notes | verify |
| 4 PACKAGING | add publish row | T023 |
| 5 APPROVAL | creator approvals | T015 |
| 6 PUBLISH + LOG | mark published | T008 |
| 7 REPORT | metrics + settlement tabs | T009–T010, T014 |
| Interactive checklist mode | — | **OOS** P3 |

### 5. Gap matrix → tasks

| Gap | Priority | Task(s) | Action |
|-----|----------|---------|--------|
| No weekly metrics UI | P1 | T009, T017, T025 | build |
| No monthly settlement UI | P1 | T004, T010, T018, T026 | build |
| BUILD LIST manual only | P2 | T005, T011, T019, T027 | build |
| IG-specific packaging fields | P2 | T023 | build |
| `posted_time`, `yt_video_id`, 7d views missing in UI | P2 | T008, T030 | build |
| Onboarding split across creator form | OK | — | verify |
| Interactive batch checklist | P3 | — | **OOS** |
| Billing / membership | P3 | T007, T012, T016, T020, T028 | build |
| n8n webhooks | P4 | T006, T013, T015, T021, T029 | build |

### 6. Slice acceptance → tests

| Slice | Acceptance | Task |
|-------|------------|------|
| 1 | Operator records week metrics without Sheets | T025 |
| 2 | S÷T formula matches README; creator sees same numbers | T026 |
| 3 | Import ≥1 URL from JSONL paste | T027 |
| 4 | Creator count gated by plan | T028 |
| 5 | Webhook receives JSON on approve | T029 |

### 7. Components

| Component | Status | Task |
|-----------|--------|------|
| `x-batch-loop-rail` | Built | verify |
| `x-publish-status` | Built | verify |
| `x-ops-panel` | Not in repo | **OOS** (use existing panel pattern) |
| `x-creator-subnav` | Missing | T024 |

### 8. Tests today

| Area | File | Status | Task |
|------|------|--------|------|
| Publish flow | `PublishLogFlowTest.php` | 7 tests | verify + T030 |
| Auth/profile | Breeze tests | 27 tests | verify |
| Metrics | — | Missing | T025 |
| Settlement | — | Missing | T026 |
| Import | — | Missing | T027 |
| Billing | — | Missing | T028 |
| Webhooks | — | Missing | T029 |

---

## Step D2 — Coverage matrix (zero blanks)

| ID | Source | Slice | Route / element | Field / column | Code? | Task | Wave | Par | Action |
|----|--------|-------|-----------------|----------------|-------|------|------|-----|--------|
| M01 | MVP | 0 | publish log flow | full flow | Yes | — | — | — | verify |
| M02 | publish-log.csv | 0 | edit form | `yt_video_id` | Partial | T008 | W2 | A | build |
| M03 | publish-log.csv | 0 | edit form | `posted_time` | Partial | T008 | W2 | A | build |
| M04 | publish-log.csv | 0 | edit form | `views_yt_7d`, `views_ig_7d` | Partial | T008 | W2 | A | build |
| M05 | DESIGN gap | 4 | packaging create/edit | IG caption section | No | T023 | W4 | C | build |
| M06 | weekly-metrics.csv | 1 | `/operator/.../metrics` | all 7 columns | No | T009,T017 | W3,W4 | B,D | build |
| M07 | weekly-metrics.csv | 1 | `/creator/reports` | read-only rows | No | T014,T022 | W3,W4 | E | build |
| M08 | monthly-settlement.csv | 2 | settlement CRUD | all columns + formula | No | T004,T010,T018 | W2–W4 | B,C | build |
| M09 | monthly-settlement.csv | 2 | `/creator/settlement` | statement + badges | No | T014,T022 | W3,W4 | E | build |
| M10 | tiktok-metadata | 3 | `/operator/.../import` | JSONL paste + preview | No | T005,T011,T019 | W2–W4 | B,D | build |
| M11 | Slice 4 | 4 | `/operator/billing` | plan + limit | No | T007,T012,T020 | W2–W4 | C | build |
| M12 | Slice 4 | 4 | onboard creator | enforce limit | No | T016 | W3 | — | build |
| M13 | Slice 5 | 5 | `/operator/integrations` | CRUD + test ping | No | T013,T021 | W3,W4 | D | build |
| M14 | Slice 5 | 5 | approve/publish | dispatch webhook | No | T006,T015 | W2,W3 | B | build |
| M15 | DESIGN | — | creator hub | subnav links | No | T024 | W4 | C | ux |
| M16 | DESIGN | — | navigation | Billing, Integrations | No | T031 | W6 | — | ux |
| M17 | checklist §7 | 1 | metrics form | experiment fields | No | T009 | W3 | B | build |
| M18 | README formula | 2 | settlement create | live preview | No | T004,T010 | W2,W3 | B | build |
| M19 | Slice 3 | 3 | import | dedupe by tiktok_url | No | T005 | W2 | B | build |
| M20 | Slice 5 | 5 | integrations | governance copy link | No | T021 | W4 | D | ux |
| M21 | checklist interactive | — | dashboard | checklist mode | No | — | — | — | **OOS** |
| M22 | Slice 4 | 4 | `/settings/subscription` | Stripe portal | No | — | — | — | **OOS** Track B |
| M23 | Phase 6 | — | email report | weekly email | No | — | — | — | **OOS** |
| M24 | Phase 6 | — | export | CSV download | No | — | — | — | **OOS** |
| M25 | onboarding | OK | creator edit | onboarding fields | Yes | — | — | — | verify |

**Matrix status:** 25 rows mapped · 4 explicit OOS · 0 blanks

---

## Step D3 — Tasks T001–T032

Format: `ID | Slice | DoD tag | Deliverable | Deps`

| ID | Slice | Tag | Deliverable | Deps |
|----|-------|-----|-------------|------|
| T001 | all | build | Migration: `weekly_metrics`, `monthly_settlements`, `integration_webhooks`, `integration_webhook_deliveries`; `users.operator_plan` | — |
| T002 | all | build | Models + enums: `WeeklyMetric`, `MonthlySettlement`, `IntegrationWebhook`, `IntegrationWebhookDelivery`, `PayoutStatus`, `SettlementPlatform`, `IntegrationEvent`, `OperatorPlan`; `Creator` relations | T001 |
| T003 | all | build | Factories for new models; seed demo metric row optional | T002 |
| T004 | 2 | build | `App\Services\SettlementCalculator` — attributed, commission, creator_net per README | T002 |
| T005 | 3 | build | `App\Services\TikTokMetadataImportService` — parse JSONL, dedupe vs existing `tiktok_url` | T002 |
| T006 | 5 | build | `App\Services\IntegrationWebhookDispatcher` — HMAC optional, log deliveries | T002 |
| T007 | 4 | build | `config/operator-billing.php` — starter/pro plans, creator limits; `User` cast | T001 |
| T008 | gap | build | Publish log edit: `yt_video_id`, `posted_time`, `views_yt_7d`, `views_ig_7d` in Blade + publish form | — |
| T009 | 1 | build | `Operator\WeeklyMetricController` + routes index/create/store | T002 |
| T010 | 2 | build | `Operator\MonthlySettlementController` + routes + formula preview in create | T004 |
| T011 | 3 | build | `Operator\TikTokImportController` + routes preview/store bulk rows | T005 |
| T012 | 4 | build | `Operator\BillingController` + route; plan display + mock upgrade | T007 |
| T013 | 5 | build | `Operator\IntegrationWebhookController` + CRUD routes + test ping | T006 |
| T014 | 1,2 | build | `Creator\ReportsController`, `Creator\SettlementController` + routes | T002 |
| T015 | 5 | build | Call dispatcher from `ApprovalController::approve` + `PublishLogController::publish` | T006 |
| T016 | 4 | build | `CreatorController::store` — block when at plan creator limit | T007 |
| T017 | 1 | build | Views `operator/metrics/index`, `create` | T009 |
| T018 | 2 | build | Views `operator/settlement/index`, `create` with formula block | T010 |
| T019 | 3 | build | View `operator/import/index` — paste JSONL, preview table, confirm | T011 |
| T020 | 4 | build | View `operator/billing/index` | T012 |
| T021 | 5 | build | View `operator/integrations/index` — URL, events, test, governance note | T013 |
| T022 | 1,2 | build | Views `creator/reports/index`, `creator/settlement/index` | T014 |
| T023 | gap | ux | Packaging create/edit — labeled IG caption + YT tags sections in `notes` or subfields | T008 |
| T024 | ux | ux | `components/creator-hub-nav.blade.php`; links on `operator/creators/show` | — |
| T025 | 1 | test | `tests/Feature/WeeklyMetricsTest.php` | T017 |
| T026 | 2 | test | `tests/Feature/MonthlySettlementTest.php` + unit calc edge cases | T018 |
| T027 | 3 | test | `tests/Feature/TikTokImportTest.php` + fixture JSONL | T019 |
| T028 | 4 | test | `tests/Feature/OperatorBillingTest.php` — limit enforcement | T016,T020 |
| T029 | 5 | test | `tests/Feature/IntegrationWebhookTest.php` — approve fires delivery | T015,T021 |
| T030 | gap | verify | Extend tests for publish log edit fields (T008) | T008 |
| T031 | ux | docs | `navigation.blade.php` — Billing, Integrations; update DESIGN.md IA table | T020,T021 |
| T032 | ship | verify | `./bin/verify-example creator-operator-v1`; update NEXT_SESSION | all |

**Count:** 32 tasks · **6 waves**

---

## Step D3 — Wave table (parallel groups)

**Rules:** Sequential waves · max **5 parallel** agents inside W2–W5 · no two tasks same file in one wave.

| Wave | Task IDs | Parallel | Focus | Primary paths (ownership) |
|------|----------|----------|-------|---------------------------|
| **W1** | T001–T003 | **1 agent** | Schema + models + factories | `database/migrations/*`, `app/Models/*`, `database/factories/*` |
| **W2** | T004–T008 | **A–E** | Services + publish-log field gap | `app/Services/*`, `config/operator-billing.php`, `resources/views/operator/publish-log/edit.blade.php` |
| **W3** | T009–T016 | **A–E** | Controllers, routes, webhook wire, billing gate | `app/Http/Controllers/**`, `routes/web.php`, `ApprovalController`, `PublishLogController`, `CreatorController` |
| **W4** | T017–T024 | **A–E** | Blade views + creator hub nav | `resources/views/operator/**`, `resources/views/creator/**`, `resources/views/components/creator-hub-nav.blade.php`, `operator/creators/show.blade.php` |
| **W5** | T025–T030 | **A–F** | Feature tests | `tests/Feature/*` |
| **W6** | T031–T032 | **1–2 agents** | Global nav, DESIGN sync, verify | `resources/views/layouts/navigation.blade.php`, `docs/DESIGN.md`, `docs/NEXT_SESSION.md` |

### W2 parallel assignment

| Par | Task | Files |
|-----|------|-------|
| A | T004 | `app/Services/SettlementCalculator.php` |
| B | T005 | `app/Services/TikTokMetadataImportService.php` |
| C | T006 | `app/Services/IntegrationWebhookDispatcher.php` |
| D | T007 | `config/operator-billing.php`, `app/Enums/OperatorPlan.php`, `app/Models/User.php` |
| E | T008 | `resources/views/operator/publish-log/edit.blade.php`, `PublishLogController.php` |

### W3 parallel assignment

| Par | Task | Files |
|-----|------|-------|
| A | T009 | `Operator/WeeklyMetricController.php` |
| B | T010 | `Operator/MonthlySettlementController.php` |
| C | T011 | `Operator/TikTokImportController.php` |
| D | T012 | `Operator/BillingController.php` |
| E | T013 | `Operator/IntegrationWebhookController.php` |
| — | T014 | `Creator/ReportsController.php`, `Creator/SettlementController.php` (sequential after A–E or own agent if routes file conflict — merge routes in lead task) |
| — | T015 | `ApprovalController.php`, `PublishLogController.php` (**after** T006 merged) |
| — | T016 | `Operator/CreatorController.php` (**after** T007 merged) |

*Note:* W3 lead merges T009–T013 first, then T014–T016 in same wave or splits T014–T016 to early W4 if `routes/web.php` conflicts.

### W4 parallel assignment

| Par | Task | Files |
|-----|------|-------|
| A | T017 | `resources/views/operator/metrics/` |
| B | T018 | `resources/views/operator/settlement/` |
| C | T019 | `resources/views/operator/import/` |
| D | T020 | `resources/views/operator/billing/` |
| E | T021 | `resources/views/operator/integrations/` |
| — | T022 | `resources/views/creator/reports/`, `creator/settlement/` |
| — | T023 | `publish-log/create.blade.php`, `edit.blade.php` |
| — | T024 | `components/creator-hub-nav.blade.php`, `creators/show.blade.php` |

### W5 parallel assignment

| Par | Task | Files |
|-----|------|-------|
| A | T025 | `tests/Feature/WeeklyMetricsTest.php` |
| B | T026 | `tests/Feature/MonthlySettlementTest.php` |
| C | T027 | `tests/Feature/TikTokImportTest.php` |
| D | T028 | `tests/Feature/OperatorBillingTest.php` |
| E | T029 | `tests/Feature/IntegrationWebhookTest.php` |
| F | T030 | extend `PublishLogFlowTest.php` or new file |

---

## Domain invariants (unchanged + slice)

1. Creator never sees another creator’s rows (policies + `user_id` on `Creator`).
2. Publish log status vocabulary matches CSV (`pending_approval`, not “Pending Review”).
3. Creator actions are **Approve** / **Skip** only.
4. Operator marks **published** only when row is `approved`.
5. Settlement: `attributed = gross × (S/T)`; commission on attributed; creator_net per README.
6. Webhooks fire on `approved` and `published` — never auto-publish from webhook.
7. Operator SaaS billing (plan limit) is separate from creator commission settlement.
8. Lite tier: no auto-publish without creator approval (governance copy on integrations page).

---

## Wave 1 execution opener

```markdown
Read examples/creator-operator-v1/docs/ROADMAP.md — Wave W1 only (T001–T003).

Use openspec + superpowers + laravel-specialist + subagent-driven-development.

Project: examples/creator-operator-v1/ — Mode D Track A.

Execute **W1 only** (1 agent, serial):
- T001 migration
- T002 models + enums + Creator relations
- T003 factories

Run `php artisan migrate --seed` and `php artisan test` before done.
Update NEXT_SESSION.md: W1 complete, next W2 parallel groups A–E.

Do not start W2 in this session unless user asks for all waves.
```

---

## After all waves (W6 complete)

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd examples/creator-operator-v1
php artisan test
cd ../.. && ./bin/verify-example creator-operator-v1
```

Update `docs/DESIGN.md` IA section — mark new routes **built**.  
Update `docs/SESSION_STATE.md` if post-MVP slice program complete.

---

## Skill stack

`openspec` + `superpowers` + `laravel-specialist` + `impeccable` + `subagent-driven-development` + `using-git-worktrees` + `dispatching-parallel-agents`

Parallel template: [`docs/prompts/97-parallel-agents-template.md`](../../../docs/prompts/97-parallel-agents-template.md)
