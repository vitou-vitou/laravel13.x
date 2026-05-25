# Laravel Specialist — Full Capabilities

## Core Stack Coverage

| Layer | What's Covered |
|-------|---------------|
| Database | Migrations, schema design, indexing |
| Models | Eloquent ORM, relationships, scopes, casts, observers |
| Business Logic | Service classes, dependency injection, repositories |
| HTTP | Controllers, API resources, middleware, routing, versioning |
| Auth | Sanctum, Passport, 2FA, OAuth (Socialite), magic links, RBAC |
| Queues | Jobs, batching, Horizon, failure handling, retry logic |
| Real-time | Broadcasting, Laravel Echo, Pusher/Reverb, Livewire |
| Search | Scout + Meilisearch/Algolia, full-text, filters |
| Caching | Redis, cache tags, query caching, invalidation |
| Testing | Pest, PHPUnit, factories, feature + unit tests (>85% coverage) |
| Performance | Octane (Swoole/FrankenPHP), N+1 prevention, query optimization |
| Code Quality | PSR-12, PHP 8.2+ typed properties, enums, readonly |
| DevOps | Docker/Sail, GitHub Actions CI/CD, Forge/Envoyer configs |

---

## 180+ Buildable Project Types

### SaaS & Business
- Multi-tenant SaaS platform with subscription billing
- CRM (contacts, deals, pipelines, activities)
- Project management tool (tasks, boards, time tracking)
- Invoice & billing system
- HR management system (employees, leaves, payroll)
- Inventory management system
- Point of Sale (POS) backend
- Booking & reservation system
- Subscription box management platform
- Fleet management system

### E-Commerce
- Full e-commerce backend (products, cart, orders, payments)
- Digital products marketplace
- Multi-vendor marketplace
- Auction platform
- Dropshipping management system
- Wholesale ordering platform
- Rental/lending platform
- Coupon & discount engine

### Content & Media
- Blog / CMS with scheduled publishing
- News aggregator
- Podcast platform backend
- Video streaming platform backend
- Document management system
- Knowledge base / wiki
- Portfolio platform
- Job board

### Communication & Social
- Real-time chat system (WebSockets + Echo)
- Forum / community platform
- Notification center (email/SMS/push)
- Newsletter platform
- Social network backend
- Event management & ticketing
- Q&A platform (Stack Overflow clone)

### APIs & Integrations
- RESTful API with versioning
- GraphQL API (Lighthouse)
- Webhook receiver & processor
- Payment gateway integration (Stripe, PayPal)
- SMS gateway integration (Twilio)
- Third-party OAuth integration
- Data sync pipeline (external APIs → DB)
- Zapier-like automation platform

### Admin & Dashboards
- Filament v3 admin panel
- Analytics dashboard with charts
- Multi-role user management system
- Audit log system
- System health monitor
- Feature flag management
- A/B testing backend

### File & Media Processing
- Image upload + resize pipeline (queued)
- PDF generation service
- CSV/Excel import & export
- File storage manager (S3/local)
- Video transcoding job pipeline
- OCR document processing pipeline

### Auth & Security
- Full auth system (login, 2FA, OAuth, magic links)
- API token management (Sanctum/Passport)
- Role-based access control (RBAC)
- Permission management system
- SSO integration
- Audit trail & activity log

### Queues & Background Processing
- Email campaign sender (bulk, queued)
- Report generation engine
- Scheduled data exports
- Background scraper / data fetcher
- Retry-safe payment processor
- Async notification dispatcher

### Real-time & Live Features
- Live dashboard (Livewire + polling)
- Real-time order tracking
- Live auction bidding
- Collaborative document editing backend
- Live sports scores / leaderboard
- Chat with typing indicators

### DevOps & Infrastructure
- Docker + Laravel Sail setup
- CI/CD pipeline config (GitHub Actions)
- Horizon queue monitoring setup
- Telescope debugging setup
- Octane performance setup (Swoole/FrankenPHP)
- Multi-environment config management

### Packages & Libraries
- Custom Laravel package with service provider
- Reusable Livewire component library
- Custom Artisan command suite
- Laravel macro collections
- Custom validation rule library

### Multi-language & Localization
- i18n system with locale switching
- RTL support setup
- Translation management backend

### Reporting & Analytics
- Custom report builder
- Scheduled PDF/Excel exports
- Dashboard with KPI widgets
- Data aggregation pipelines
- Cohort analysis backend

### Geolocation
- Location-based search (nearby X)
- Delivery zone management
- GPS tracking backend
- Maps integration (Google/Mapbox)

### Machine Learning Integration
- Recommendation engine backend
- Sentiment analysis pipeline
- Image classification via external API
- Predictive analytics data prep

### Compliance & Legal
- GDPR data export/erasure endpoints
- Cookie consent management
- Terms acceptance tracking
- Data retention policies (auto-purge jobs)

### Monetization
- Stripe billing with webhooks
- Usage-based billing metering
- Trial management system
- Promo/referral code engine
- Affiliate tracking system

### IoT & Data Ingestion
- High-volume event ingestion API
- Device registration & management
- Time-series data storage
- Alert/threshold monitoring jobs

### Workflow & Automation
- State machine workflows (model states)
- Approval chain system
- Automated email sequences
- Trigger-based action engine

### Healthcare & Education
- Patient management system
- Appointment scheduling with reminders
- Telemedicine backend
- LMS (courses, lessons, quizzes, progress)
- Student enrollment system
- Certificate generation pipeline

### Finance & Fintech
- Wallet system (deposits, withdrawals, transfers)
- Transaction ledger
- Currency exchange backend
- Loan management system
- Budget tracking API
- Tax calculation engine

### Logistics & Supply Chain
- Order fulfillment pipeline
- Warehouse management system
- Shipment tracking integration
- Supplier management portal
- Purchase order system

### Gaming & Entertainment
- Leaderboard system
- Achievement/badge engine
- Virtual currency system
- Matchmaking backend
- Game session management

### Internal Tools
- Employee directory
- Internal ticketing system
- Expense management
- Asset tracking
- Onboarding workflow automation

### API Gateway Patterns
- Rate limiting per client
- API key rotation system
- Request logging & replay
- Circuit breaker pattern
- Multi-tenant API scoping

### Emerging & Niche
- AI chatbot backend (OpenAI/Claude API integration)
- RAG pipeline (embeddings, vector search, context retrieval)
- Crypto payment processor (on-chain verification)
- NFT metadata API
- Decentralized identity verification backend
- Carbon footprint tracking system
- Smart home device backend (MQTT + WebSockets)
- Drone fleet management API
- Satellite data ingestion pipeline
- Digital twin backend

### Platform Engineering
- Feature flag system (LaunchDarkly clone)
- Error tracking service (Sentry clone)
- Log aggregation API
- Uptime monitoring backend
- Synthetic monitoring job system
- Cost tracking & billing metering

### Developer Tools
- CLI tool with Laravel Zero
- Code snippet manager API
- API documentation generator
- Schema migration diff tool
- Webhook testing/debugging platform (RequestBin clone)

---

## What Gets Built Per Feature

Every implementation includes:

1. **Migration** — schema with proper indexes and foreign keys
2. **Model** — Eloquent with relationships, scopes, casts, factory
3. **Service class** — business logic separated from controller
4. **Controller** — thin, delegates to service
5. **API Resource** — clean response transformation
6. **Form Request** — validated input
7. **Job** — queued if long-running
8. **Feature test** — Pest, covers happy path + edge cases

---

## Validation Checkpoints

| Stage | Command | Expected |
|-------|---------|----------|
| After migration | `php artisan migrate:status` | All `Ran` |
| After routing | `php artisan route:list --path=api` | Routes present |
| After job | `php artisan queue:work --once` | No exception |
| After implementation | `php artisan test --coverage` | >85%, 0 failures |
| Before PR | `./vendor/bin/pint --test` | PSR-12 passes |

---

## PHP 8.2+ Features Used

- Readonly properties
- Backed enums
- Typed properties
- Named arguments
- Match expressions
- Fibers (via Octane)
- First-class callable syntax

---

## The Unlimited Projects Thesis — Deep Guide

### The Core Insight

Every software project reduces to the same primitives:

```
Input → Process → Store → Output
```

Laravel owns all four:

| Primitive | Laravel Tool |
|-----------|-------------|
| Input | HTTP (routes, requests, validation), CLI (Artisan), Events, Queues |
| Process | Services, Jobs, Actions, Pipelines, State Machines |
| Store | Eloquent (SQL), Redis (cache/queue), S3 (files), Scout (search) |
| Output | API Resources, Blade, Livewire, Notifications, Broadcasting |

**Any domain maps to these primitives. That's why it's unlimited.**

---

### The Constraint Is Never the Framework

When a project "can't be built in Laravel" the real reason is one of:

1. **Scale** — 10M req/sec needs Go/Rust at the edge *(not a Laravel problem — a cost/ops problem)*
2. **Real-time latency** — sub-10ms game loops need C++ *(not web backend territory)*
3. **ML compute** — training models needs Python/CUDA *(Laravel calls the Python API)*
4. **Hardware** — embedded systems need C *(Laravel is the backend, not the device)*

Everything else? Laravel handles it. The framework is never the bottleneck for 99% of business software.

---

### How to Approach Any Unknown Domain

When given a new domain, decompose it:

```
Domain → Entities → Relationships → Actions → Events → Outputs
```

**Example: Carbon Footprint Tracker**

```
Domain:     Environmental compliance

Entities:   Company, Facility, EmissionSource, Activity, Report

Relations:  Company hasMany Facilities
            Facility hasMany EmissionSources
            EmissionSource hasMany Activities

Actions:    LogActivity (queued job)
            CalculateEmissions (service, formula-driven)
            GenerateReport (queued job → PDF)
            AlertThreshold (event → notification)

Events:     ThresholdExceeded → notify compliance officer
            ReportGenerated  → email stakeholders

Outputs:    API (dashboard), PDF (regulators), CSV (auditors)
```

Every step maps directly to Laravel constructs. **No new tools needed.**

---

### The Domain Decomposition Pattern

Use this for ANY domain:

```
1. NOUNS    → Models + Migrations
2. VERBS    → Service methods or Jobs
3. RULES    → Validation + Policy + Business logic in Services
4. EVENTS   → Laravel Events + Listeners
5. TRIGGERS → Scheduled tasks or Queue jobs
6. OUTPUTS  → API Resources + Notifications + Exports
```

---

### Where Domain Knowledge Matters More Than Code

The framework is the easy part. Hard parts:

| Domain | Hard Part (not Laravel) |
|--------|------------------------|
| Finance | Double-entry bookkeeping rules, regulatory compliance |
| Healthcare | HL7/FHIR standards, HIPAA data boundaries |
| Logistics | Route optimization algorithms, carrier API quirks |
| Gaming | Game balance math, anti-cheat heuristics |
| ML/AI | Prompt engineering, embedding strategies, vector indexing |
| Crypto | Chain-specific transaction verification, gas estimation |

**Laravel builds the plumbing. You supply the domain expertise.**

---

### The Multiplier Effect

One Laravel app can serve multiple domains simultaneously:

```
Single codebase →
  ├── /api/v1        (REST API consumers)
  ├── Livewire UI    (internal ops team)
  ├── Queue workers  (background processing)
  ├── Scheduled jobs (automated reporting)
  ├── WebSockets     (real-time clients)
  └── CLI commands   (DevOps automation)
```

That's why the count is unlimited — combinations of domains + delivery mechanisms = exponential project types.

---

### Practical Takeaway

When someone says "can Laravel build X?" — the answer is almost always **yes**, unless X requires:

- Sub-millisecond hardware-level latency
- Native GPU compute
- Bare-metal OS interaction

Everything else: **decompose the domain, map to primitives, build.**

---

## The Honest Answer

Laravel is a general-purpose backend framework. If a problem needs:

- HTTP endpoints
- Database storage
- Background jobs
- Auth
- Real-time events
- Scheduled tasks

...Laravel handles it. The real answer is **unlimited project types** — constrained only by the problem domain, not the framework.

The 180+ list isn't exhaustive. It's representative. Any business domain you name, I can build the backend.

**Name your domain. I build it.**

---

## Domain Decomposition — Live Framework

### What "Decompose" Actually Means

Not "list features." Real decomposition = exposing the **invariants** of a domain.

```
Invariant = a rule that can NEVER be violated, no matter what
```

Find the invariants → you understand the domain. Miss them → bugs in production.

---

### The 7-Layer Decomposition Model

For any domain, answer these in order:

```
Layer 1: IDENTITY     — What are the core entities?
Layer 2: OWNERSHIP    — Who owns what? (tenancy, permissions)
Layer 3: LIFECYCLE    — What states can entities be in?
Layer 4: INVARIANTS   — What rules can never break?
Layer 5: COMMANDS     — What actions change state?
Layer 6: EVENTS       — What happened as a result?
Layer 7: QUERIES      — What do consumers need to read?
```

---

### Live Example 1: Hospital Management

**Layer 1 — Identity**
```
Patient, Doctor, Appointment, Prescription, Ward, Bed, MedicalRecord
```

**Layer 2 — Ownership**
```
Patient owns MedicalRecord (HIPAA — no cross-patient access)
Doctor owns Prescriptions they write
Hospital owns Wards/Beds
```

**Layer 3 — Lifecycle**
```
Appointment: scheduled → confirmed → in_progress → completed | cancelled | no_show
Bed:         available → occupied → cleaning → available
Prescription: draft → signed → dispensed → completed | voided
```

**Layer 4 — Invariants**
```
- A bed cannot be occupied by 2 patients simultaneously
- A doctor cannot have overlapping appointments
- A prescription cannot be dispensed without doctor signature
- MedicalRecord is immutable after signing (append-only)
```

**Layer 5 — Commands**
```
BookAppointment       → validates doctor availability, creates Appointment
AdmitPatient          → assigns Bed, creates AdmissionRecord
SignPrescription      → requires Doctor auth, locks Prescription
DischargePatient      → releases Bed, triggers cleaning job, generates discharge summary
```

**Layer 6 — Events**
```
AppointmentBooked     → notify patient (SMS/email)
PatientAdmitted       → notify ward nurse
PrescriptionSigned    → notify pharmacy queue
PatientDischarged     → trigger bed cleaning job, generate invoice
```

**Layer 7 — Queries**
```
Doctor's schedule for today
Available beds in Ward X
Patient's full medication history
Pending prescriptions for pharmacy
```

**Laravel Map:**
```
Entities      → Eloquent Models + Migrations
Ownership     → Policies + Gates + Scopes
Lifecycle     → Enum states + State machine (spatie/laravel-model-states)
Invariants    → Service layer validation + DB constraints
Commands      → Service methods + Form Requests
Events        → Laravel Events + Listeners + Notifications
Queries       → API Resources + Eager loading + Caching
```

---

### Live Example 2: Fintech Wallet

**Invariants (critical):**
```
- Wallet balance can NEVER go negative
- Every debit must have a corresponding credit (double-entry)
- Transaction is immutable after confirmed
- Concurrent transfers must not cause race conditions
```

**Laravel Solution per Invariant:**
```php
// Balance never negative → DB constraint + pessimistic lock
Wallet::lockForUpdate()->find($id);
// Never use optimistic lock here — race condition risk

// Double-entry → single DB transaction wrapping both journal entries
DB::transaction(function () use ($from, $to, $amount) {
    $from->debit($amount);
    $to->credit($amount);
    JournalEntry::createPair($from, $to, $amount);
});

// Immutable transactions → no update/delete on Transaction model
// Enforce via Model::saving() observer that blocks updates post-confirmation

// Race conditions → queue + lock
// Transfer jobs run sequentially per wallet via unique job ID
```

---

### Live Example 3: Multi-vendor Marketplace

**Ownership complexity:**
```
Platform owns: Categories, Tags, Dispute resolution
Vendor owns:   Products, Orders (their portion), Payouts
Buyer owns:    Cart, Order, Reviews
```

**Key Invariants:**
```
- Commission calculated at order time, not payout time (price lock)
- Vendor cannot see other vendor's data
- Dispute freezes payout until resolved
- Refund cannot exceed original payment
```

**Tricky part → multi-tenancy scoping:**
```php
// Global scope on every vendor-owned model
protected static function booted(): void
{
    static::addGlobalScope('vendor', function (Builder $query) {
        if (auth()->check() && auth()->user()->isVendor()) {
            $query->where('vendor_id', auth()->user()->vendor_id);
        }
    });
}
```

---

### The Questions That Expose Hidden Complexity

Ask these for any domain:

```
1. What can NEVER happen? (invariants)
2. What happens when two users act simultaneously? (concurrency)
3. What is irreversible? (immutability)
4. Who can see whose data? (tenancy/permissions)
5. What must be audited? (compliance)
6. What fails silently if you don't handle it? (edge cases)
7. What external system can go down? (resilience)
```

Answers to these 7 questions = the hard 20% of any project.

---

### Pattern: Complexity Heatmap

```
Low complexity  → CRUD (blog, catalog, directory)
Med complexity  → Workflows, state machines, multi-role (CRM, LMS, booking)
High complexity → Money, concurrency, compliance (fintech, healthcare, marketplace)
```

Higher complexity = more invariants = more service layer = more tests.

**Laravel handles all three tiers. Architecture changes, not the framework.**

---

### Bottom Line

Domain decomposition is the skill. Laravel is the tool.

Master decomposition → you can build anything, in any framework.

---

*Skill: laravel-specialist — activated via `Skill` tool in Claude Code*
