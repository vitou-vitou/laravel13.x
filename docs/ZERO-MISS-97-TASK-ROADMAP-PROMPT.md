# Zero-miss prompt — 97-task / 3-month / 10-wave roadmap

## Pocket card (remember this)

**One question:** *New app or existing code?*

| | New app (180+) | Existing repo (PGI, GitHub, `D:\…`) | **MVP done + docs/UX slices** |
|--|----------------|-------------------------------------|--------------------------------|
| **1** | Pick type → `./bin/new-example slug` | **Audit first** (read code, no MD yet) | Read `examples/<slug>/docs/DESIGN.md` + parent `docs/` |
| **2** | Agent → 97-task roadmap | Bug/feature → contributor prompt | **Mode D** → matrix + N tasks + parallel waves |
| **3** | Waves build features | Big program → update roadmap, then waves | **4–5 agents per wave** (worktrees) |
| **Skills** | spec-kit + superpowers | openspec + superpowers | openspec + superpowers + **impeccable** (UX) |

**Daily dev (99% of time):** existing repo → describe bug/feature → test green. **No phases. No 97 tasks.**

**Big program only:** open [Master prompt](#master-prompt-copy-everything-below-the-line) when you need a full audit + 97-task map.

**Paths:** agency = `examples/pgi-agency-portal` · core frontend = `examples/pgi-core-frontend-uat`

**Not Laravel?** (Next.js, React, Python, Java, Keycloakify, monorepo workspace) — **same pocket card**; swap skills + run/test commands:

| Stack | Audit / build skills | Test / run (examples) |
|-------|----------------------|------------------------|
| Next.js / React | senior-frontend + superpowers | `npm test` / `npm run dev` |
| Vue (PGI core) | senior-frontend + laravel-specialist | `php artisan test` + `npm run build` |
| Python | senior-backend / focused skill | `pytest` |
| Java | IntelliJ skill if plugin; else generalPurpose | `./mvnw test` / `./gradlew test` |
| Keycloakify | read theme repo; Keycloak + React patterns | build theme JAR; no `./bin/new-example` |

`./bin/new-example` and 180+ catalog = **Laravel in laravel13.x only**. Other stacks: clone anywhere → audit → fix/feature prompt with **full path** (`D:\phillipinsurancekh\…`).

**Copy-paste (any stack, daily dev):**

```markdown
Project path: D:\phillipinsurancekh\[repo]
Stack: [Next.js | Python | Keycloakify | …]
Task: [bug or feature — one paragraph]
Use superpowers + [stack skill from table above].
Read repo first; minimal diff; run project's test command before done.
```

**Learn Cursor:** [cursor.com/learn](https://cursor.com/learn) → start with 4 lessons in [`docs/CURSOR_LEARN_MAP.md`](CURSOR_LEARN_MAP.md).

**UI looks too basic?** Say **“AI pick my UI”** — [`GITHUB_UI_RESOURCE_INDEX.md`](GITHUB_UI_RESOURCE_INDEX.md) (agent chooses Dribbble/Unsplash/kit; you don’t). Agent skill: **laravel-ui-phase** (`.agents/skills/laravel-ui-phase/` or `.cursor/skills/laravel-ui-phase/`).

Paste this **before** asking for a roadmap or before an agent writes T001–T097.  
The agent must **finish the inventory gates** and show the **coverage matrix** before listing any tasks.

**Jump to:**

| You are… | Start here |
|----------|------------|
| Starting a **new** app from the 180+ catalog | [Greenfield playbook (Steps 1–6)](#greenfield-playbook-start-from-the-180-catalog) |
| **PGI / existing app** — bug fix or new feature | [Contributor workflow (PGI)](#contributor-workflow--existing-app-eg-pgi) |
| **Any repo** — `D:\phillipinsurancekh`, GitHub, frontend | [External repos — understand first](#external-repos-understand-first-then-update-md) |
| **Cloning** or hardening existing code (full audit) | [Short prompt (existing clone)](#short-prompt-when-you-already-have-a-clone) |
| Running waves after the roadmap exists | [Execution opener](#execution-opener-after-roadmap-exists) |
| **MVP done — ship slices from DESIGN.md + `docs/`** | [Mode D — Docs + UX + parallel](#mode-d--docs--ux--parallel-post-mvp-slices) |
| Parallel agent orchestration (template) | [`docs/prompts/97-parallel-agents-template.md`](prompts/97-parallel-agents-template.md) |
| Full agent rules (Phases 0–6) | [Master prompt](#master-prompt-copy-everything-below-the-line) |
| **Learn Cursor** (official course) | [`docs/CURSOR_LEARN_MAP.md`](CURSOR_LEARN_MAP.md) |
| **Company repos** (Phillip, GitHub) + laravel13.x | [`docs/COMPANY_PROJECTS_WORKFLOW.md`](COMPANY_PROJECTS_WORKFLOW.md) |
| **UI too basic vs PRD** | [`docs/FRONTEND_REAL_WORLD_GATE.md`](FRONTEND_REAL_WORLD_GATE.md) |
| **Dribbble / Unsplash / GitHub UI lists** | [`docs/GITHUB_UI_RESOURCE_INDEX.md`](GITHUB_UI_RESOURCE_INDEX.md) — **say “AI pick my UI”** · skill **laravel-ui-phase** |

---

## Master prompt (copy everything below the line)

```markdown
You are building a **97-task / 12-week / 10-wave (~10h agent)** roadmap for an existing or greenfield Laravel project.

**Do not write T001–T097 until every gate below is done and printed as tables.**

---

### Phase 0 — Clarify intent (ask if unclear; else state assumption)

Pick ONE primary mode and say it out loud:

| Mode | Meaning |
|------|---------|
| **A — Harden** | Code exists; goal = tests, UAT, prod readiness |
| **B — Greenfield** | Build MVP from scratch (like marketplace-v2) |
| **C — Hybrid** | Harden + explicit net-new features listed by user |
| **D — Docs + UX slices** | MVP shipped; extend from `examples/<slug>/docs/DESIGN.md` + parent `docs/` specs, CSV templates, checklists — **parallel waves required** |

Also state: **Track A** (mock/CI, no live secrets) vs **Track B** (live UAT credentials required).

**Mode D:** Task count is **N** (often 20–40), not forced to 97. Still use **sequential waves** with **4–5 parallel agents inside each wave**.

---

### Phase 1 — Mandatory codebase inventory (read repo; no guessing)

Produce these tables from **actual files** (paths + counts):

1. **Panels & routes** — every Filament panel, page, resource, hidden pages, web/api routes
2. **Product / domain lines** — enum or catalog; for each: UI entry point, service, e-policy, tests yes/no
3. **Services** — every class under `app/Services/`; real vs mock binding; interface yes/no
4. **Auth & identity** — provider, SSO, roles → panel map, entity/multi-tenant config
5. **Payments** — all rails (e.g. KHQR, Payway, Stripe); webhook routes; paid-only-via-webhook rule
6. **Integrations** — OCR, CIF, Staff, Referral, gateway OAuth, external APIs + OpenAPI docs paths
7. **Livewire / UI components** — scanners, capture modals, shared traits/concerns
8. **Events / realtime** — broadcasts, Reverb, jobs, mail/notifications (list every mailable path)
9. **i18n** — lang files; which flows translated vs English-only
10. **Data layer** — models, migrations; legacy/orphan tables; FK debt on fresh migrate
11. **Tests** — count files/methods; coverage **by product line and panel** (not just total)
12. **Ops** — Docker/CI, `.env.example` vars, deploy docs, verify scripts
13. **Git upstream** — `git branch -a`; note unmerged remote branches that may be in-scope (e.g. payway, travel)
14. **Existing specs** — `docs/superpowers/`, OpenSpec, Spec-Kit, ADRs

---

### Phase 2 — Coverage matrix (every row must map to a task or explicit OUT OF SCOPE)

Build a matrix with columns:

`Feature / slice | Exists in code? | In tests? | Roadmap task ID | Action: build | verify | refactor | OOS | blocked`

**Rules:**

- Every **panel page** → at least one row
- Every **InsuranceType / product line** (or equivalent) → at least one row
- Every **Service** with production binding → at least one row (test or contract)
- Every **webhook / public route** → security + test row
- Every **promo / OCR / QR / upload** flow → own row (if already built → **verify**, not rebuild)
- **Admin / planned-but-missing** roles → build tasks OR explicit OOS with reason
- **Upstream branches** not merged → spike/OOS row each

**Hard stop:** If any inventory row is blank in "task ID" and not marked OOS → do not publish roadmap.

**Mode D — add UX/docs columns to the matrix:**

`Source doc | UX slice | Route | Field / CSV column | Screen element | Exists in UI? | Task ID | Wave | Parallel group | Action | OOS`

**Mode D — mandatory doc sources (read files; no guessing):**

1. `examples/<slug>/docs/DESIGN.md` — personas, IA, gap matrix, slice specs, components
2. Parent specs — e.g. `docs/superpowers/specs/`, `docs/<domain>/README.md`, checklists
3. CSV templates — every header column → form field, table column, or export column
4. Weekly / operational checklists — every step → batch rail step, route, or OOS
5. Existing routes + Blade — map **built** vs **DESIGN.md** (verify vs build)
6. Copy / trust UX — approve vs skip labels, status enum values match CSV exactly

**Mode D hard stop:** Every **gap matrix row** in DESIGN.md and every **slice acceptance** line → task ID or OOS. Every **CSV column** → mapped or OOS.

---

### Phase 3 — Domain invariants (8–12 bullets)

From code + business rules (payment, auth, tenant, immutability, mock-only-in-test, etc.).

---

### Phase 4 — Write exactly 97 tasks

Format per task: `ID | Slice | Role | Week | Deps | Branch | DoD`

**Distribution target (adjust labels, keep totals):**

| Block | Tasks | Weeks |
|-------|-------|-------|
| Foundation + CI + docs + schema | 20 | 1–2 |
| Auth / access | 8 | 3 |
| Payment(s) — **each rail gets tasks** | 9 | 4 |
| Product line 1 (e.g. vehicle) | 8 | 5 |
| Product line 2 (e.g. fire) | 7 | 6 |
| Product line 3–4 (e.g. property) | 7 | 7 |
| Product line 5–6 (e.g. PA/travel) | 7 | 8 |
| Secondary panel (e.g. agent) | 7 | 9 |
| Shared (OCR, e-policy, docs, email notify) | 7 | 10 |
| External integrations | 8 | 11 |
| Admin + E2E + ship | 9 | 12 |

**Total must equal 97.** Renumber if you swap tasks; never drop a matrix row without OOS.

**Task type tags in DoD:** `[build]` `[verify]` `[refactor]` `[docs]` `[blocked:needs-credentials]`

**Anti-patterns (forbidden):**

- Marking "build promo UI" when `applyPromoCode` already exists → use `[verify]`
- One vague task for "all product lines" → split per line or per panel
- Skipping Docker/i18n/upstream sync without OOS
- 97 simultaneous agents → use **10 waves**, 4–5 parallel inside each wave

---

### Phase 5 — 10-wave execution table

| Hour | Wave | Task IDs | Focus |
|------|------|----------|-------|
| H1–H10 | 1–10 | ~10 tasks each | Sequential waves; parallel only inside wave |

Wave rules: worktree per task; no two agents same file; test green before next wave.

**Parallel execution (all modes — especially Mode D):**

| Rule | Detail |
|------|--------|
| Waves are **sequential** | Wave N+1 only after `php artisan test` green on merged Wave N |
| Tasks inside a wave are **parallel** | Up to **4–5** concurrent agents (or Agent Teams / `dispatching-parallel-agents`) |
| File ownership | No two parallel tasks edit the same file — split tasks or put in different waves |
| Branch | `feat/T0xx-short-slug` or worktree per task |
| Merge order | Lead integrates, resolves conflicts, runs full suite |
| UX gate | Mode D: each `[build]` task lists **route + fields** from matrix; optional screenshot in NEXT_SESSION |

Template for batch grouping: [`docs/prompts/97-parallel-agents-template.md`](prompts/97-parallel-agents-template.md).

**Mode D wave table must include:** `Wave | Task IDs | Parallel group (A–E) | Files touched (paths) | UX acceptance (1 line)`.

---

### Phase 6 — Deliverables checklist

Before saying "done", confirm:

- [ ] Coverage matrix attached (zero unmapped rows)
- [ ] Exactly 97 tasks counted
- [ ] Realistic scope note (what 10h likely finishes vs needs hour 11 / Track B)
- [ ] Claude opener for Wave 1
- [ ] Skill stack line: openspec|spec-kit + superpowers + laravel-specialist + filament-pro (pick fit)
- [ ] Build status section with baseline test count from `php artisan test`

---

### Output files

- `docs/<project-slug>-97-task-roadmap.md`
- `<example-path>/docs/NEXT_SESSION.md` (handoff stub)

Project context for this run:

[PASTE: repo path, branch, clone path, prod goal A/B/C, in-scope payment rails, entities, deadline]
```

---

## Mode D — Docs + UX + parallel (post-MVP slices)

Use when **MVP is done** (publish log, auth, core flow) and the next work is **all slices** from a UX map plus parent `docs/` — e.g. `examples/creator-operator-v1` + `docs/creator-commission/`.

**Not greenfield (B). Not a single bug (contributor).** This is the path for “build roadmap for **all of it** and **start everything**” with **parallel waves**.

### What you get

| Artifact | Path |
|----------|------|
| UX-aligned roadmap | `examples/<slug>/docs/ROADMAP.md` **or** `docs/<slug>-slice-roadmap.md` |
| UX source of truth | `examples/<slug>/docs/DESIGN.md` (extend route/field tables as slices ship) |
| Handoff | `examples/<slug>/docs/NEXT_SESSION.md` |
| Coverage matrix | Inside roadmap — **zero blank rows** across docs + UX + code |

**Reference:** `examples/creator-operator-v1/docs/DESIGN.md` (gap matrix + slices 1–5).

### Pipeline

```text
Read DESIGN.md + parent docs/ + CSV templates
        ↓
Mode D inventory (code + UX + docs tables)
        ↓
Unified coverage matrix (zero blanks)
        ↓
N tasks (T001–T0NN) + wave table with parallel groups
        ↓
Wave 1…K — parallel inside wave, sequential across waves
        ↓
verify-example + test green + update DESIGN.md + NEXT_SESSION
```

### Step D1 — Docs + UX inventory (mandatory tables)

Produce from **real files**:

| # | Table | Contents |
|---|--------|----------|
| 1 | **Doc map** | Every linked spec, checklist, CSV path → purpose |
| 2 | **IA / routes** | DESIGN.md routes vs `routes/web.php` — built / partial / missing |
| 3 | **CSV columns** | Each template header → UI field, export, or OOS |
| 4 | **Checklist steps** | Batch steps 1–7 → screen, action, or OOS |
| 5 | **Gap matrix** | Every DESIGN.md gap row |
| 6 | **Slice acceptance** | Each slice’s “Acceptance:” line → test or manual check task |
| 7 | **Components** | `x-*` Blade components — exists / needs extend |
| 8 | **Tests** | Feature tests per slice — exists / missing |

**Hard stop:** Do not write T001 until tables 4–6 have no unmapped rows.

### Step D2 — Coverage matrix (docs + UX + code)

Minimum columns:

`ID | Source | Slice | Route | Field/column | Code? | UX spec? | Task | Wave | Par | Action [build|verify|ux|docs|OOS]`

Example rows (creator-operator):

| Source | Slice | Route | Action |
|--------|-------|-------|--------|
| `weekly-metrics.csv` | 1 | `/operator/creators/{id}/metrics` | build |
| `monthly-settlement.csv` | 2 | `/creator/settlement` | build |
| `tools/tiktok-metadata` | 3 | `/operator/creators/{id}/import` | build |
| DESIGN gap P2 | 4 | publish-log form | build (IG fields) |
| Slice 4 | billing | `/operator/billing` | build |
| Slice 5 | webhooks | `/operator/integrations` | build |
| DESIGN § approvals | MVP | `/creator/approvals` | verify |

### Step D3 — Write N tasks + parallel wave table

- **N** = one task per matrix `[build]` or `[verify]` row (typically 25–45 for 5 slices).
- Group into **waves of ~8–12 tasks**, **4–5 parallel** per wave by **disjoint file paths**.
- Tag DoD: `[build]` `[verify]` `[ux]` `[docs]` `[test]`.

**Wave planning rules:**

1. **Wave 1:** schema + models (sequential, 1 agent) — blocks others.
2. **Wave 2+:** parallel by slice **if** migrations already landed (e.g. metrics controller + settlement service in same wave only if different directories).
3. Put all **navigation / shared layout** edits in one task or one sequential wave tail — avoids merge hell.
4. **Webhooks + billing** often parallel (different controllers).
5. End with **integration wave:** nav links, DESIGN.md route table update, full test run.

Example wave table (creator-operator, illustrative):

| Wave | Task IDs | Parallel | Focus |
|------|----------|----------|-------|
| W1 | T001–T003 | 1 agent | migrations, models, factories |
| W2 | T004–T008 | A–E | metrics CRUD, settlement calculator, import service |
| W3 | T009–T013 | A–D | billing config, webhooks, policies |
| W4 | T014–T018 | A–E | Blade views per slice (separate view dirs) |
| W5 | T019–T022 | A–D | feature tests per slice |
| W6 | T023–T025 | 1–2 | nav, DESIGN.md sync, verify-example |

### Step D4 — Copy-paste: roadmap + parallel (no code yet)

```markdown
Read docs/ZERO-MISS-97-TASK-ROADMAP-PROMPT.md — **Mode D — Docs + UX + parallel**.

Do not write application code. Roadmap + unified matrix + wave table + handoff only.

Project: examples/{{slug}}/
UX map: examples/{{slug}}/docs/DESIGN.md
Parent docs: [list paths, e.g. docs/creator-commission/, docs/superpowers/specs/...]
CSV templates: [list paths]

Follow Step D1–D3:
- All mandatory inventory tables
- Coverage matrix with **zero blank rows** (docs + UX + gaps + CSV columns)
- N tasks with [build]/[verify]/[ux] tags
- Wave table with **parallel groups** and **file ownership** (no same-file parallel)
- Realistic scope note for ~10h parallel agent time

Output:
- examples/{{slug}}/docs/ROADMAP.md (replace lightweight phase list if present)
- examples/{{slug}}/docs/NEXT_SESSION.md stub with Wave 1 opener

Skill stack: openspec + superpowers + laravel-specialist + impeccable (UX copy/layout)
Reference parallel template: docs/prompts/97-parallel-agents-template.md
```

### Step D5 — Copy-paste: execute one wave (parallel)

```markdown
Read examples/{{slug}}/docs/ROADMAP.md — Coverage matrix + **Wave {{N}}** table only.

Use:
- subagent-driven-development
- using-git-worktrees
- dispatching-parallel-agents (or Agent Teams — max 4–5 concurrent)

Project: examples/{{slug}}/ — Mode D, Track A.

Execute **Wave {{N}} only** (tasks {{Txxx}}–{{Tyyy}}):
- One worktree / branch per parallel group (feat/T0xx-…)
- No two agents on the same file
- Each task: backend + Blade + feature test per matrix UX acceptance line
- [verify] tasks: add tests only, no duplicate UI
- Run `php artisan test` + `./bin/verify-example {{slug}}` before claiming wave done
- Update NEXT_SESSION.md (pass count, next wave, any DESIGN.md route rows to mark built)

Do **not** start Wave {{N+1}} in this session.
```

### Step D6 — “Start everything” opener (multi-wave session)

For a single long session that runs **all waves** with parallel inside each:

```markdown
Read examples/{{slug}}/docs/ROADMAP.md (full matrix + all waves).

Mode D — Docs + UX + parallel. Project: examples/{{slug}}/

For each wave W1…WK **in order**:
1. Dispatch up to 5 parallel agents (disjoint files) — dispatching-parallel-agents + git worktrees
2. Merge, resolve conflicts, `php artisan test` green
3. Only then start next wave

After final wave:
- Sync examples/{{slug}}/docs/DESIGN.md (IA table: mark routes built)
- ./bin/verify-example {{slug}}
- Update NEXT_SESSION.md and docs/SESSION_STATE.md if MVP+ slices complete

Skills: openspec + superpowers + laravel-specialist + impeccable + subagent-driven-development + using-git-worktrees
```

### Mode D vs lightweight ROADMAP

| Lightweight phases only | Mode D zero-miss |
|-------------------------|------------------|
| “Slice 1 in progress” | Every CSV column → task |
| Easy to skip UX gaps | Gap matrix rows forced to task or OOS |
| Serial implementation | **Parallel groups** per wave documented upfront |
| No file ownership | Explicit paths — fewer merge conflicts |

**Rule:** If user says **parallel is important**, always produce the **wave table with Par groups A–E** — never “implement slices 1–5” without parallel plan.

### laravel13.x examples using Mode D today

| Example | UX map | Parent docs |
|---------|--------|-------------|
| `creator-operator-v1` | `examples/creator-operator-v1/docs/DESIGN.md` | `docs/creator-commission/` |

---

## Greenfield playbook — start from the 180+ catalog

Use this path when you want a **new** Laravel example in `laravel13.x` — not when you clone an external repo (that is **Mode A**, like PGI).

### What you get at the end

| Artifact | Path |
|----------|------|
| Runnable app shell | `examples/<slug>/` at `http://<slug>.test` |
| 97-task roadmap | `docs/<slug>-97-task-roadmap.md` |
| Agent handoff | `examples/<slug>/docs/NEXT_SESSION.md` |
| Spec-Kit stub (aligned) | `.specify/specs/001-*/spec.md`, `plan.md`, `tasks.md` |

**Reference roadmaps:** `docs/marketplace-v1-97-task-roadmap.md` (greenfield), `docs/pgi-agency-portal-97-task-roadmap.md` (harden existing).

---

### The pipeline (one glance)

```text
Step 1  Pick project type (180+ catalog) + fill Project Brief
Step 2  ./bin/new-example <slug>          → empty Laravel + Spec-Kit
Step 3  Agent: zero-miss Mode B           → 97-task roadmap + matrix
Step 4  Agent: sync Spec-Kit stub         → one source of truth
Step 5  Agent: Wave 1…10 build            → spec-kit + superpowers
Step 6  ./bin/verify-example + tests      → SESSION_STATE when MVP done
```

**Rule:** Greenfield init = **Spec-Kit + Superpowers**. Do **not** use OpenSpec until after MVP.

---

### Project Brief — fill once, paste everywhere

Copy the block below into a note (or `examples/<slug>/docs/PROJECT_BRIEF.md`). Replace every `{{…}}`. You will paste the filled brief into Step 1 (optional), Step 3, and Step 5 — no need to rewrite in “good writer” style each time.

```markdown
# Project Brief — {{DISPLAY_NAME}}

## Identity
- **Slug:** {{slug}}                    (lowercase, hyphens; drives http://{{slug}}.test)
- **Display name:** {{DISPLAY_NAME}}
- **180+ pick:** LARAVEL_SPECIALIST_CAPABILITIES.md → {{CATEGORY}} → **{{PROJECT_TYPE}}**
- **One-line:** {{One sentence — what the app does for whom}}

## Mode
- **Roadmap mode:** B — Greenfield
- **Track:** A — mock/CI only (no live Stripe/Keycloak/etc. unless listed below)
- **Live credentials needed later (Track B):** {{none | list keys}}

## Roles (who logs in)
- {{Role 1}} — {{what they do}}
- {{Role 2}} — {{what they do}}

## Stack (agent picks if unsure)
- Auth: {{Breeze | Sanctum | Keycloak | Filament panel auth}}
- Admin UI: {{Filament | Livewire only | none for MVP}}
- Payments: {{none | Stripe | KHQR mock | …}}
- Realtime: {{none | Reverb}}
- Other: {{Horizon, Scout, …}}

## Domain decomposition (nouns → rules)
### Entities (models)
- {{Entity}} — {{one line}}

### Ownership (who owns what row)
- {{Role}} owns: {{entities}}

### States (lifecycle)
- {{Entity}}: {{state}} → {{state}} → …

### Invariants (must never break)
1. {{e.g. Vendor cannot see another vendor's orders}}
2. {{e.g. Order is paid only after webhook confirms payment}}
3. {{…}}

### Events / jobs (optional)
- {{Event}} → {{listener or job}}

## Out of scope for MVP (explicit OOS)
- {{feature}} — reason

## Reference (do not re-scaffold)
- Similar example in repo: {{examples/kindly-e-commerce-1122 | none}}
- Read SESSION_STATE.md — skip completed MVPs

## Success
- MVP = {{e.g. vendor can list product, customer can checkout with mock payment}}
- Verify: `./bin/verify-example {{slug}}` + `php artisan test` green
```

#### Worked example (multi-vendor marketplace)

```markdown
# Project Brief — Acme Marketplace

## Identity
- **Slug:** acme-marketplace
- **Display name:** Acme Marketplace
- **180+ pick:** LARAVEL_SPECIALIST_CAPABILITIES.md → E-Commerce → **Multi-vendor marketplace**
- **One-line:** Customers buy from many vendors; platform takes commission and pays out vendors.

## Mode
- **Roadmap mode:** B — Greenfield
- **Track:** A — Stripe test mode + mock webhooks in CI
- **Live credentials needed later:** Stripe live keys, optional SendGrid

## Roles
- Customer — browse, cart, checkout, reviews
- Vendor — products, variants, fulfill their order groups
- Admin — categories, commission rates, disputes

## Stack
- Auth: Breeze + Sanctum API
- Admin UI: Filament (admin + vendor panel)
- Payments: Stripe Checkout + webhook idempotency
- Realtime: none for MVP

## Domain decomposition
### Entities
- Customer, Vendor, Product, ProductVariant, Cart, Order, OrderGroup, OrderLine, Payment, Payout, Review, Dispute, Commission

### Ownership
- Platform: categories, commission, disputes, payouts
- Vendor: products, variants, their order groups
- Customer: cart, orders, reviews

### States
- Order: pending_payment → paid → processing → shipped → delivered → completed | cancelled
- Payment: pending → completed | failed | refunded

### Invariants
1. Vendor never sees another vendor's data (tenant scope on vendor_id)
2. Paid status only via verified Stripe webhook
3. OrderGroup splits multi-vendor checkout; payout per OrderGroup

## Out of scope for MVP
- Native mobile app — API only later
- Multi-currency — USD only

## Reference
- Decomposition detail: LARAVEL_SPECIALIST_CAPABILITIES.md § Live Decomposition Example: Multi-vendor Marketplace
- Do not re-scaffold kindly-e-commerce-1122 (single-vendor MVP complete)

## Success
- MVP = vendor lists product, customer checks out, admin sees order; tests green
```

---

### Step 1 — Choose your project type (about 10 minutes)

**Goal:** Know *what* you are building before any code runs.

**Do this:**

1. Open [`LARAVEL_SPECIALIST_CAPABILITIES.md`](../LARAVEL_SPECIALIST_CAPABILITIES.md) and find your row under **180+ Buildable Project Types** (category + name).
2. Optional browse: [`docs/study/180-laravel-project-types-study-packet.md`](study/180-laravel-project-types-study-packet.md) for a category map.
3. If that type has a **Live Decomposition Example** in the capabilities file (marketplace is the canonical one), copy entities, ownership, and states into your Project Brief.
4. If there is **no** live example, run the decomposition prompt below in a **short agent chat** first — still no scaffold yet.

**Checklist before Step 2:**

- [ ] Slug chosen (lowercase, hyphens, unique under `examples/`)
- [ ] Project Brief filled (at least entities, roles, 3+ invariants)
- [ ] Checked `docs/SESSION_STATE.md` — you are not redoing a finished MVP

**Decomposition-only prompt** (when the catalog has no live example):

```markdown
Read LARAVEL_SPECIALIST_CAPABILITIES.md — "Domain Decomposition Pattern" and checklist.

Project Brief:
[PASTE YOUR FILLED PROJECT BRIEF — or only Identity + Roles + One-line]

Output only:
1. Entities (models) with one-line descriptions
2. Ownership boundaries per role
3. Lifecycle states per core entity
4. 8–12 business invariants
5. Suggested Laravel stack (auth, admin, payments, queues)

Do not scaffold code. Do not write T001–T097 yet.
```

**What Step 1 produces:** A completed **Project Brief** you can paste into Steps 3 and 5 unchanged.

---

### Step 2 — Scaffold the empty app (about 5 minutes)

**Goal:** Create `examples/<slug>/` with Laravel, Herd link, and Spec-Kit stubs. The scaffold does **not** implement your 180+ type — only the shell.

**Run in Git Bash** (from repo root):

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd /d/laravel13.x
./bin/new-example {{slug}} "{{DISPLAY_NAME}}"
./bin/verify-example {{slug}}
```

Replace `{{slug}}` and `{{DISPLAY_NAME}}` with your Project Brief values.

**What you should see:**

| Check | Expected |
|-------|----------|
| Folder | `examples/{{slug}}/` exists |
| URL | `APP_URL=http://{{slug}}.test` in `.env` |
| Spec-Kit | `.specify/specs/001-*/` with stub `spec.md`, `plan.md`, `tasks.md` |
| Verify script | `./bin/verify-example {{slug}}` passes |

**If something fails:** See [`docs/EXAMPLE_DEV_LESSONS.md`](EXAMPLE_DEV_LESSONS.md) and [`.cursor/rules/windows-herd-gitbash.mdc`](../.cursor/rules/windows-herd-gitbash.mdc) — common fixes: `composer install`, `./bin/fix-example-app-key {{slug}}`, file session/cache in `.env`.

**What Step 2 produces:** An empty, verified example ready for a roadmap — not features.

---

### Step 3 — Generate the 97-task roadmap (agent chat, roadmap only)

**Goal:** One document that lists exactly **97 tasks**, a **coverage matrix** (nothing missing), **10 waves**, and invariants — before any feature code.

**Paste this entire block** (replace the bracket section with your filled Project Brief):

```markdown
Read docs/ZERO-MISS-97-TASK-ROADMAP-PROMPT.md and follow Phases 0–6 exactly.

Do not write application code. Roadmap + matrix + handoff only.

---

## Project Brief
[PASTE FULL PROJECT BRIEF FROM STEP 1]

## Paths
- Example: examples/{{slug}}/
- Roadmap output: docs/{{slug}}-97-task-roadmap.md
- Handoff: examples/{{slug}}/docs/NEXT_SESSION.md

## Instructions
- **Mode B — Greenfield.** Phase 1 inventory = list what **must be built** (scaffold is empty except Laravel + Spec-Kit stub).
- **Track A** unless Project Brief lists Track B credentials.
- Phase 2: every entity, role, panel, payment rail, webhook, job, and admin screen → task ID or explicit OUT OF SCOPE.
- Phase 4: exactly 97 tasks; tag DoD with [build] [verify] [refactor] [docs] [blocked:needs-credentials].
- Phase 5: 10 waves (~10 tasks each); parallel only inside a wave.
- Phase 6: deliverables checklist + Wave 1 execution opener + baseline test count from `php artisan test` in examples/{{slug}}/.
- Skill stack line: **spec-kit** + superpowers + laravel-specialist + filament-pro (if admin in brief).

Reference shape: docs/marketplace-v1-97-task-roadmap.md (structure, not copy-paste tasks).
```

**What Step 3 produces:**

| File | Purpose |
|------|---------|
| `docs/{{slug}}-97-task-roadmap.md` | Master plan — matrix, invariants, T001–T097, waves |
| `examples/{{slug}}/docs/NEXT_SESSION.md` | Resume file for the next agent session |

**Review before Step 4:** Open the roadmap and confirm the coverage matrix has **no blank rows** (every row has a task ID or OOS).

---

### Step 4 — Align Spec-Kit with the roadmap (one short agent chat)

**Goal:** Spec-Kit files describe *intent*; the 97-task doc remains the **single source of truth** for execution. Avoid duplicating 97 rows in `tasks.md`.

**Paste:**

```markdown
Read docs/{{slug}}-97-task-roadmap.md and the Project Brief in examples/{{slug}}/docs/PROJECT_BRIEF.md (create PROJECT_BRIEF.md from brief if missing).

Update only Spec-Kit stubs under examples/{{slug}}/.specify/specs/001-*/:

1. **spec.md** — user stories + acceptance criteria from Project Brief; link to roadmap matrix
2. **plan.md** — architecture, stack choices, phase summary (not 97 duplicate lines)
3. **tasks.md** — one paragraph: "Execute T001–T097 per docs/{{slug}}-97-task-roadmap.md; one wave at a time; test green between waves."

Do not implement features. Do not change the 97 task list except to fix obvious typos.
```

**File roles after Step 4:**

| File | Holds |
|------|--------|
| `docs/{{slug}}-97-task-roadmap.md` | **Master** — 97 tasks, waves, coverage matrix |
| `.specify/.../spec.md` | Why / user stories / invariants |
| `.specify/.../plan.md` | How / stack / phases |
| `.specify/.../tasks.md` | Pointer to roadmap (not a second task list) |

**What Step 4 produces:** Spec-Kit and roadmap stay in sync; you are ready to build Wave 1.

---

### Step 5 — Execute Wave 1 (then Wave 2…10)

**Goal:** Implement tasks in order; never skip the matrix; tests green before the next wave.

**Paste for each wave** (change wave number and task IDs):

```markdown
Use **spec-kit** + superpowers + laravel-specialist + filament-pro (if admin in Project Brief).
Use subagent-driven-development + using-git-worktrees for parallel tasks inside the wave.

Read:
- docs/{{slug}}-97-task-roadmap.md (Coverage matrix + invariants + Wave {{N}} table)
- examples/{{slug}}/docs/NEXT_SESSION.md

Project: examples/{{slug}}/ — Mode B Greenfield, Track A.

Execute **Wave {{N}} only** (tasks {{Txxx}}–{{Tyyy}}).
- One worktree per parallel task; no two agents on the same file
- [build] vs [verify]: never rebuild what the matrix marks as already done
- Run `php artisan test` before claiming wave complete
- Update NEXT_SESSION.md with pass count and next wave

Do not start Wave {{N+1}} in this session.
```

**After Wave 10:** Run verification and record MVP in session state.

---

### Step 6 — Done criteria

```bash
export PATH="/d/laravel13.x/bin:$PATH"
./bin/verify-example {{slug}}
cd examples/{{slug}} && php artisan test
```

When MVP matches your Project Brief **Success** section:

1. Update `docs/SESSION_STATE.md` (tests count, resume path).
2. Refresh `examples/{{slug}}/docs/NEXT_SESSION.md` for post-MVP work (OpenSpec changes, polish).

---

### Mode cheat sheet

| Starting point | Mode | Phase 1 inventory means… |
|----------------|------|---------------------------|
| `./bin/new-example` + 180+ pick | **B — Greenfield** | List what **must be built** from decomposition |
| Clone external repo (e.g. PGI uat) | **A — Harden** or **C — Hybrid** | Read what **already exists** in `app/`, tests, git branches |
| MVP complete, new feature | **OpenSpec** | Change proposal — not a full 97-task greenfield |
| MVP complete, **all DESIGN.md slices + docs/** | **D — Docs + UX + parallel** | DESIGN.md gap matrix + CSV columns + checklist steps → N tasks + wave table |

---

### Common mistakes

| Mistake | Fix |
|---------|-----|
| Skipping Project Brief | Fill the template once — saves rework in Steps 3–5 |
| Expecting `new-example` to build the 180+ type | Scaffold is empty; roadmap + waves build features |
| Using OpenSpec at init | Greenfield = Spec-Kit first; OpenSpec after MVP |
| Two task lists (Spec-Kit + roadmap diverge) | Step 4: `tasks.md` points to roadmap only |
| Re-scaffolding SESSION_STATE MVPs | Read `docs/SESSION_STATE.md` before Step 2 |
| Opening `:5173` instead of Herd | Browser URL = `APP_URL` → `http://{{slug}}.test` |

---

## Contributor workflow — existing app (e.g. PGI)

Use this when you **already have code** in `examples/pgi-agency-portal/` and you are fixing bugs or adding features — not starting from `new-example`.

### Pick the right tool

| Your work | Use this | Do **not** |
|-----------|----------|------------|
| One bug fix | **Bug prompt** below | Regenerate 97 tasks |
| Small feature (e.g. “foo foo” button on one page) | **Feature prompt** below | Run greenfield Project Brief |
| Medium feature (new panel page, new service) | **OpenSpec** (`/opsx:propose` or openspec skill) | Re-scaffold the app |
| Test hardening / UAT / many gaps | **Mode A** + `docs/pgi-agency-portal-97-task-roadmap.md` waves | Mode B greenfield |
| Big net-new slice (e.g. Payway rail, Medical on Partners) | **Mode C Hybrid** — one matrix row + roadmap task or OpenSpec change | Blanket “rewrite all 97 tasks” |
| **MVP done — ship all UX slices from DESIGN.md** | **Mode D** — [Docs + UX + parallel](#mode-d--docs--ux--parallel-post-mvp-slices) | Lightweight phase list without matrix |

**PGI paths:**

- App: `examples/pgi-agency-portal/` (branch `uat`)
- Roadmap: `docs/pgi-agency-portal-97-task-roadmap.md`
- Handoff: `examples/pgi-agency-portal/docs/NEXT_SESSION.md`
- Invariants: read roadmap **Domain invariants** before changing payment/auth/tenant code

**Skills:** `openspec` + `superpowers` + `laravel-specialist` + `filament-pro` + `systematic-debugging` (bugs).

**Verify after any change:**

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd examples/pgi-agency-portal && php artisan test
```

---

### Bug fix — copy-paste prompt

```markdown
Project: examples/pgi-agency-portal (existing Laravel 12 + Filament 5 app)

Bug: [describe symptom — URL, panel, steps, expected vs actual]
Error (if any): [paste stack trace or test name]

Use systematic-debugging + superpowers + laravel-specialist + filament-pro.

Rules:
- Minimal diff — fix root cause only
- Match existing patterns (Services, Filament pages, KHQR mocks in tests)
- Add or update a test that would have caught this
- Do not re-run ZERO-MISS Phases 0–6 or rewrite the 97-task roadmap
- Run php artisan test before done

Read docs/pgi-agency-portal-97-task-roadmap.md Domain invariants if touch payment, auth, or multi-panel access.
```

---

### Small new feature — copy-paste prompt (“foo foo”)

```markdown
Project: examples/pgi-agency-portal

Feature: [one sentence — e.g. add "Foo Foo" export on Partners Fire sale list]
Panel / route: [e.g. /partners — FireSale resource]
Roles allowed: [e.g. partner user only]
Acceptance:
1. [observable behavior]
2. [test or manual check]
Out of scope: [what not to build]

Use openspec + superpowers + laravel-specialist + filament-pro.

Process:
1. Quick inventory — which files/pages already exist for this area (grep/read; no full 97 matrix)
2. TDD: failing test first where practical
3. Implement smallest change; Filament/Livewire conventions already in repo
4. php artisan test green
5. Short note in examples/pgi-agency-portal/docs/NEXT_SESSION.md (what changed, how to verify)

Do not regenerate docs/pgi-agency-portal-97-task-roadmap.md unless this feature was already listed as T0xx — if so, mark that task done in NEXT_SESSION only.
```

---

### Medium / large feature — OpenSpec change

When the feature touches **multiple areas** (new service, webhook, new product line, new panel):

```markdown
Project: examples/pgi-agency-portal

Change: [describe feature]
Why: [business reason]

Use openspec skill — propose change, then apply tasks.
Also read docs/pgi-agency-portal-97-task-roadmap.md coverage matrix: if this slice exists as T0xx, align work to that task ID.

Stack: superpowers TDD + laravel-specialist + filament-pro.
Track A unless change needs live Keycloak/KHQR/bank API keys.
```

After merge, optionally add one row to the roadmap **coverage matrix** (manual edit) so the zero-miss map stays honest — no need to renumber all 97 tasks.

---

### When the full zero-miss doc *does* apply to PGI

Use **Mode A** (short prompt in this doc) when:

- First-time audit of the clone
- Rewriting `docs/pgi-agency-portal-97-task-roadmap.md` so nothing is missing
- Running **Wave 1…10** as a coordinated hardening program (tests, CI, verify slices)

Use **Mode C Hybrid** when:

- User lists explicit net-new work (e.g. “add Payway + Medical on Partners”) **and** hardening
- Agent adds matrix rows + replaces or appends tasks — still aim for 97 total only if doing a full roadmap refresh

---

### Example: “add foo foo” on PGI

| If “foo foo” is… | You do… |
|------------------|---------|
| Label on one Filament page | **Small feature prompt** — one session, one PR |
| New Partners workflow + API + tests | **OpenSpec change** — proposal → tasks → implement |
| Part of planned UAT hardening | Find **T0xx** in roadmap → **Harden wave opener** for that wave only |

---

## External repos — understand first, then update MD

Use this for **any project outside greenfield** — including:

| Location | Example |
|----------|---------|
| `examples/*` in laravel13.x | `pgi-agency-portal`, `pgi-core-frontend-uat` |
| `D:\phillipinsurancekh\…` | Your daily dev checkout (upstream working copy) |
| Any GitHub org/repo | `phillipinsurancekh/pgi-core-frontend`, random OSS |

**Yes — understand with skills first, update markdown second.** That order avoids wrong 97-task roadmaps (empty tests folder, wrong mode, missing panels).

### Three places code can live

```text
GitHub (source of truth)
    ↓ clone
D:\phillipinsurancekh\repo     ← you commit & push here (normal dev)
    ↓ optional mirror for agents
examples/<slug> in laravel13.x ← roadmaps, verify scripts, agent handoff
```

| Where you work | Best for |
|----------------|----------|
| `D:\phillipinsurancekh\…` | Day job: fix bugs, PRs to company GitHub |
| `examples/<slug>` | Agent sessions: roadmaps, parallel waves, docs in laravel13.x |
| GitHub only (no local yet) | Clone first — never guess structure from memory |

**Keep paths explicit in every prompt:** `Project path: D:\phillipinsurancekh\pgi-core-frontend` **or** `examples/pgi-core-frontend-uat` — not both unless you state which is canonical.

---

### Phase 0 — Understand (skills, no roadmap yet)

**Goal:** Inventory real code; produce a short **audit note** (can be chat output or `docs/audits/<repo>-YYYY-MM-DD.md`).

**Do not** write T001–T097 or rewrite ROADMAP until this phase is done.

| Skill / approach | Use when |
|------------------|----------|
| **codebase-onboarding** / **code-to-prd** | First time in repo — routes, modules, auth, data model |
| **laravel-specialist** | Laravel/PHP/Filament/Livewire structure |
| **systematic-debugging** | Bug-driven entry — trace failure to root cause |
| **openspec explore** | Unclear scope — explore before proposing change |
| **superpowers** brainstorming | New feature — requirements before code |
| **cavecrew-investigator** | Quick “where is X defined?” file:line map |

**Copy-paste — understand-only audit:**

```markdown
Understand-only — do not implement, do not write 97 tasks yet.

Project path: [D:\phillipinsurancekh\pgi-core-frontend | examples/pgi-core-frontend-uat]
Branch: [uat | main]
Stack: [Laravel + Vue | React | etc. — detect from repo]

Use codebase-onboarding + laravel-specialist (if PHP) or senior-frontend (if SPA-only).

Output:
1. Stack & entry points (how to run, test command if any)
2. Auth & roles
3. Main modules / panels / routes (table with paths)
4. Tests today (count, gaps)
5. Integrations (.env vars, external APIs)
6. Git branches worth noting
7. Risks for production (blockers)
8. Recommended mode: A harden | C hybrid | contributor one-off

Save summary to: docs/audits/[repo-slug]-audit.md (in laravel13.x) OR paste in chat only.
```

---

### Phase 1 — Update MD (after audit)

Only after Phase 0, pick **one** doc strategy:

| Situation | Update what |
|-----------|-------------|
| PGI agency portal | `docs/pgi-agency-portal-97-task-roadmap.md` + matrix |
| PGI core frontend (already in repo) | `examples/pgi-core-frontend-uat/docs/ROADMAP.md` + optional align with `docs/prompts/pgi-core-frontend-97-parallel-agents-prompt.md` |
| New Phillip repo, no docs yet | Create `docs/<slug>-97-task-roadmap.md` using **Mode A** master prompt + audit paste |
| Single bug / small feature | **Contributor workflow** only — skip full 97 refresh |
| New repo not in `examples/` yet | Clone to `examples/<slug>` **or** document path `D:\…` in PROJECT_BRIEF / NEXT_SESSION |

**Copy-paste — audit → roadmap refresh:**

```markdown
Read docs/ZERO-MISS-97-TASK-ROADMAP-PROMPT.md Phases 0–6.

Mode A — Harden (existing code).

Prior audit:
[PASTE Phase 0 output or docs/audits/...]

Project path: [path]
Output: docs/[slug]-97-task-roadmap.md (or update existing ROADMAP.md)
Coverage matrix must have zero unmapped rows. Exactly 97 tasks only if doing full program; else say "contributor mode — no 97 refresh".
```

---

### Phase 2 — Execute (same as PGI contributor)

- Bug / small feature → contributor prompts in this doc  
- Wave program → execution opener (openspec + superpowers for existing apps)  
- Greenfield new app in laravel13.x only → greenfield playbook  

---

### PGI family quick map (this monorepo today)

| Repo | Local agent path | Roadmap doc |
|------|------------------|-------------|
| `pgi-agency-portal` | `examples/pgi-agency-portal` | `docs/pgi-agency-portal-97-task-roadmap.md` |
| `pgi-core-frontend` | `examples/pgi-core-frontend-uat` | `examples/pgi-core-frontend-uat/docs/ROADMAP.md` + `docs/prompts/pgi-core-frontend-97-parallel-agents-prompt.md` |
| Other `phillipinsurancekh/*` | Clone to `examples/<slug>` or use `D:\phillipinsurancekh\<name>` | Create after Phase 0 audit |

**Frontend note:** `pgi-core-frontend` is Laravel + Vue (not Filament-only). Use **laravel-specialist + senior-frontend** (or frontend-developer) in skills line, not filament-pro unless audit shows Filament.

---

### Random GitHub project (not Phillip)

1. **Understand:** clone locally, run Phase 0 audit prompt with detected stack skills.  
2. **Decide:** contribute upstream vs mirror into `examples/` for agent tooling.  
3. **Document:** only if you will run multi-week agent program — else use normal PR workflow without 97-task doc.  
4. **Do not** assume `./bin/new-example` or `./bin/verify-example` unless project lives under `examples/<slug>` in laravel13.x.

---

### Workflow summary (recommended)

```text
Skills audit (Phase 0)  →  short audit MD or chat
        ↓
Update roadmap MD (Phase 1)  →  only if program or missing matrix
        ↓
Contributor / waves / OpenSpec (Phase 2)  →  actual code
```

**Rule of thumb:** If you cannot fill an inventory table from real files, you are not ready to update the 97-task MD.

---

```markdown
Read docs/ZERO-MISS-97-TASK-ROADMAP-PROMPT.md and follow Phases 0–6 exactly.

Project: examples/pgi-agency-portal (uat)
Goal: Mode A harden + list Mode B blockers
Re-audit codebase; print coverage matrix; then rewrite docs/pgi-agency-portal-97-task-roadmap.md so zero matrix rows are unmapped. Keep 97 tasks and 10 waves. Swap redundant "build promo" tasks for verify tasks + missing slices (Vehicle QR, DocumentUpload, payment email, Reverb prod, Docker, Medical Partners decision, upstream sync).
```

---

## Execution opener (after roadmap exists)

**Greenfield (Mode B)** — use spec-kit:

```markdown
Read docs/{{slug}}-97-task-roadmap.md AND the Coverage matrix section.
Read examples/{{slug}}/docs/PROJECT_BRIEF.md if present.

Use **spec-kit** + superpowers + laravel-specialist + filament-pro (if admin)
+ subagent-driven-development + using-git-worktrees.

Before Wave N: every task is [build] or [verify] — never rebuild what the matrix marks "exists + untested".

Execute Wave {{N}} only. php artisan test green before Wave {{N+1}}.
```

**Harden existing app (Mode A/C)** — use openspec:

```markdown
Read docs/{{slug}}-97-task-roadmap.md AND the Coverage matrix section.

Use **openspec** + superpowers + laravel-specialist + filament-pro
+ subagent-driven-development + using-git-worktrees.

Before Wave N: confirm [verify] tasks for code that already exists; [build] only for gaps.

Wave {{N}} only. Merge + php artisan test green before next wave.
```

**Docs + UX slices (Mode D)** — MVP done, parallel waves:

```markdown
Read examples/{{slug}}/docs/ROADMAP.md AND examples/{{slug}}/docs/DESIGN.md AND the Coverage matrix.

Use **openspec** + superpowers + laravel-specialist + impeccable
+ subagent-driven-development + using-git-worktrees + dispatching-parallel-agents.

Execute **Wave {{N}} only** — up to 4–5 parallel agents, disjoint files.
Each task must satisfy its matrix UX acceptance line (route + fields + copy).
[verify] = tests/docs only; [build] = code + Blade + test.

Merge → php artisan test → ./bin/verify-example {{slug}} → update NEXT_SESSION → then Wave {{N+1}}.
```

---

## Why this works

| Failure mode | Gate that catches it |
|--------------|----------------------|
| Skipped Vehicle QR, doc upload | Phase 1 §7 + matrix |
| "Build fire promo" when UI exists | Matrix action = verify; anti-pattern |
| Medical line forgotten | Phase 2 product-line rule |
| Payway branch ignored | Phase 1 §13 upstream branches |
| 10h plan impossible | Track A/B + realistic scope |
| 97 tasks but wrong shape | Phase 4 distribution + exact count |
| **Skipped UX slice / CSV column** | **Mode D Step D1 tables 3–6 + gap matrix** |
| **Parallel merge conflicts** | **Wave table file ownership + max 4–5 agents** |
| **DESIGN.md out of sync with routes** | **Mode D Step D6 — update IA table after last wave** |
