# What people work on & talk about in Laravel development — grouped by timescale and topic

**Date:** 2026-11-01
**Question:** "Explore what people talk about developing of Laravel in daily, weekly, monthly, year group by topic."

**Method:** Researched against **primary sources only** — the first-party record of Laravel development:
the official release/support policy (`laravel/docs` → `releases.md`), the framework commit log and release
tags (`laravel/framework` via the GitHub API), and the app-skeleton changelog/releases (`laravel/laravel`,
including the copy vendored in this repo). Every claim below cites the source that owns it. Community
*sentiment* (Reddit, X/Twitter, forums, Discord) is **not** primary-source verifiable and is quarantined in
**§7 Unverified / out of scope** — nothing there is presented as fact.

**Where this lives:** `docs/research/`, matching the existing convention (kebab-case Markdown research
notes alongside `laravel-11-crud-primary-sources.md`, `laravel-usage-by-country.md`, etc.).

**Operationalisation (read this first):** "What people talk about developing of Laravel" is not a metric a
single primary source reports. The most defensible first-party proxy is **the development record itself**:
commit/PR titles (what contributors are literally working on and discussing per change), release notes
(what maintainers say a release is *about*), and the official docs topic taxonomy. This note reads that
record across the four time horizons and groups it by topic. Where a claim is an interpretation rather than
a sourced fact, it is marked **[inference]**.

---

## 0. The cadence is explicitly documented (start here)

- **Primary claim:** Laravel ships **one major release per year (~Q1)**, and **minor/patch releases "as
  often as every week."** Verbatim: *"Major framework releases are released every year (~Q1), while minor
  and patch releases may be released as often as every week. Minor and patch releases should never contain
  breaking changes."*
  — Source: [`laravel/docs` `releases.md` (13.x) → Versioning Scheme](https://github.com/laravel/docs/blob/13.x/releases.md)
- **Primary claim:** Support window is **bug fixes for 18 months, security fixes for 2 years**, so two major
  lines are maintained in parallel at any given time.
  — Source: [`laravel/docs` `releases.md` (13.x) → Support Policy](https://github.com/laravel/docs/blob/13.x/releases.md)
- **Primary claim — the yearly majors, with exact dates (the "year" column):**

  | Version | Release date | Bug fixes until | Security fixes until | Source |
  |---|---|---|---|---|
  | Laravel 10 | 2023-02-14 | 2024-08-06 | 2025-02-04 | [releases.md](https://github.com/laravel/docs/blob/13.x/releases.md) |
  | Laravel 11 | 2024-03-12 | 2025-09-03 | 2026-03-12 | [releases.md](https://github.com/laravel/docs/blob/13.x/releases.md) |
  | Laravel 12 | 2025-02-24 | 2026-08-13 | 2027-02-24 | [releases.md](https://github.com/laravel/docs/blob/13.x/releases.md) |
  | Laravel 13 | 2026-03-17 | ~Q3 2027 | 2028-03-17 | [releases.md](https://github.com/laravel/docs/blob/13.x/releases.md) |

  This confirms the **yearly (Q1) major cadence** and the overlapping 18-month / 2-year windows.

---
## 1. DAILY — individual commits: the granular "what are people working on today"

**Cadence (primary):** `laravel/framework` logged **2,682 commits in the last 52 weeks** (≈ **52 commits/week**,
≈ **7–10/day** on active days), with **continuous activity every single week** (no gaps), ranging **24–92
commits/week**. The trailing 12 weeks show 574 commits (≈ 48/week).
— Source: [`laravel/framework` `stats/commit_activity`](https://api.github.com/repos/laravel/framework/stats/commit_activity) (fetched 2026-11-01; current partial week shows 0).

**Topic mix (primary):** Keyword-tagged buckets over the **last 300 commit titles** on the active branch.
Buckets are **overlapping** (one commit can hit several) and are directional, not a strict partition.

| Topic (daily commit titles) | Hits / 300 | Share | Representative sourced example |
|---|---:|---:|---|
| **Bug fixes / regressions** | 108 | ~36% | *"Fix lazily loaded images not being freed until garbage collection (#62265)"* |
| **DX / API ergonomics / helpers** | 81 | ~27% | *"add Schedule::whenTaskRunsLongerThan (#62233)"*; *"insert before/after middleware helpers (#62261)"* |
| **Database / Eloquent / SQL / migrations** | 56 | ~19% | *"Fix forceIndex generating invalid SQL on SQLite (#62255)"*; *"Qualify index names with the table schema on PostgreSQL (#62213)"* |
| **Cache / Redis / sessions / locks** | 47 | ~16% | *"Fix Redis tagged cache flush blocking Redis when flushing many tags (#62215)"* |
| **Docs / code health / chores** | 44 | ~15% | *"Update facade docblocks"*; *"Update CHANGELOG"* |
| **Queues / scheduler / jobs / workers** | 41 | ~14% | *"dispatch ScheduledTaskSkipped when its skipped by on one server (#62256)"*; *"Show released jobs as released in queue:work output (#62180)"* |
| **HTTP / routing / views / pagination** | 39 | ~13% | *"Fix pagination contracts render() return type (#62242)"* |
| **AI / Cloud / first-party services** | 33 | ~11% | *"Reconnect the Cloud log socket after a failed write (#62258)"* |
| **Testing / fakes** | 29 | ~10% | *"Centralize + improve tests for `InteractsWithData` (#62217)"* |
| **Security / auth / validation** | 11 | ~4% | *"Drop check constraints when dropping columns on SQL Server (#62222)"* (schema-safety); forgery-protection work is folded into the weekly/annual view |

— Source for all rows: [`laravel/framework` commits (titles)](https://api.github.com/repos/laravel/framework/commits), sampled 2026-11-01 (300 titles); examples are verbatim commit subjects.

**[inference] Daily headline:** the day-to-day conversation is dominated by **correctness (bug fixes)** and
**developer-experience ergonomics**, with the database/Eloquent layer and cache/Redis as the steadiest
subsystem topics. New feature work is present but is the minority of daily traffic.

---

## 2. WEEKLY — patch/minor releases and continuous multi-version maintenance

**Cadence (primary):** The framework releases **multiple times per week**. In the ~8 weeks from **2026-09-01
to 2026-10-27** alone there were **18 tagged releases** spanning **both active major lines simultaneously**
(`v13.30.0 → v13.39.0` on 13.x and `v12.69.0 → v12.70.2` on 12.x), often cut the **same day**.
— Source: [`laravel/framework` releases](https://github.com/laravel/framework/releases) (fetched 2026-11-01); latest release `v13.39.0` @ 2026-10-27.

**What a weekly release "talks about" (primary):** the `laravel/laravel` app-skeleton changelog records the
week-scale theme as small, safe, quality-of-life changes — never breaking ones. Verbatim entries:

- *"Render JSON exceptions for API routes by default"* — `v13.8.0` (2026-05-25)
- *"Use the Vite font plugin for application fonts"* — `v13.5.0` (2026-04-30)
- *"enable npm audit by default"* / *"remove axios and enable ignore-scripts"* — `v13.3.0` / `v13.2.0`
- *"Remove Pdo/Mysql const workaround"* — `v13.6.0` (2026-05-11)

— Source: local `CHANGELOG.md` (this repo, `laravel/laravel` skeleton) and [`laravel/laravel` releases](https://github.com/laravel/laravel/releases) (e.g. `v13.8.0` 2026-05-27, `v13.11.0` 2026-10-06).

**[inference] Weekly headline:** the weekly conversation is **maintenance + incremental defaults/tooling**
(front-end build config, dependency hygiene, exception rendering), executed as **parallel patch trains on two
supported majors** — which is exactly what the 18-month/2-year support policy forces.

---

## 3. MONTHLY — minor-release trains and cumulative subsystem themes

**Cadence (primary):** Monthly windows aggregate several weekly drops into a visible **minor-version climb**.
On the skeleton, `v13.1.0 (2026-03-18) → v13.8.0 (2026-05-25)`; on the framework, `v13.30.0 (2026-09-01) →
v13.39.0 (2026-10-27)` — i.e. **~10 minor releases across ~2 months** on the current line.
— Sources: [`laravel/laravel` releases](https://github.com/laravel/laravel/releases); [`laravel/framework` releases](https://github.com/laravel/framework/releases).

**Cumulative monthly topic themes (primary, from the same commit/release record):** the recurring month-scale
themes are **queue/scheduler robustness**, **cache/Redis behaviour**, **database portability (SQLite /
PostgreSQL / SQL Server dialect fixes)**, and **PHP-attribute / API ergonomics expansion**. Each is evidenced
by the daily-commit examples in §1 that recur across weeks (e.g. scheduler events `#62256`/`#62141`, Redis
tag flush `#62215`, SQL Server/PostgreSQL fixes `#62239`/`#62213`).
— Source: [`laravel/framework` commits](https://api.github.com/repos/laravel/framework/commits) and [releases](https://github.com/laravel/framework/releases).

**[inference] Monthly headline:** at month scale the individual fixes resolve into **subsystem pushes** —
make queues/schedulers more observable, make cache/Redis safe under load, and make the query layer portable
across drivers.

---
## 4. YEARLY — the major version is where the big thematic "what people talk about" lives

**Primary claim:** The Laravel 13 major release (2026-03-17) is explicitly themed, in maintainers' own words:
*"Laravel 13 continues Laravel's annual release cadence with a focus on AI-native workflows, stronger
defaults, and more expressive developer APIs."*
— Source: [`laravel/docs` `releases.md` (13.x) → Laravel 13](https://github.com/laravel/docs/blob/13.x/releases.md)

**The yearly topics, verbatim from the release notes (each is a sourced sub-claim):**

- **Laravel AI SDK (first-party AI primitives):** text generation, tool-calling agents, embeddings, audio,
  images, vector-store integrations — *"providing a unified API… provider-agnostic AI features while keeping
  a consistent, Laravel-native developer experience."* — [releases.md → Laravel AI SDK](https://github.com/laravel/docs/blob/13.x/releases.md)
- **JSON:API resources (first-party):** resource serialization, relationship inclusion, sparse fieldsets,
  JSON:API-compliant headers. — [releases.md → JSON:API Resources](https://github.com/laravel/docs/blob/13.x/releases.md)
- **Semantic / vector search:** native vector query support + embeddings (PostgreSQL + `pgvector`), e.g.
  `DB::table('documents')->whereVectorSimilarTo(...)`. — [releases.md → Semantic/Vector Search](https://github.com/laravel/docs/blob/13.x/releases.md)
- **Request-forgery protection hardened:** formalized as `PreventRequestForgery`, origin-aware request
  verification alongside token-based CSRF. — [releases.md → Request Forgery Protection](https://github.com/laravel/docs/blob/13.x/releases.md)
- **Queue routing by class:** `Queue::route(ProcessPodcast::class, connection: 'redis', queue: 'podcasts')`. — [releases.md → Queue Routing](https://github.com/laravel/docs/blob/13.x/releases.md)
- **Expanded PHP attributes (declarative config):** `#[Middleware]`, `#[Authorize]`, `#[Tries]`, `#[Backoff]`,
  `#[Timeout]`, `#[FailOnTimeout]`, plus attributes across Eloquent/events/notifications/validation/testing. — [releases.md → Expanded PHP Attributes](https://github.com/laravel/docs/blob/13.x/releases.md)
- **Cache TTL extension:** `Cache::touch(...)` to extend a TTL without re-storing. — [releases.md → Cache TTL Extension](https://github.com/laravel/docs/blob/13.x/releases.md)
- **"Minimal breaking changes" as a stated philosophy:** *"Much of our focus during this release cycle has
  been minimizing breaking changes… shipping continuous quality-of-life improvements throughout the year."* — [releases.md → Minimal Breaking Changes](https://github.com/laravel/docs/blob/13.x/releases.md)
- **PHP floor raised to 8.3.** — [releases.md → PHP 8.3](https://github.com/laravel/docs/blob/13.x/releases.md)

**[inference] Yearly headline:** the annual conversation is where **bet-the-framework themes** land — in
2026 that is unmistakably **AI-native development (AI SDK + vector/semantic search)**, plus **API
expressiveness (PHP attributes, JSON:API)** and **security hardening**, deliberately shipped with **few
breaking changes**.

---

## 5. Topic × timescale matrix (summary)

| Topic | Daily (commits) | Weekly (patches) | Monthly (minor trains) | Yearly (major) |
|---|---|---|---|---|
| Bug fixes / correctness | ★★★ dominant | ★★★ (the point of patches) | ★★ | ★ (regressions from new features) |
| DX / API ergonomics | ★★ | ★★ (new helpers/defaults) | ★★ | ★★★ (PHP attributes, JSON:API) |
| DB / Eloquent / SQL | ★★ | ★★ (driver fixes) | ★★ | ★ |
| Cache / Redis / sessions | ★★ | ★ | ★★ | ★ (Cache::touch) |
| Queues / scheduler | ★★ | ★ | ★★ | ★★ (queue routing) |
| Security / auth | ★ | ★ | ★ | ★★★ (PreventRequestForgery) |
| **AI / vector search** | ★ (emerging) | ★ | ★ | ★★★ **(the 2026 headline)** |
| Testing / fakes | ★★ | ★ | ★ | ★ |
| Docs / code health | ★★ | ★ | ★ | ★ |

★ = relative prominence at that timescale **[inference]**, derived from the sourced counts in §1 and the
release-note themes in §2–§4.

---

## 6. The meta-topic now visible in the primary record: AI-assisted contribution

- **Primary claim:** A measurable share of recent `laravel/framework` commits are **co-authored by AI coding
  agents**. In a 200-commit sample, **18 commits (~9%)** carried agent co-author trailers such as
  `Co-authored-by: Claude Opus 5.5 <noreply@anthropic.com>`, `Co-authored-by: Amp`, `Claude-Session:`, and
  `Amp-Thread-ID: https://ampcode.com/threads/...`.
  — Source: [`laravel/framework` commits](https://api.github.com/repos/laravel/framework/commits) (message trailers), sampled 2026-11-01.
- **[inference]** This is "what people talk about when developing Laravel" in a literal, primary-source sense
  in 2026: **AI agents are now participants in the commit conversation**, and the framework's own yearly
  theme (§4, Laravel AI SDK) mirrors that shift.

---

## 7. Unverified / out of scope (NOT presented as fact)

These are things a reader might expect from "what people talk about," but which **cannot be verified against a
primary source** with the evidence gathered. They are listed explicitly and are **not** asserted above:

1. **Community sentiment / volume on Reddit, X/Twitter, Discord, Laracasts forums, Stack Overflow.** No
   first-party source owns "how much people discuss topic X." Forum/social volume is secondary and
   platform-owned; not measured here. The closest first-party venue is [`laravel/framework` Discussions &
   Issues](https://github.com/laravel/framework/discussions), which was **not** sampled in depth — treat any
   "popular community topic" claim as unverified.
2. **That the commit-title keyword buckets are a precise taxonomy.** They are **overlapping, keyword-based
   approximations** over a 300-commit sample, not an official categorisation; a formal topic split would need
   label-by-label human coding.
3. **Per-day commit counts.** GitHub's `commit_activity` is **weekly** granularity; the "7–10/day" figure is
   the weekly average **[inference]**, not a directly sourced daily metric.
4. **Global "how much Laravel development happens daily vs yearly."** The numbers here are for the
   `laravel/framework` / `laravel/laravel` repositories specifically, not the whole ecosystem (there is no
   first-party ecosystem-wide activity metric).
5. **Download/star popularity of topics.** No primary source maps topics to adoption; Packagist/GitHub stars
   measure packages, not topics.

---

## Sources (primary)

- `laravel/docs` → [`releases.md` (13.x)](https://github.com/laravel/docs/blob/13.x/releases.md) — versioning, support policy, Laravel 13 themes (raw fetched 2026-11-01).
- `laravel/framework` → [commits](https://api.github.com/repos/laravel/framework/commits) (topic sample), [`stats/commit_activity`](https://api.github.com/repos/laravel/framework/stats/commit_activity) (weekly cadence), [releases](https://github.com/laravel/framework/releases) (weekly/multi-major cadence) — GitHub API, fetched 2026-11-01.
- `laravel/laravel` → [releases](https://github.com/laravel/laravel/releases) and the vendored [`CHANGELOG.md`](/workspace/CHANGELOG.md) in this repo (weekly/monthly app-skeleton topics).
