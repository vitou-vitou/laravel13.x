# Senior Laravel Developer — God Mode Playbook

> **Stack:** Laravel 13.x · PHP 8.4/8.5 · Local Dev (Docker/Sail) · Production (Render)
> **Audience:** You. Forever. From day one to retirement.
> **Purpose:** This is the complete operating system of a senior Laravel engineer who ships, scales, sleeps, and wins.

---

## Table of Contents

| # | File | Topic | Why It Matters |
|---|------|-------|----------------|
| 01 | [Mindset & Principles](01-mindset-and-principles.md) | How a senior thinks | The difference between mid and senior is mental, not technical |
| 02 | [Local Development Setup](02-local-development-setup.md) | Docker, Sail, Herd, IDE | Your daily battlefield |
| 03 | [Project Scaffolding](03-project-scaffolding.md) | Starter kits, init flow, conventions | First 30 minutes shape next 3 years |
| 04 | [Architecture Patterns](04-architecture-patterns.md) | Services, Actions, DDD-lite, hexagonal | Code that survives 5 years |
| 05 | [Database & Eloquent Mastery](05-database-eloquent.md) | Migrations, queries, N+1, indexes | 80% of bugs live here |
| 06 | [Testing Strategy](06-testing-strategy.md) | Pest, feature, unit, browser, CI | Your safety net |
| 07 | [Security Playbook](07-security-playbook.md) | OWASP, auth, CSRF, secrets | One leak ends your career |
| 08 | [Performance Optimization](08-performance-optimization.md) | Caching, queues, profiling, indexes | The line between "works" and "scales" |
| 09 | [Render Hosting Mastery](09-render-hosting-mastery.md) | Services, DBs, secrets, scaling, gotchas | Production lives here |
| 10 | [Deployment & CI/CD](10-deployment-cicd.md) | GitHub Actions, render.yaml, zero-downtime | Ship without fear |
| 11 | [Queues, Jobs, Scheduling](11-queues-jobs-scheduling.md) | Background work that doesn't break | Async or die |
| 12 | [Debugging & Monitoring](12-debugging-monitoring.md) | Telescope, Sentry, logs, oncall | When prod burns at 3am |
| 13 | [Cost Optimization](13-cost-optimization.md) | Render bill control, infra discipline | Profitable apps |
| 14 | [Career Longevity](14-career-longevity.md) | Habits, contracts, retirement, mental health | Why you're doing this |

---

## How to Use This Playbook

### As a Beginner
Read 01 → 02 → 03 → 05 → 06 in order. Skip the rest until you have a project shipping.

### As a Mid Dev
Skim everything. Bookmark 04, 07, 08. Implement 10 on every project.

### As a Senior
This is your reference. Open the file you need. Don't re-read what you already know.

### As a Tech Lead
Use this to onboard your team. Translate it to your own context.

### When You're Stuck
Open 12. Then 04. Then 08. Most problems are in one of those three buckets.

---

## The Core Doctrine

1. **Ship working software.** Theory loses to working code that paid the bills last month.
2. **Boring is good.** Boring tech doesn't wake you up at 3am.
3. **Local mirrors production.** If it works locally and breaks in prod, you skipped step 1.
4. **The framework is enough.** 95% of "we need a microservice" conversations end with "we needed a queue."
5. **Tests are leverage.** They let you sleep, refactor, and quit a job without guilt.
6. **Logs > guesses.** If you don't know why, read the log. If there's no log, add one.
7. **Money is a feature.** Cheap infrastructure that scales beats expensive infrastructure that doesn't.
8. **Retirement is the real deliverable.** Optimize for the version of you in 20 years.

---

## Stack Decisions (Locked In)

| Layer | Choice | Why |
|-------|--------|-----|
| PHP | 8.4 (8.5 when stable) | JIT, typed properties, asymmetric visibility |
| Framework | Laravel 13.x | LTS-friendly, mature, agentic-ready |
| Local | Laravel Sail / Herd Pro | Mirrors prod Docker |
| Frontend | Vue 3 + Inertia v3 + TS | One-team SPA without API hell |
| CSS | Tailwind v4 | CSS-native config, no JS overhead |
| DB | PostgreSQL 16 | Better than MySQL for 95% of cases |
| Cache | Redis 7 | Sessions, queues, cache |
| Queue | Redis-backed | Simpler than SQS until 100k jobs/day |
| Files | AWS S3 (or Backblaze B2) | Render disk is ephemeral |
| Email | Resend / Postmark | Better than SES for transactional |
| Hosting | Render | Heroku's spiritual successor |
| CI | GitHub Actions | Free for public, cheap for private |
| Monitoring | Sentry + Render logs + UptimeRobot | Triangle of truth |
| Errors | Flare (Spatie) | Made for Laravel |

---

## What This Playbook Is NOT

- Not a Laravel tutorial. Read the docs at https://laravel.com/docs.
- Not a "10 tips" listicle. This is the OS, not the marketing.
- Not vendor-agnostic. Render is the host. Period.
- Not a beginner's "Hello World." We assume you can write a Controller.

---

## The Promise

If you internalize every file in this playbook and apply it religiously, by year 3 you will:

- Ship features 3× faster than peers
- Sleep through deployments
- Charge $150-300/hr as a freelancer or pull $180k+ as an employee
- Refuse projects that don't match your standards
- Have a portfolio of apps you can re-sell or open-source
- Retire on your own terms

This is the path. Walk it.
