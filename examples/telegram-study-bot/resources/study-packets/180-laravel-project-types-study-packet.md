# Laravel 180+ Buildable Project Types

**A Structured Study Packet**

Built with an 8-principle learning method  
**Source:** `LARAVEL_SPECIALIST_CAPABILITIES.md` (catalog + domain decomposition)  
**Learner level:** Intermediate — you know Laravel basics; you want to *choose*, *decompose*, and *build* any backend type confidently.

---

## How to use this packet

This packet teaches you how to **navigate and build** from the 169 named project types in the Laravel specialist catalog — not memorize every bullet. You will learn the **category map**, the **decomposition method** that applies to all of them, and how they connect to **Spec-Kit / OpenSpec / Superpowers** workflows.

**Step 1 — Understanding (Principles 1–4):** Build a mental model of the catalog and the decomposition pipeline.  
**Step 2 — Automaticity (Principles 5–8):** Quiz yourself, space reviews, mix topics, and keep practicing until picking a project type and decomposing it feels automatic.

---

## The 8 principles at a glance

| # | Principle | Purpose |
|---|-----------|---------|
| 1 | Map of the system | See how categories and build layers connect |
| 2 | Clear explanations | Core ideas in plain language |
| 3 | Different media | Summary, diagram, analogy, comparison table |
| 4 | Short lessons | Bite-sized micro-lessons |
| 5 | Test yourself | Quiz + flashcards + answer key |
| 6 | Wait to review | Spaced repetition schedule |
| 7 | Mix it up | Interleaved quiz |
| 8 | Don't stop | Overlearning plan |

---

## Table of contents

1. [Step 1 — Understanding](#step-1--understanding)
   - [Principle 1 — Map of the system](#principle-1--map-of-the-system)
   - [Principle 2 — Clear explanations](#principle-2--clear-explanations)
   - [Principle 3 — Different media](#principle-3--different-media)
   - [Principle 4 — Short lessons](#principle-4--short-lessons)
2. [Step 2 — Automaticity](#step-2--automaticity)
   - [Principle 5 — Test yourself](#principle-5--test-yourself)
   - [Principle 6 — Wait to review](#principle-6--wait-to-review)
   - [Principle 7 — Mix it up](#principle-7--mix-it-up)
   - [Principle 8 — Don't stop](#principle-8--dont-stop)
3. [Appendix A — Full catalog by category (169 types)](#appendix-a--full-catalog-by-category-169-types)
4. [Appendix B — Glossary](#appendix-b--glossary)

---

# Step 1 — Understanding

**Goal:** See the 180+ list as one **system** (categories + decomposition + Laravel layers), not 169 random ideas.

---

## Principle 1 — Map of the system

### The three-layer map

| Layer | What it is | Your artifact |
|-------|------------|---------------|
| **Catalog** | 169 named project types in 23 categories | Pick *what* to build |
| **Decomposition** | 7-layer model + 6-step NOUNS→OUTPUTS map | Design *how* the domain works |
| **Laravel build** | Migration → Model → Service → … → Test | Ship *code* |

### Flow: from catalog pick to running app

```
Pick category + project type
        ↓
Run decomposition checklist (entities, states, invariants)
        ↓
Spec-Kit (new) OR OpenSpec (change) + Superpowers (TDD)
        ↓
Laravel Specialist stack (8 artifacts per feature)
        ↓
php artisan test → verify
```

### 23 categories (ecosystem table)

| Category | Count | Typical core entities |
|----------|------:|------------------------|
| SaaS & Business | 10 | Tenant, Subscription, User, Invoice |
| E-Commerce | 8 | Product, Cart, Order, Payment |
| Content & Media | 8 | Post, Author, Category, PublishSchedule |
| Communication & Social | 7 | User, Thread, Message, Notification |
| APIs & Integrations | 8 | Client, Webhook, SyncJob, ApiKey |
| Admin & Dashboards | 7 | User, Role, AuditLog, Metric |
| File & Media Processing | 6 | File, Job, Transform, StorageDisk |
| Auth & Security | 6 | User, Role, Permission, Token |
| Queues & Background | 6 | Campaign, Job, Export, RetryLog |
| Real-time & Live | 6 | Session, Event, Broadcast, Presence |
| DevOps & Infrastructure | 6 | (config/tooling — often not CRUD apps) |
| Packages & Libraries | 5 | (code reuse — not end-user apps) |
| Localization | 3 | Locale, Translation, StringKey |
| Reporting & Analytics | 5 | Report, Widget, Aggregation, Export |
| Geolocation | 4 | Location, Zone, DeliveryRoute |
| Machine Learning | 4 | Embedding, Prediction, PipelineJob |
| Compliance & Legal | 4 | Consent, DataRequest, RetentionPolicy |
| Monetization | 5 | Subscription, Meter, PromoCode |
| IoT & Data Ingestion | 4 | Device, Event, Threshold, Alert |
| Workflow & Automation | 4 | Workflow, State, Approval, Trigger |
| Healthcare & Education | 6 | Patient, Appointment, Course, Enrollment |
| Finance & Fintech | 6 | Wallet, LedgerEntry, Transfer, Loan |
| Logistics & Supply Chain | 5 | Order, Warehouse, Shipment, Supplier |
| Gaming & Entertainment | 5 | Player, Match, Leaderboard, Achievement |
| Internal Tools | 5 | Employee, Ticket, Expense, Asset |
| API Gateway Patterns | 5 | Client, RateLimit, ApiKey, Circuit |
| Emerging & Niche | 10 | varies widely |
| Platform Engineering | 6 | Flag, Error, Log, Monitor |
| Developer Tools | 5 | Snippet, WebhookCapture, SchemaDiff |

**Map takeaway:** The catalog is **grouped by business domain**, not by Laravel feature. Every entry becomes the same build pipeline once decomposed.

---

## Principle 2 — Clear explanations

### What does “180+ buildable project types” mean?

It is a **representative menu** of backends Laravel can host. The number is not magic — it is ~169 bullets in the doc, plus room for combinations (e.g. marketplace + real-time chat + billing).

### What is “decomposition” in this context?

**Decomposition** means translating business language into **entities, ownership, lifecycles, and invariants** before you write migrations. It is not a feature list (“add login, add cart”). Invariants are rules that must **never** break (e.g. “vendor A cannot see vendor B’s orders”).

### What is the 7-layer decomposition model?

| Layer | Question |
|-------|----------|
| 1 Identity | What are the core entities? |
| 2 Ownership | Who owns what? (tenancy, RBAC) |
| 3 Lifecycle | What states can each entity enter? |
| 4 Invariants | What can NEVER happen? |
| 5 Commands | What actions change state? |
| 6 Events | What happened? Who reacts? |
| 7 Queries | What do readers need to see? |

### What is the 6-step Laravel mapping (NOUNS → OUTPUTS)?

| Step | Domain | Laravel |
|------|--------|---------|
| NOUNS | Entities | Models + migrations |
| VERBS | Actions | Service methods or jobs |
| RULES | Constraints | Validation, policies, services |
| EVENTS | Things that happened | Events + listeners |
| TRIGGERS | Time/async | Scheduler, queues |
| OUTPUTS | What users see | API resources, notifications, exports |

### How do Spec-Kit, OpenSpec, and Superpowers fit?

| Tool | Role | Use when |
|------|------|----------|
| **Spec-Kit** | Full blueprint for new work | Greenfield app |
| **OpenSpec** | Change order per feature | Existing repo |
| **Superpowers** | TDD, debug, verify, review | Always while coding |
| **Laravel Specialist** | PHP/Laravel implementation | Writing code |

**Never use Spec-Kit and OpenSpec together on one project.** Always add Superpowers for execution quality.

### How do you pick ONE project from 169 options?

Use four filters:

1. **Learning goal** — Need depth? Pick marketplace, CRM, or booking (rich invariants).
2. **Already built** — Skip duplicates in your `examples/` folder (e.g. jira-v2 = project mgmt).
3. **Stack fit** — Filament admin + Pest + SQLite is enough for most catalog entries.
4. **Decomposition ready** — Multi-vendor marketplace already has full decomposition in the source MD.

### What gets built for every feature (Laravel Specialist)?

1. Migration  
2. Model (relationships, scopes, factory)  
3. Service class  
4. Thin controller  
5. API resource (if API)  
6. Form request  
7. Job (if slow/async)  
8. Pest feature test  

### What is NOT limited by Laravel?

Laravel handles HTTP, DB, queues, auth, scheduling, broadcasting. It does **not** replace: sub-ms game loops, GPU training, bare-metal firmware. Those domains use Laravel as **orchestrator**, not the compute engine.

### What ARE the hard parts per domain?

| Domain | Hard part (you must research) |
|--------|-------------------------------|
| Finance | Double-entry, compliance |
| Healthcare | HIPAA, FH7/FHIR boundaries |
| Logistics | Carrier APIs, routing |
| ML/AI | Embeddings, vector search |
| Crypto | Chain verification, gas |

**Explanation takeaway:** The catalog tells you **what** exists; decomposition tells you **how to think**; Laravel gives you **where each piece of thinking lands in code**. Memorizing 169 names is unnecessary — mastering the map and checklist is enough.

**Common misconception:** “I must build each of the 180 projects to learn Laravel.” **Reality:** Build 2–3 deeply decomposed projects; the rest become pattern recognition.

---

## Principle 3 — Different media

### One-line summary

**Any catalog entry = domain decomposition + standard Laravel feature stack + one SDD tool + Superpowers discipline.**

### Diagram — category → decomposition → code

```
┌─────────────────────────────────────────────────────────┐
│  CATALOG (169 types in 23 categories)                   │
│  e.g. "Multi-vendor marketplace" under E-Commerce       │
└──────────────────────────┬──────────────────────────────┘
                           ▼
┌─────────────────────────────────────────────────────────┐
│  7-LAYER DECOMPOSITION                                  │
│  Vendor, Order, OrderGroup… + states + invariants       │
└──────────────────────────┬──────────────────────────────┘
                           ▼
┌─────────────────────────────────────────────────────────┐
│  SDD: Spec-Kit (new)  OR  OpenSpec (change)             │
└──────────────────────────┬──────────────────────────────┘
                           ▼
┌─────────────────────────────────────────────────────────┐
│  LARAVEL: Migration Model Service Test …                │
│  + Superpowers: TDD → verify → review                   │
└─────────────────────────────────────────────────────────┘
```

### Analogy

The **catalog** is a restaurant menu with 169 dishes. **Decomposition** is reading the recipe (ingredients, order, “never serve fish raw”). **Laravel** is the kitchen equipment — same stoves for soup and steak. **Superpowers** is food safety and tasting before service. You do not eat every dish; you learn to read any recipe.

### Comparison table

| Approach | Strength | Weakness |
|----------|----------|----------|
| Memorize all 169 names | Feels complete | Forgotten in a week |
| Learn only Laravel syntax | Fast first app | Wrong model, rewrite at month 6 |
| Learn decomposition + catalog map | Builds any listed type | Needs practice on 2–3 real apps |
| Random project from list | Fun | No depth, no invariants |
| Marketplace + CRM + one API app | Covers 80% patterns | Takes months — worth it |

**Media takeaway:** Same ideas, four lenses — one sentence, flow, menu analogy, comparison.

---

## Principle 4 — Short lessons

**Lesson 1 — The list is a map, not a todo list.**  
Use categories to narrow; use decomposition to design. You will never “finish” 169 projects.

**Lesson 2 — Every type shares the same Laravel skeleton.**  
NOUNS → VERBS → RULES → EVENTS → TRIGGERS → OUTPUTS. Difference is domain words and invariants.

**Lesson 3 — Pick depth over breadth.**  
One marketplace with tests beats ten half-scaffolded Filament demos.

**Lesson 4 — Invariants become tests.**  
“What can never happen?” → Pest feature test name. Example: `test_vendor_cannot_view_other_vendor_orders`.

**Lesson 5 — SDD tools plan; Superpowers execute.**  
Spec-Kit or OpenSpec for documents; Superpowers for TDD and verification; Laravel Specialist for PHP.

**Lesson 6 — Cross-category combos are new “types”.**  
E-commerce + real-time + monetization = one product, three category labels. Count explodes — patterns stay the same.

**Short-lessons takeaway:** Learn the **pipeline**, not the **enumeration**.

---

# Step 2 — Automaticity

Understanding the catalog fades without retrieval practice. Use Principles 5–8 to make selection and decomposition automatic.

---

## Principle 5 — Test yourself

### Quiz (12 questions)

1. How many named project types are in the source document’s main list (approximately)?
2. Name three layers in the “three-layer map” (catalog, ?, ?).
3. What is an **invariant**?
4. List the seven layers of the decomposition model in order.
5. Map NOUNS and VERBS to Laravel constructs.
6. When do you use Spec-Kit vs OpenSpec?
7. Can you use Spec-Kit and OpenSpec on the same project? Why?
8. What is Superpowers’ role relative to Spec-Kit?
9. Name four filters for picking a project from the catalog.
10. Which category would “Wallet system” fall under?
11. What eight artifacts does Laravel Specialist expect per feature?
12. Why is “Multi-vendor marketplace” a strong first deep project in this repo?

### Answer key

1. **~169** (document says “180+” as representative round number).
2. **Catalog → Decomposition → Laravel build** (or Spec/OpenSpec + Superpowers in the middle).
3. A **rule that must never be violated**, no matter what (e.g. no cross-vendor data leak).
4. **Identity → Ownership → Lifecycle → Invariants → Commands → Events → Queries**.
5. **NOUNS → Models/migrations; VERBS → Services or Jobs**.
6. **Spec-Kit = greenfield**; **OpenSpec = changes on existing codebase**.
7. **No** — overlapping SDD; pick one per project.
8. **Execution methodology** — TDD, debugging, verification, code review — not a spec store.
9. **Learning goal, avoid duplicates, stack fit, decomposition already done** (any four from section).
10. **Finance & Fintech**.
11. **Migration, Model, Service, Controller, API Resource, Form Request, Job (if needed), Feature test**.
12. **Full decomposition exists in source MD**; teaches split orders, payments, tenancy; fits E-Commerce category.

### Flashcards (15)

| Front | Back |
|-------|------|
| What is the catalog for? | Choosing *what* domain to build |
| 7-layer layer 4? | **Invariants** — what can never happen |
| NOUNS in Laravel? | Models + migrations |
| Spec-Kit question? | “What rules govern the work?” |
| OpenSpec question? | “What changed?” |
| Superpowers question? | “How to execute well?” |
| Level 3 domain understanding? | **Invariants** (not just CRUD) |
| Ubiquitous language? | Code uses **business words** (`fulfill()`, not `status=4`) |
| E-Commerce example with split checkout? | **Multi-vendor marketplace** (OrderGroup per vendor) |
| SaaS category example? | Multi-tenant SaaS, CRM, HR, booking… |
| DevOps category = end-user app? | Often **tooling/config**, not a CRUD product |
| Two SDD tools together? | **Never** on same project |
| Invariant → ? | Service rules, DB constraints, **tests** |
| 169 vs 180+? | **Representative** list, not exhaustive count |
| Best study strategy? | **2–3 deep** decomposed apps, not 169 shallow |

---

## Principle 6 — Wait to review

| Session | When | What to do | Done |
|---------|------|------------|:----:|
| 1 | Today | Read Principles 1–4; draw three-layer map from memory | ☐ |
| 2 | Day 1 | Quiz Q1–6 closed book; fix gaps | ☐ |
| 3 | Day 3 | Flashcards 1–15; redo diagram | ☐ |
| 4 | Day 7 | Full quiz 1–12 + interleaved quiz (Principle 7) | ☐ |
| 5 | Day 14 | Pick 3 catalog types; write 5 invariants each without looking | ☐ |
| 6 | Day 30 | Cold draw: 7 layers + 6 NOUNS→OUTPUTS + name 10 categories | ☐ |

**Why spacing works:** The catalog is large; spaced retrieval trains **pattern recall** (decomposition pipeline), not brute memorization of 169 strings.

---

## Principle 7 — Mix it up

### Interleaved quiz (10 questions — topics shuffled)

1. TRIGGERS in the 6-step map map to what Laravel features?
2. Is “Telescope debugging setup” a domain CRUD app or DevOps tooling?
3. Name two invariants you’d expect for an **auction platform**.
4. Which SDD tool for adding Kanban to existing `jira-v2`?
5. What category is “LMS (courses, lessons, quizzes)”?
6. VERBS → ?
7. Why does Laravel struggle with “sub-10ms game loops” but can still host a **leaderboard API**?
8. Aggregate root — one sentence purpose?
9. Three artifacts OpenSpec might store under `.openspec/changes/`?
10. After `php artisan test` fails, which Superpowers skill first?

### Interleaved answer key

1. **Scheduler + queue jobs**.
2. **DevOps/tooling** (monitoring setup, not core business entities).
3. Examples: **atomic highest bid**; **no self-bidding**; **deadline rule for in-flight bids**.
4. **OpenSpec** (`/opsx:new`, etc.).
5. **Healthcare & Education**.
6. **Service methods or Jobs**.
7. **Game loop = client/engine**; leaderboard = **HTTP + DB** (Laravel’s strength).
8. **The entity that owns consistency** — changes go through it (e.g. Order, not OrderLine alone).
9. **proposal.md, tasks.md, design/spec** (varies by change).
10. **systematic-debugging**.

**Why mixing helps:** Real work jumps between category, SDD, Laravel, and invariants — not “section order” in a doc.

---

## Principle 8 — Don't stop

### Stages past first success

| Stage | Sign | Action |
|-------|------|--------|
| First correct | You pass the quiz once | Do **not** stop — run flashcards |
| Comfortable | You explain decomposition without notes | Build one small feature with TDD |
| Automatic | You pick catalog type + list invariants in 5 min | Teach someone; monthly cold test |

### Overlearning plan

- [ ] **3 flawless flashcard rounds** (no hesitations on 7 layers).
- [ ] **Decompose 5 random catalog picks** (Appendix A): entities + 3 invariants each — weekly.
- [ ] **Build one OpenSpec change** on `examples/jira-v2` OR **Spec-Kit spec** for `marketplace-v1`.
- [ ] **Explain the triad** (Spec-Kit / OpenSpec / Superpowers / Laravel Specialist) in under 2 minutes to a colleague.
- [ ] **Monthly:** cold-write the three-layer map + 6-step NOUNS→OUTPUTS.

**Final takeaway:** The 180+ list is infinite combinations of **the same thinking**. Permanent skill = **decompose any line in the catalog**, not recall every line. Practice after you “get it” — that is when memory locks in.

---

# Appendix A — Full catalog by category (169 types)

Use this as a **lookup table**. When studying, cover one category per day.

### SaaS & Business (10)
Multi-tenant SaaS platform with subscription billing · CRM (contacts, deals, pipelines, activities) · Project management tool (tasks, boards, time tracking) · Invoice & billing system · HR management system (employees, leaves, payroll) · Inventory management system · Point of Sale (POS) backend · Booking & reservation system · Subscription box management platform · Fleet management system

### E-Commerce (8)
Full e-commerce backend (products, cart, orders, payments) · Digital products marketplace · Multi-vendor marketplace · Auction platform · Dropshipping management system · Wholesale ordering platform · Rental/lending platform · Coupon & discount engine

### Content & Media (8)
Blog / CMS with scheduled publishing · News aggregator · Podcast platform backend · Video streaming platform backend · Document management system · Knowledge base / wiki · Portfolio platform · Job board

### Communication & Social (7)
Real-time chat system (WebSockets + Echo) · Forum / community platform · Notification center (email/SMS/push) · Newsletter platform · Social network backend · Event management & ticketing · Q&A platform (Stack Overflow clone)

### APIs & Integrations (8)
RESTful API with versioning · GraphQL API (Lighthouse) · Webhook receiver & processor · Payment gateway integration (Stripe, PayPal) · SMS gateway integration (Twilio) · Third-party OAuth integration · Data sync pipeline (external APIs → DB) · Zapier-like automation platform

### Admin & Dashboards (7)
Filament v3 admin panel · Analytics dashboard with charts · Multi-role user management system · Audit log system · System health monitor · Feature flag management · A/B testing backend

### File & Media Processing (6)
Image upload + resize pipeline (queued) · PDF generation service · CSV/Excel import & export · File storage manager (S3/local) · Video transcoding job pipeline · OCR document processing pipeline

### Auth & Security (6)
Full auth system (login, 2FA, OAuth, magic links) · API token management (Sanctum/Passport) · Role-based access control (RBAC) · Permission management system · SSO integration · Audit trail & activity log

### Queues & Background Processing (6)
Email campaign sender (bulk, queued) · Report generation engine · Scheduled data exports · Background scraper / data fetcher · Retry-safe payment processor · Async notification dispatcher

### Real-time & Live Features (6)
Live dashboard (Livewire + polling) · Real-time order tracking · Live auction bidding · Collaborative document editing backend · Live sports scores / leaderboard · Chat with typing indicators

### DevOps & Infrastructure (6)
Docker + Laravel Sail setup · CI/CD pipeline config (GitHub Actions) · Horizon queue monitoring setup · Telescope debugging setup · Octane performance setup (Swoole/FrankenPHP) · Multi-environment config management

### Packages & Libraries (5)
Custom Laravel package with service provider · Reusable Livewire component library · Custom Artisan command suite · Laravel macro collections · Custom validation rule library

### Multi-language & Localization (3)
i18n system with locale switching · RTL support setup · Translation management backend

### Reporting & Analytics (5)
Custom report builder · Scheduled PDF/Excel exports · Dashboard with KPI widgets · Data aggregation pipelines · Cohort analysis backend

### Geolocation (4)
Location-based search (nearby X) · Delivery zone management · GPS tracking backend · Maps integration (Google/Mapbox)

### Machine Learning Integration (4)
Recommendation engine backend · Sentiment analysis pipeline · Image classification via external API · Predictive analytics data prep

### Compliance & Legal (4)
GDPR data export/erasure endpoints · Cookie consent management · Terms acceptance tracking · Data retention policies (auto-purge jobs)

### Monetization (5)
Stripe billing with webhooks · Usage-based billing metering · Trial management system · Promo/referral code engine · Affiliate tracking system

### IoT & Data Ingestion (4)
High-volume event ingestion API · Device registration & management · Time-series data storage · Alert/threshold monitoring jobs

### Workflow & Automation (4)
State machine workflows (model states) · Approval chain system · Automated email sequences · Trigger-based action engine

### Healthcare & Education (6)
Patient management system · Appointment scheduling with reminders · Telemedicine backend · LMS (courses, lessons, quizzes, progress) · Student enrollment system · Certificate generation pipeline

### Finance & Fintech (6)
Wallet system (deposits, withdrawals, transfers) · Transaction ledger · Currency exchange backend · Loan management system · Budget tracking API · Tax calculation engine

### Logistics & Supply Chain (5)
Order fulfillment pipeline · Warehouse management system · Shipment tracking integration · Supplier management portal · Purchase order system

### Gaming & Entertainment (5)
Leaderboard system · Achievement/badge engine · Virtual currency system · Matchmaking backend · Game session management

### Internal Tools (5)
Employee directory · Internal ticketing system · Expense management · Asset tracking · Onboarding workflow automation

### API Gateway Patterns (5)
Rate limiting per client · API key rotation system · Request logging & replay · Circuit breaker pattern · Multi-tenant API scoping

### Emerging & Niche (10)
AI chatbot backend (OpenAI/Claude API integration) · RAG pipeline (embeddings, vector search, context retrieval) · Crypto payment processor (on-chain verification) · NFT metadata API · Decentralized identity verification backend · Carbon footprint tracking system · Smart home device backend (MQTT + WebSockets) · Drone fleet management API · Satellite data ingestion pipeline · Digital twin backend

### Platform Engineering (6)
Feature flag system (LaunchDarkly clone) · Error tracking service (Sentry clone) · Log aggregation API · Uptime monitoring backend · Synthetic monitoring job system · Cost tracking & billing metering

### Developer Tools (5)
CLI tool with Laravel Zero · Code snippet manager API · API documentation generator · Schema migration diff tool · Webhook testing/debugging platform (RequestBin clone)

---

# Appendix B — Glossary

| Term | Definition |
|------|------------|
| **Catalog** | The 169-type list grouped in 23 business categories |
| **Invariant** | Business rule that must never break |
| **Decomposition** | Translating domain → entities, ownership, states, rules before code |
| **7-layer model** | Identity through Queries — design checklist |
| **NOUNS→OUTPUTS** | Six-step map from domain to Laravel artifacts |
| **Spec-Kit** | Greenfield SDD: constitution, spec, plan, tasks, implement |
| **OpenSpec** | Change-based SDD: proposal, apply, archive |
| **Superpowers** | Execution skills: TDD, debug, verify, review |
| **Laravel Specialist** | Implementation skill for Laravel 10+ patterns |
| **Aggregate root** | Entity that guards consistency for a cluster |
| **Ubiquitous language** | Code vocabulary matches business vocabulary |
| **Bounded context** | Same word, different meaning in different subdomains |
| **Greenfield** | New project from scratch |
| **Filament** | Admin panel UI for Laravel |

---

## Recommended build path (when you return from AFK)

1. **Study:** Complete Principle 6 schedule (Day 1 quiz today).  
2. **Plan:** Multi-vendor marketplace — decomposition already in `LARAVEL_SPECIALIST_CAPABILITIES.md`.  
3. **SDD:** Spec-Kit init in `examples/marketplace-v1` OR OpenSpec on next `jira-v2` feature.  
4. **Execute:** Superpowers TDD + `laravel-specialist`.

---

*Packet generated for study while AFK. Format: Markdown. Source: `d:\laravel13.x\LARAVEL_SPECIALIST_CAPABILITIES.md`.*
