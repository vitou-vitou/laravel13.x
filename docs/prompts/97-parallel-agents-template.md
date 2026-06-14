# Generic 97-Task Parallel Agent Build Template

**Goal:** Build a complete [PROJECT NAME] (Laravel 13) — normally scoped as a 3-month roadmap — compressed into a single ~10-hour run using parallel agents.

**How to use:**
1. Fill in the bracketed placeholders (project name, slug, domain entities).
2. Replace the domain-specific batches (3-10) with tasks for your project type.
3. Keep the structural batches (1-2, 11-16) — they apply to almost any project.
4. Paste the finished "Master Orchestrator Prompt" into your main agent session.

---

## Master Orchestrator Prompt

```
You are the orchestrator for building a Laravel 13 "[PROJECT NAME]" — [ONE-LINE
DESCRIPTION OF THE APP, e.g. "an e-commerce storefront with catalog, cart,
checkout, and admin"].

Project path: examples/[project-slug] (create via ./bin/new-example if missing)

Use the dispatching-parallel-agents skill (.agents/skills/dispatching-parallel-agents/SKILL.md):
dispatch one agent per INDEPENDENT task slice below, run batches concurrently,
and integrate + run `php artisan test` after each batch.

CONSTRAINTS:
- Total wall-clock budget: 10 hours. Prioritize ruthlessly — if a batch risks
  blowing the budget, cut scope (stub UI, skip polish) rather than skip tests.
- TDD: every feature slice ships with feature tests (Spec-Kit + Superpowers TDD).
- No shared-file edits within the same batch — if two tasks touch the same file,
  put them in different batches (sequential), not the same batch (parallel).
- After each batch: run full test suite, fix conflicts, commit.

97 TASK SLICES (grouped into sequential batches of parallel, independent tasks):

BATCH 1 — Foundation (sequential, 1 agent — others depend on this)
1. Spec-Kit init + Breeze scaffold + base schema (users, [core entity 1], [core entity 2])

BATCH 2 — Core domain models (parallel, independent migrations/models)
[2-10. List your core models: migration + model + factory + policy for each
 main entity in your domain — e.g. for e-commerce: Product, Category, Cart,
 Order, OrderItem, Coupon, Payment, Review, Inventory]

BATCH 3 — Core CRUD features (parallel, separate controllers/routes)
[11-20. CRUD + key workflows for each entity above — e.g. product catalog CRUD,
 cart add/remove/update, checkout flow, order status transitions, coupon
 apply/validate, admin product management]

BATCH 4 — Views & UI (parallel, separate Blade views)
[21-30. List + detail + dashboard views for the main user-facing flows]

BATCH 5 — Search & filtering (parallel)
[31-35. Search, filters, sort, pagination, saved views for your main list pages]

BATCH 6 — Notifications & real-time (parallel)
[36-42. Order confirmation emails, status update notifications, real-time
 stock/order updates, etc. — adapt to domain]

BATCH 7 — Permissions & roles (parallel)
43. Spatie roles/permissions setup
44. Admin role (full access)
45. [Domain role 2, e.g. Vendor/Manager — scoped access]
46. [Domain role 3, e.g. Customer — own-data access]
47. Guest/viewer role (read-only, if applicable)
48. Policy tests for all roles x all CRUD actions

BATCH 8 — Reporting & analytics (parallel)
[49-54. Reports relevant to the domain — sales report, conversion funnel,
 inventory report, customer activity, exports]

BATCH 9 — Automation & workflow (parallel)
[55-60. Scheduled jobs, recurring processes, status automation, dependency
 chains relevant to the domain — e.g. abandoned cart reminders, low-stock
 alerts, auto-refund expired holds]

BATCH 10 — Integrations (parallel)
[61-66. External integrations — payment gateway, webhook, REST API (Sanctum),
 calendar/email/Slack, rate limiting]

BATCH 11 — Admin & settings (parallel, mostly domain-agnostic)
67. Admin: user management (CRUD, deactivate)
68. Admin: [domain entity] management
69. Admin: custom fields builder (if applicable)
70. Admin: workflow/status customization
71. Admin: audit log viewer
72. Org-level settings (timezone, branding, etc.)

BATCH 12 — UX polish & accessibility (parallel, domain-agnostic)
73. Responsive layout pass (mobile/tablet)
74. Dark mode
75. Accessibility audit fixes (design:accessibility-review skill)
76. Empty states + onboarding tooltips
77. Loading states / skeleton screens
78. Toast notifications for actions

BATCH 13 — Performance & hardening (parallel, domain-agnostic)
79. N+1 query audit + eager loading fixes
80. DB indexing pass on hot queries
81. Caching layer (counts, dashboards, catalogs)
82. Queue offloading for heavy jobs (exports, emails)
83. Rate limiting on public-facing routes
84. File upload size/type validation hardening (if applicable)

BATCH 14 — Security review (parallel, domain-agnostic)
85. Authorization re-audit (all policies)
86. CSRF/XSS spot checks on dynamic content
87. Mass-assignment guard review on all models
88. Sensitive data review (PII, payment info in logs/exports)
89. security-review skill pass on full diff

BATCH 15 — Testing & QA (parallel, domain-agnostic)
90. Feature test coverage gap-fill (target 90%+)
91. Browser/E2E smoke test (core happy-path flow end to end)
92. Load test critical endpoints
93. Cross-role permission test matrix

BATCH 16 — Docs & handoff (parallel, domain-agnostic)
94. README + setup docs
95. API docs (if Sanctum API built)
96. docs/NEXT_SESSION.md handoff doc
97. Final ROADMAP.md update marking phases complete

---
EXECUTION NOTES:
- Batch 1 is sequential and blocking — everything else depends on it.
- Don't dispatch more than ~10 agents per wave even with 97 tasks — reuse
  agent slots across waves.
- If 10-hour budget is at risk by Batch 10, defer Batches 10 and 12 to a
  follow-up session — core CRUD + permissions + tests (Batches 1-9, 14-15)
  are the must-ship MVP.
- After all batches: run `php artisan test`, then add the new project to
  docs/SESSION_STATE.md following the existing table format.
```

---

## Quick-fill examples for Batches 2-10 by project type

**E-commerce:** Product, Category, Cart, Order, OrderItem, Coupon, Payment, Review, Inventory, Wishlist
**Booking/Scheduling:** Provider, Service, Availability, Booking, Reminder, Cancellation, Payment
**CRM:** Contact, Company, Deal, Pipeline Stage, Activity/Note, Email Log, Task
**Helpdesk/Support:** Ticket, Category, Reply/Comment, SLA, Agent, Macro/Canned Response
**LMS:** Course, Module, Lesson, Enrollment, Quiz, Progress, Certificate
**Marketplace:** Vendor, Listing, Order, Commission, Payout, Review, Dispute

Use these as your starting entity list when filling Batches 2-3, then derive Batches 4-10 from the workflows each entity implies.
