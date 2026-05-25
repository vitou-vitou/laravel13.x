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

## Domain Decomposition — The Master Skill

### Why Decomposition > Framework Knowledge

Most developers learn frameworks. Few master decomposition. Result:

```
Framework expert without decomposition:
→ Builds the wrong thing perfectly
→ Misses invariants → prod bugs
→ Rewrites when requirements shift

Decomposition master with any framework:
→ Builds the right thing, learns framework as needed
→ Catches edge cases before code exists
→ Adapts when domain evolves
```

**Framework = syntax. Decomposition = thinking.**

---

### The Mental Model Shift

Stop thinking in code. Think in **domain language** first.

```
WRONG approach:
"I need a users table, orders table, products table..."

RIGHT approach:
"A Customer places an Order containing OrderLines.
 Each OrderLine references a Product variant.
 An Order transitions through states.
 Payment and fulfillment are separate lifecycles."
```

Same database. Completely different architecture. Second one survives 3 years of product changes. First one gets rewritten at month 6.

---

### The 3 Levels of Domain Understanding

```
Level 1 — SURFACE     Entities and CRUD operations
Level 2 — BEHAVIOR    State machines, workflows, business rules
Level 3 — INVARIANTS  What can never be violated under any circumstance
```

Most developers stop at Level 1. Production bugs live at Level 3.

---

### How to Reach Level 3: The Invariant Hunt

**Technique: Ask "what if" until you find the constraint that cannot move.**

Example domain: **Online Auction**

```
What if two people bid the same amount at the same time?
→ Only one can win → need atomic bid acceptance

What if the seller cancels after bids exist?
→ Bidders must be notified + deposits returned → cancellation has consequences

What if the winner doesn't pay?
→ Second-highest bidder gets offered the item → reserve winner logic

What if someone bids on their own auction?
→ Must be prevented → self-bid invariant

What if the auction clock hits zero during bid processing?
→ Bid in-flight at deadline — accept or reject? → need explicit rule
```

Each "what if" exposes an invariant. **These become your service layer rules, DB constraints, and test cases.**

---

### The Ubiquitous Language Principle

Stolen from Domain-Driven Design (DDD). Core idea:

**Code must use the same words the business uses.**

```
Business says:    "The order is fulfilled"
Bad code:         $order->status = 4;
Good code:        $order->fulfill();

Business says:    "Suspend the account"
Bad code:         $user->active = false;
Good code:        $account->suspend(reason: $reason);
```

Why it matters:
- Bug reports map directly to code
- New developers understand intent immediately
- Domain experts can review logic without translation

**In Laravel:**
```php
// Bad
$order->update(['status' => 'shipped', 'shipped_at' => now()]);

// Good
$order->ship(carrier: $carrier, trackingNumber: $tracking);
// Method encapsulates: status change + timestamp + event dispatch + notification
```

---

### The Aggregate Root Pattern

Not everything is equal. Some entities **own** others and protect invariants across them.

```
Order (aggregate root)
  ├── OrderLines      (owned — cannot exist without Order)
  ├── ShippingAddress (owned — value object)
  └── Payment         (associated — has its own lifecycle)
```

Rules:
- Only modify `OrderLine` through `Order`
- `Order` enforces: total recalculation, status consistency, line limit rules
- External code never touches `OrderLine` directly

**In Laravel:**
```php
// Wrong — bypasses aggregate
OrderLine::create([...]);

// Right — Order enforces invariants
$order->addLine(product: $product, qty: 2);
// Internally: creates line, recalculates total, checks max lines, fires event
```

---

### Commands vs Queries — The CQRS Insight

Every operation is either:

```
COMMAND → changes state, has side effects, returns nothing (or minimal ack)
QUERY   → reads state, no side effects, returns data
```

Separating them:
- Commands go through service layer (validation, events, jobs)
- Queries go direct to DB (optimized reads, no business logic overhead)

**In Laravel:**
```php
// Command — through service
app(OrderService::class)->placeOrder($customer, $cart);

// Query — direct, optimized
Order::with(['lines.product', 'customer'])
    ->where('customer_id', $id)
    ->latest()
    ->paginate(20);
```

Never mix. A method that reads AND writes is a hidden bug waiting to happen.

---

### The Bounded Context — When One Model Isn't Enough

Same word, different meaning in different parts of the system:

```
"Product" in Catalog context:
  → name, description, images, SEO, categories

"Product" in Inventory context:
  → SKU, stock level, warehouse location, reorder point

"Product" in Pricing context:
  → base price, discount rules, tax class, currency variants

"Product" in Order context:
  → snapshot at time of purchase (price locked, immutable)
```

**One Product model trying to do all four = a 40-column table with God-object smell.**

Solution: separate models per context, shared ID as reference.

```php
// Catalog
App\Catalog\Models\Product

// Inventory
App\Inventory\Models\StockItem  (references product_id)

// Order (immutable snapshot)
App\Orders\Models\OrderLineSnapshot  (copied data at order time)
```

---

### The Event Storming Shortcut

Fast way to decompose any domain in a team meeting:

```
Step 1: Write all DOMAIN EVENTS on orange stickies (things that happened)
        "OrderPlaced", "PaymentFailed", "ItemShipped", "AccountSuspended"

Step 2: Write COMMANDS that caused them on blue stickies
        "PlaceOrder" → "OrderPlaced"

Step 3: Write AGGREGATES that handle commands on yellow stickies
        Order aggregate handles PlaceOrder

Step 4: Write POLICIES that react to events on purple stickies
        "When PaymentFailed → send retry email after 1 hour"

Step 5: Write READ MODELS needed on green stickies
        "Customer needs order history with status"
```

30 minutes → full domain map → direct translation to Laravel code.

---

### Decomposition Anti-Patterns

**Anemic Domain Model**
```php
// Bad — model is just a data bag, logic lives in controller
class Order extends Model {} // no methods, just fillable

class OrderController {
    public function ship($id) {
        $order = Order::find($id);
        $order->status = 'shipped';      // raw state mutation
        $order->shipped_at = now();
        $order->save();
        // forgot to notify customer
        // forgot to decrement inventory
        // forgot to fire event
    }
}
```

**God Service**
```php
// Bad — one service knows everything
class OrderService {
    public function doEverything() { ... } // 800 lines
}

// Good — bounded responsibility
class OrderPlacementService { ... }
class OrderFulfillmentService { ... }
class OrderRefundService { ... }
```

**Primitive Obsession**
```php
// Bad
public function transfer(int $fromId, int $toId, float $amount, string $currency)

// Good — value objects carry invariants
public function transfer(WalletId $from, WalletId $to, Money $amount)
// Money enforces: positive amount, valid currency, precision rules
```

---

### The Decomposition Checklist

Before writing one line of code:

```
[ ] Named all core entities in domain language
[ ] Defined ownership boundaries (who owns what)
[ ] Mapped all lifecycle states per entity
[ ] Listed every invariant (what can NEVER happen)
[ ] Identified aggregate roots
[ ] Separated commands from queries
[ ] Found bounded contexts (same word, different meaning?)
[ ] Mapped domain events and their reactions
[ ] Asked the 7 hard questions (concurrency, immutability, compliance...)
[ ] Verified ubiquitous language matches business terminology
```

Complete this checklist → your architecture is 80% done before touching Laravel.

---

### The Ultimate Truth

```
Bad developer:   learns framework → tries to fit domain into framework patterns
Good developer:  understands domain → uses framework as implementation detail
Great developer: decomposes domain → framework choice becomes almost irrelevant
```

Laravel, Django, Rails, Spring — all capable. The difference in output quality comes entirely from how deeply the developer understood the domain before writing code.

**Decomposition is the senior engineer skill. Everything else is execution.**

---

## How to Get Decomposition Checklist for Your App

Ask directly in chat. Provide:

```
1. What the app does (one sentence)
2. Who uses it (roles)
3. The main "thing" the app manages
```

The decomposition runs live against your domain:

```
[ ] Core entities in domain language
[ ] Ownership boundaries
[ ] Lifecycle states per entity
[ ] Invariants (what can NEVER happen)
[ ] Aggregate roots
[ ] Commands vs queries
[ ] Bounded contexts
[ ] Domain events + reactions
[ ] 7 hard questions (concurrency, immutability, compliance...)
[ ] Ubiquitous language check
```

Output: full domain map → ready to build in Laravel.

---

## Live Decomposition Example: Multi-vendor Marketplace

### 1. Core Entities (Domain Language)

```
Customer        — buys products
Vendor          — sells products, manages their store
Product         — listed item with variants
ProductVariant  — SKU-level (size, color, stock)
Order           — customer purchase (may span multiple vendors)
OrderGroup      — subset of Order belonging to one Vendor
OrderLine       — single product+qty within OrderGroup
Cart            — pre-order state
Payment         — financial transaction against Order
Payout          — platform pays Vendor their earnings
Review          — Customer rates a Product or Vendor
Dispute         — Customer challenges an OrderGroup
Commission      — platform's cut per OrderGroup
```

---

### 2. Ownership Boundaries

```
Platform owns:  Categories, Tags, Commission rates, Dispute resolution, Payouts
Vendor owns:    Products, ProductVariants, their OrderGroups, their Payouts
Customer owns:  Cart, Orders, Reviews, Disputes they filed
```

**Critical:** Vendor can NEVER see another Vendor's data.

---

### 3. Lifecycle States

```
Product:
  draft → active → suspended | archived

ProductVariant:
  active → out_of_stock → discontinued

Cart:
  active → checked_out | abandoned | expired

Order:
  pending_payment → paid → processing → partially_shipped
  → shipped → delivered → completed | cancelled | refunded

OrderGroup (per vendor):
  pending → confirmed → processing → shipped → delivered
  → completed | cancelled | disputed

Payment:
  pending → processing → completed | failed | refunded

Payout:
  pending → scheduled → processing → completed | failed

Dispute:
  opened → under_review → resolved_buyer | resolved_vendor | escalated
```

---

### 4. Invariants (What Can NEVER Happen)

```
- Order total must equal sum of all OrderGroup totals
- Commission locked at order time (not payout time)
- Vendor cannot fulfill another Vendor's OrderGroup
- Payout cannot be released while Dispute is open on that OrderGroup
- Refund cannot exceed original Payment amount
- ProductVariant stock cannot go negative
- Customer cannot review a Product they didn't purchase
- Vendor cannot see Customer PII beyond shipping address for their orders
- Price on OrderLine is snapshot — immutable after order placed
- Cart cannot be checked out with out-of-stock variants
```

---

### 5. Aggregate Roots

```
Order (root)
  └── OrderGroups → OrderLines (owned)
  └── Payment (associated — own lifecycle)

Product (root)
  └── ProductVariants (owned)
  └── Reviews (associated)

Dispute (root)
  └── DisputeMessages (owned)
```

---

### 6. Commands vs Queries

**Commands (state-changing):**
```
AddToCart              → validates stock, creates/updates CartLine
Checkout               → creates Order + OrderGroups + locks prices
ProcessPayment         → charges gateway, updates Payment
ConfirmOrderGroup      → Vendor acknowledges their portion
ShipOrderGroup         → Vendor marks shipped + tracking number
FileDispute            → Customer challenges delivery/quality
ResolveDispute         → Admin rules in favor of buyer or vendor
ReleasePayout          → Platform pays Vendor after hold period
```

**Queries (read-only):**
```
Vendor dashboard        → their orders, revenue, pending payouts
Customer order history  → all orders with status
Admin dispute queue     → open disputes by age
Product catalog         → paginated, filtered, search
```

---

### 7. Bounded Contexts

```
"Product" means different things:

Catalog context:    name, description, images, SEO, categories
Inventory context:  SKU, stock_qty, reorder_point, warehouse
Pricing context:    base_price, sale_price, tax_class, currency
Order context:      SNAPSHOT — price+name locked at purchase time (immutable)
```

```
"User" means different things:

Auth context:       email, password, tokens, 2FA
Customer context:   shipping addresses, wishlist, order history
Vendor context:     store name, payout details, commission tier, rating
Admin context:      permissions, audit log access
```

---

### 8. Domain Events + Reactions

```
OrderPlaced           → notify all Vendors in order, reduce stock
PaymentCompleted      → unlock OrderGroups for fulfillment
OrderGroupShipped     → notify Customer with tracking, start delivery timer
OrderGroupDelivered   → start review window, start payout hold timer
DisputeOpened         → freeze Vendor payout, notify Admin
DisputeResolved       → release or refund accordingly, notify both parties
PayoutReleased        → notify Vendor, update ledger
ReviewPosted          → update Vendor rating aggregate
StockDepleted         → notify Vendor, mark variant out_of_stock
```

---

### 9. The 7 Hard Questions

```
1. CONCURRENCY
   Two customers buy last item simultaneously?
   → Pessimistic lock on ProductVariant stock during checkout
   → DB-level: check-then-decrement in single transaction

2. IMMUTABILITY
   What never changes after creation?
   → OrderLine price + product snapshot
   → Payment amount after completion
   → Commission rate on OrderGroup

3. COMPLIANCE
   What must be audited?
   → All Payment state changes
   → All Payout releases
   → Dispute resolutions (admin action log)
   → PII access by Vendors

4. TENANCY
   Who sees whose data?
   → Global scopes on Product, OrderGroup, Payout by vendor_id
   → Customer sees only their own Orders
   → Admin sees everything

5. EXTERNAL SYSTEM FAILURES
   Payment gateway down?
   → Queue retry with exponential backoff
   → Order stays in pending_payment, cart preserved
   Shipping API down?
   → Manual tracking fallback, alert Vendor

6. SILENT FAILURES
   Payout job fails silently?
   → Horizon monitoring + failed job alerts
   Stock not decremented on payment failure?
   → Compensating transaction in PaymentFailed listener

7. IRREVERSIBILITY
   What cannot be undone?
   → Released Payout (only new Payout can compensate)
   → Completed Dispute resolution
   → Posted Review (editable within 48h window only)
```

---

### 10. Ubiquitous Language Check

| Business Term | Code Term | Match? |
|--------------|-----------|--------|
| "Store" | `Vendor` | ✓ |
| "Listing" | `Product` | ✓ |
| "Purchase" | `Order` | ✓ |
| "Seller's cut" | `Payout` | ✓ |
| "Platform fee" | `Commission` | ✓ |
| "Claim" | `Dispute` | ✓ |

---

### Laravel Implementation Map

```
Entities          → Eloquent Models + Migrations
Global scopes     → Vendor/Customer data isolation
State machines    → spatie/laravel-model-states
Stock decrement   → DB::transaction + lockForUpdate()
Price snapshot    → OrderLine stores price at purchase time
Events            → Laravel Events + Listeners
Payout hold       → Scheduled job checks hold_until timestamp
Commission lock   → Stored on OrderGroup at creation
Dispute freeze    → Payout query checks for open disputes
Tests             → Pest feature tests per invariant
```

---

*Skill: laravel-specialist — activated via `Skill` tool in Claude Code*
