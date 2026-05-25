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

## 100+ Buildable Project Types

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

*Skill: laravel-specialist — activated via `Skill` tool in Claude Code*
