# 13 — Cost Optimization

> Cheap infrastructure that scales beats expensive infrastructure that doesn't. The senior dev knows every dollar.

---

## The Senior's Cost Philosophy

1. **Every $/mo is a feature you're not building.** Justify every line item.
2. **Right-size monthly.** Resources you used last month aren't what you need next.
3. **Free tiers compound.** $50/mo saved = $600/yr = paid vacation.
4. **Don't optimize prematurely.** $20/mo savings isn't worth a refactor.
5. **Don't optimize cheaply.** $5/mo savings on critical infra = 4am wake-ups.

---

## The Render Cost Map (2026 Pricing)

### Web/Worker/Cron Services

| Plan | RAM | CPU | $/mo | Use For |
|------|-----|-----|------|---------|
| Free | 512MB | shared | $0 | Dev/demo only (sleeps) |
| Starter | 512MB | 0.5 | $7 | MVP, small SaaS |
| Standard | 2GB | 1 | $25 | Growing app |
| Pro | 4GB | 2 | $85 | Stable production |
| Pro Plus | 8GB | 4 | $175 | Heavy load |
| Pro Max | 16GB | 8 | $350 | Big SaaS |

### Postgres

| Plan | Storage | RAM | $/mo | Connections | Backups |
|------|---------|-----|------|-------------|---------|
| Free | 1GB | shared | $0 | 97 | 7 days |
| Starter | 10GB | 256MB | $7 | 97 | 7 days |
| Standard | 50GB | 1GB | $35 | 97 | 7 days + manual |
| Pro | 250GB | 4GB | $135 | 197 | 7 days + PITR |
| Pro Plus | 500GB | 8GB | $260 | 397 | PITR |

### Key Value (Redis)

| Plan | RAM | $/mo |
|------|-----|------|
| Free | 25MB | $0 |
| Starter | 256MB | $10 |
| Standard | 1GB | $35 |
| Pro | 5GB | $135 |

---

## The Minimum Viable Production Stack

For an MVP that needs to feel real:

| Service | Plan | $/mo |
|---------|------|------|
| Web | Starter | $7 |
| Worker | Starter | $7 |
| Cron | Starter | $7 |
| Postgres | Starter | $7 |
| Redis | Starter | $10 |
| **Total** | | **$38** |

Plus:
- S3 (50GB): ~$1
- Sentry (free up to 10k events): $0
- Domain: $1/mo amortized
- Email (Resend free tier): $0

**Real total: $40/mo for production-grade Laravel.**

Cheaper than Heroku's $50 hobby tier with more capacity.

---

## The Healthy Growth Stack

When you have 1k-10k users:

| Service | Plan | $/mo |
|---------|------|------|
| Web | Standard (2× instances for HA) | $50 |
| Worker | Standard | $25 |
| Cron | Starter | $7 |
| Postgres | Standard | $35 |
| Redis | Standard | $35 |
| **Render Total** | | **$152** |

| Plus | $/mo |
|------|------|
| S3 (200GB) | $5 |
| Sentry Team | $26 |
| Logtail (logs) | $20 |
| Better Stack (uptime) | $25 |
| Postmark (10k emails) | $15 |
| Cloudflare (CDN) | $0 |
| **Total monthly** | **$263** |

At $263/mo, you can support 10k MAUs with healthy margins.

---

## The Scale Stack (100k+ users)

| Service | Plan | $/mo |
|---------|------|------|
| Web | Pro (3× instances) | $255 |
| Worker | Pro Plus (auto-scale 1-5) | $175-875 |
| Cron | Starter | $7 |
| Postgres | Pro Plus | $260 |
| Postgres read replica | Standard | $35 |
| Redis | Pro | $135 |
| **Total Render** | | **$867-1567** |

At this scale, evaluate:
- VPS migration (Hetzner $80/mo for 32GB box)
- Forge-managed AWS/DigitalOcean
- Laravel Cloud (usage-based)

But the operational simplicity of Render often justifies 30% premium.

---

## Where the Bill Sneaks Up

### 1. Staging Environments

Every staging = same cost as prod. If you have:
- prod ($263/mo)
- staging ($263/mo)
- demo ($263/mo)

= $789/mo with two empty environments.

**Cut:**
- Combine staging+demo
- Staging on smaller plan (Starter instead of Standard)
- Spin down staging at night (manual schedule)
- Use PR previews instead of permanent staging

### 2. Preview Environments

PR previews are great but cost ~$15-30/PR/day. Active team with 10 PRs/week:

```
10 PRs × 7 days × $20 = $1400/mo (if you forget to close)
```

**Fix:**
```yaml
previews:
  expireAfterDays: 3   # auto-close
```

### 3. Forgotten Services

You spun up a Redis for testing 8 months ago. Still billing $10/mo.

**Audit monthly:**
```bash
render services list
```

Delete unused. No mercy.

### 4. Workers That Don't Need to Be Up

A queue worker that processes 100 jobs/day doesn't need to be running 24/7.

**Option A:** Scale to 0 when queue is empty (Render doesn't do this natively, so use Lambda for spikes).

**Option B:** Use Render cron job to process queue every 5 minutes:
```yaml
- type: cron
  schedule: "*/5 * * * *"
  dockerCommand: php artisan queue:work --once --stop-when-empty --max-time=240
```

Saves $7-25/mo vs always-on worker.

### 5. Oversized Postgres

You picked Pro ($135/mo) because "it scales." But you use 2GB of 250GB and 10 connections of 197.

**Right-size:** Move to Standard ($35) until you actually need Pro. Save $1200/yr.

### 6. Egress Fees

If your DB is in us-west and your app is in us-east, you pay egress on every query. Painful.

**Fix:** Keep DB + app + Redis in same region. Use internal hostnames.

---

## Free Tier Hacks

### Free Tier Worth Using

| Service | Free Tier | Use For |
|---------|-----------|---------|
| Cloudflare | Unlimited bandwidth | CDN, DNS, basic WAF |
| GitHub Actions | 2000 min/mo private | CI |
| Render Web (free) | 750 hrs/mo (sleeps) | Demos, side projects |
| Render Postgres (free) | 1GB, 30-day | Side projects, MVPs |
| Render Redis (free) | 25MB | Development |
| Sentry | 5k events/mo | Personal projects, MVPs |
| Resend | 100 emails/day | Transactional MVP |
| Postmark | 100 emails/mo | Transactional MVP |
| S3 | 5GB free first 12 mo | Initial files |
| Cloudflare R2 | 10GB free | S3 alternative, no egress |
| Backblaze B2 | 10GB free | S3 alternative |
| UptimeRobot | 50 monitors | Site uptime |
| Logflare | 12.96M events/mo | Logging on a budget |

### The "$0 Side Project" Stack

| Service | Source | $/mo |
|---------|--------|------|
| Web | Render free (sleeps) | $0 |
| Postgres | Render free (1GB) | $0 |
| Domain (.dev) | Year 1 free with Cloudflare | $0 |
| DNS | Cloudflare | $0 |
| Email | Resend (100/day) | $0 |
| Sentry | Personal tier | $0 |
| Files | Cloudflare R2 (10GB) | $0 |
| **Total** | | **$0** |

Run side projects forever for free. Until they make money.

---

## Cost Discipline Per Layer

### Application Code

- Don't poll. Use webhooks. Cuts request load 90%.
- Cache aggressively. Redis is $10/mo, DB is $35-260/mo. Memory wins.
- Queue everything async. 1 worker box does 100k jobs/day.

### Database

- Index well. Slow queries = more CPU = bigger plan.
- Archive old data. Cold rows still cost storage.
- Vacuum regularly. Bloated tables hurt performance.
- Connection pool (PgBouncer). Multiplexes connections.

### Storage

- S3 lifecycle policies: move to Glacier after 90 days.
- Compress images. WebP/AVIF cut size 60%.
- CDN cached forever for hashed URLs.

### Bandwidth

- Compress responses (`gzip on` in nginx).
- Use ETags / Last-Modified.
- CDN static assets (Cloudflare, free).
- Don't return full PII tables when only 5 fields needed (`select()`).

---

## The Cost-Per-User Math

For your SaaS to be sustainable:

```
Revenue per user > Cost per user × 3
```

Cost per user breakdown:

| User Tier | Infra Cost | Pricing Floor |
|-----------|-----------|---------------|
| Free user | $0.05/mo | $0 (loss leader) |
| Light user (10 reqs/day) | $0.20/mo | $1/mo |
| Active user (100 reqs/day) | $0.80/mo | $4/mo |
| Power user (1000 reqs/day) | $4/mo | $15+/mo |
| Enterprise (custom) | $50+/mo | $500+/mo |

If your pricing is below floor: you're losing money on growth.

---

## When to Self-Host (Hetzner, etc.)

Render is great until ~$500-1000/mo, then VPS economics win.

| Solution | $/mo | Includes |
|----------|------|----------|
| Render Pro Plus | $175 | 8GB RAM, 4 CPU, managed |
| Hetzner CX42 | $20 | 8GB RAM, 4 CPU, you manage |
| Hetzner AX41 | $40 | 64GB RAM, 6 CPU, you manage |

Hetzner is 8-10× cheaper. But:
- You manage OS, security patches, monitoring
- You set up backups, replicas, failover
- You wake up at 3am

For 10x cost reduction, you need a team or DevOps skills.

**Bridge:** Use Forge ($12/mo) to manage Hetzner. You get Render-like UX on Hetzner pricing. Most senior Laravel devs do this once profitable.

---

## The Render → Forge Migration Math

Assume your Render bill is $500/mo. You'd save ~$400/mo on Hetzner + Forge.

```
Annual savings: $4800
One-time migration cost: ~$2000 (developer time)
Payback: 5 months
```

Worth doing once bill > $300/mo AND you have time.

Not worth doing if:
- Solo dev with $0 spare time
- Team without ops experience
- Compliance requires managed (HIPAA BAA)

---

## Cost Monitoring

### Render Billing Alerts

Render → Settings → Billing → set spending alert at $X/mo. Notification when approaching.

### Per-Project Tracking

```
projects:
  - my-saas: $263/mo
  - client-app: $150/mo
  - internal-tools: $50/mo
  - personal-portfolio: $0/mo
```

Maintain a spreadsheet. Quarterly review. Cut deadwood.

### Stripe Dashboard for SaaS

If you sell SaaS, watch:
- MRR (monthly recurring revenue)
- ARPU (avg revenue per user)
- Churn
- LTV (lifetime value)

```
Gross margin = (Revenue - Infra cost) / Revenue
```

Healthy SaaS: 70%+ gross margin. You're at 90% on Render Starter, 80% on Pro tier.

---

## Free Optimizations That Compound

| Move | Effort | Savings |
|------|--------|---------|
| Add OpCache + preload | 1 hour | 30% fewer CPU = smaller plan |
| Index missing FK columns | 1 hour | DB load down 50% |
| Use cursor pagination | 2 hours | Memory down 80% |
| Add HTTP caching headers | 1 hour | Bandwidth down 50% |
| Move static to Cloudflare | 1 hour | Bandwidth = $0 |
| Compress responses | 30 min | Bandwidth down 70% |
| Lazy-load Vue components | 2 hours | First load 80% smaller |
| Image optimization | 2 hours | Storage + bandwidth down 60% |
| Drop unused indexes | 30 min | Write throughput up 20% |

Most teams have $200/mo of optimizations sitting on the table.

---

## The "Don't Overoptimize" Discipline

Don't spend 40 hours saving $20/mo. Your hourly rate is higher.

Calculate before optimizing:
```
Hours required × your hourly rate = cost of optimization
Annual savings × 3 = value of optimization

If cost > value × 3: don't do it
```

Example:
- 8 hours of work × $100/hr = $800 cost
- $30/mo savings × 12 months = $360/yr value
- Cost > value: SKIP

Senior devs say no to small optimizations to focus on big ones.

---

## The Cost-Conscious Senior's Habits

| Cadence | Action |
|---------|--------|
| Weekly | Glance at Render bill |
| Monthly | Audit services, kill unused |
| Quarterly | Right-size based on actual usage |
| Yearly | Evaluate Forge/VPS migration |

Pricing changes. Tools evolve. Re-evaluate annually.

---

## The Profitability Mindset

Cost optimization is not about being cheap. It's about being **profitable**.

Profitable = sustainable = freedom = retirement.

Bad: $5000/mo infra, $4000/mo revenue → bankrupt in 3 months
Good: $500/mo infra, $5000/mo revenue → growth runway

Senior devs build apps that pay for themselves from day one of revenue.
