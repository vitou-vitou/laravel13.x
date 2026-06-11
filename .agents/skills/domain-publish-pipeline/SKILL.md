---
name: domain-publish-pipeline
description: >-
  End-to-end domain-to-publish pipeline for any topic: last30days research,
  domain decomposition, 8-principle study packet, content calendar, phased media
  (text, image, carousel, video), account setup, daily 3am scheduling, and
  reporting. Routes implementation through spec-kit-openspec-superpowers when
  building the automation app. Use for social content factories, niche
  accounts, Khmer cuisine, Angkor Wat, TikTok schedules, or "topic to daily post".
disable-model-invocation: true
---

# Domain Publish Pipeline

Turn **any domain** (e.g. Khmer kou sopeap, Angkor Wat temple) into a repeatable system: research → decompose → study → plan content → produce media → schedule (default **3:00 AM daily**) → report. **Framework is chosen later**; this skill defines artifacts and gates, not Laravel vs Next.js.

## Child skills (invoke explicitly)

| Phase | Skill | When |
|-------|-------|------|
| Research | **last30days** | Always first after intake |
| Learning packet | **8-principle-study** | After decomposition |
| SDD routing | **spec-kit-openspec-superpowers** | Before building automation |
| Codebase analysis | **laravel-specialist** | Only if scope is a Laravel app |
| Codebase study pair | **system-study-packet** | After MVP in `examples/*` |

## One-round intake (ask together if missing)

1. **Domain** — topic, niche, language, audience (e.g. Khmer food + Angkor history for English TikTok).
2. **Platforms** — TikTok, Instagram, X, etc. (default: TikTok).
3. **Account mode** — `manual` (user creates; agent documents credentials checklist) or `assisted` (agent drafts signup steps; user completes CAPTCHA/phone).
4. **Schedule** — default `03:00` local; confirm **timezone** (e.g. `Asia/Phnom_Penh`).
5. **Media ladder** — which phases now: text only | +image | +carousel | +video (default: text first).
6. **Output folder** — `{project-root}/docs/domains/{slug}/` or user path.
7. **Framework** — `deferred` until Phase 6 (user picks then).

If user gave an example topic only, infer slug (`angkor-khmer-cuisine`) and proceed.

## Master checklist

```
Pipeline progress — {slug}:
- [ ] 0 Intake recorded ({intake.md})
- [ ] 1 last30days research saved ({slug}-last30days-raw.md)
- [ ] 2 Domain decomposition ({slug}-decomposition.md)
- [ ] 3 8-principle study packet ({slug}-8-principle-study.md)
- [ ] 4 Content calendar 30 days ({slug}-content-calendar.md)
- [ ] 5 SDD choice logged ({slug}-sdd-routing.md)
- [ ] 6 Automation spec/tasks (when framework chosen)
- [ ] 7 Account checklist done ({slug}-account.md)
- [ ] 8 Scheduler spec (cron/queue at 03:00)
- [ ] 9 Publish log started ({slug}-publish-log.md)
- [ ] 10 Report template + first query ({slug}-report-queries.md)
```

---

## Phase 0 — Intake

Write `docs/domains/{slug}/intake.md`:

- Domain statement (1 paragraph)
- Platforms + account mode
- Schedule + timezone
- Media ladder (current vs later)
- Success metrics (views, saves, clicks, followers — pick 2–3)
- Framework: deferred | chosen

---

## Phase 1 — Research (`last30days`)

**Read and follow `last30days` SKILL.md completely.** Do not improvise generic web search.

1. Topic examples for Angkor/Khmer niche:
   - `Khmer kou sopeap recipe TikTok`
   - `Angkor Wat travel content 2026`
   - `Cambodian food social media`
2. Run engine + supplements; save raw to `{slug}-last30days-raw.md` under domain folder (copy or symlink from `~/Documents/Last30Days/` if engine saved there).
3. Extract **content angles** table into decomposition prep:

| Angle | Source signal | Post format |
|-------|---------------|-------------|
| … | Reddit/TikTok/YouTube cite | text / carousel / video |

Gate: raw file exists and at least 5 quotable snippets with URLs.

---

## Phase 2 — Domain decomposition

Adapt [domain-decomposition-template.md](reference/domain-decomposition-template.md). **Not a codebase doc** — decompose the **topic domain**:

- Pillars (e.g. *Dishes*, *Temple facts*, *Culture*, *Travel tips*)
- Entities (kou sopeap, Angkor Wat, Bayon, etc.)
- Audience jobs (learn, cook, visit, share)
- Content types per pillar
- **Status matrix**: Research done | Calendar drafted | Account ready | Scheduler live | Reporting live

Save: `{slug}-decomposition.md`

Gate: 3–5 pillars, status matrix filled, links to last30days raw file.

---

## Phase 3 — 8-principle study packet

**Read `8-principle-study` SKILL.md** and [packet-structure.md](reference/packet-structure-condensed.md).

- Learner level from intake (default: general audience)
- Format: Markdown in domain folder unless user asked docx/html
- Principle 1 map must match decomposition pillars (same names)
- Quizzes/flashcards only from packet content
- Add section **Content hooks** — 10 post ideas mapped to pillars

Save: `{slug}-8-principle-study.md`

Gate: all 8 principles present; cross-link decomposition ↔ study.

---

## Phase 4 — Content calendar

Use [content-calendar-template.md](reference/content-calendar-template.md).

- **30 days** minimum for MVP pipeline
- Default slot: **03:00** `{timezone}` — one post/day unless user overrides
- Columns: date, time, pillar, hook, format (text|image|carousel|video), caption draft, hashtags, status
- Week 1 = text-only if media ladder says so; mark later weeks for image/carousel/video

Save: `{slug}-content-calendar.md`

Gate: 30 rows; first 7 captions drafted.

---

## Phase 5 — SDD routing (`spec-kit-openspec-superpowers`)

Write `{slug}-sdd-routing.md`:

```
Automation app status: greenfield | existing | none (manual posting only)

SDD choice:
- Greenfield scheduler/API → spec-kit + superpowers (+ caveman optional)
- Post-MVP feature on existing repo → openspec + superpowers
- Manual only → skip SDD; user posts by hand using calendar

Framework: deferred | {chosen}

Next command when ready:
- spec-kit: /speckit.specify in {project}
- openspec: /opsx:new {change-name}
```

Do **not** run Spec-Kit and OpenSpec on the same feature.

---

## Phase 6 — Media production ladder

Produce in order; do not skip text.

| Step | Output | Tools (examples) |
|------|--------|------------------|
| 1 Text | Caption + hook + CTA in calendar | This skill |
| 2 Image | 1 hero image per post | GenerateImage, design skills |
| 3 Carousel | 3–7 slides from study packet lessons | imagegen-frontend-mobile / carousel copy |
| 4 Video | Script from caption + b-roll notes | video skills later |

Store assets under `docs/domains/{slug}/assets/{YYYY-MM-DD}/`.

Gate per run: today's text done before image; image before carousel.

---

## Phase 7 — Account setup

Write `{slug}-account.md`:

**Manual mode (default safe path)**

- [ ] Platform account created by user
- [ ] Business/creator mode enabled if needed
- [ ] Bio + link aligned to pillars
- [ ] Credentials in password manager (never commit secrets)
- [ ] Test post succeeded

**Assisted mode**

- Agent provides step-by-step + copy-paste bio/handle suggestions
- User completes verification; agent never stores passwords in repo

Gate: test post or explicit user sign-off.

---

## Phase 8 — Schedule (3:00 AM daily)

Document in `{slug}-scheduler-spec.md` (implementation waits for framework):

**Requirements**

- Cron expression: `0 3 * * *` in user's timezone
- Idempotent job: read calendar row for today → publish → log
- Failure: retry 2x, then alert row in publish log
- Manual fallback: export today's caption + asset paths if API not wired

**Laravel-shaped default** (when user picks Laravel): `Schedule::job(PublishDomainPost::class)->dailyAt('03:00')->timezone(...)` + queue worker.

Gate: spec written; if code exists, one dry-run logged.

---

## Phase 9 — Publish log

Append-only `{slug}-publish-log.md`:

| Date | Scheduled | Published | Platform | Post ID/URL | Format | Notes |

Update after each publish or dry-run.

---

## Phase 10 — Reporting

Create `{slug}-report-queries.md` with saved questions:

**Weekly**

- Views / saves / shares last 7 days vs prior week
- Top 3 posts by saves
- Pillar performance (which pillar won?)
- Failed or skipped slots

**Monthly**

- Follower delta, best format (text vs carousel vs video)
- Calendar adherence (% posted on schedule)

When automation exists, map queries to SQL/API; until then, manual export from platform analytics.

Gate: template exists; user knows where to paste numbers or run queries.

---

## Example walkthrough (Angkor + Khmer cuisine)

See [examples/angkor-khmer-cuisine.md](examples/angkor-khmer-cuisine.md).

Quick path:

1. `/last30days Khmer kou sopeap Angkor Wat TikTok`
2. Decompose pillars: *Sour soups*, *Angkor sites*, *Street food*, *Travel*
3. 8-principle packet with "Content hooks" for 10 posts
4. Calendar: Day 1 text "5 facts about kou sopeap" @ 03:00; Day 2 Angkor sunrise myth; …
5. SDD: deferred; manual TikTok until framework chosen
6. User creates account; agent fills calendar + captions
7. Report template for week 1

---

## Invocation

```text
Use domain-publish-pipeline: Angkor Wat + Khmer food, TikTok, 3am Phnom Penh, text first.
```

```text
/domain-publish-pipeline continue angkor-khmer-cuisine — add carousel for week 2
```

```text
Use domain-publish-pipeline + spec-kit: scaffold Laravel scheduler for {slug}
```

## Anti-patterns

- Skipping last30days and guessing trends
- Building Spec-Kit + OpenSpec on the same automation feature
- Scheduling without timezone confirmation
- Committing `.env`, tokens, or platform passwords
- Video before text captions exist
- Claiming scheduler live without publish log entry

## References

- [domain-decomposition-template.md](reference/domain-decomposition-template.md)
- [content-calendar-template.md](reference/content-calendar-template.md)
- [packet-structure-condensed.md](reference/packet-structure-condensed.md)
- [examples/angkor-khmer-cuisine.md](examples/angkor-khmer-cuisine.md)
