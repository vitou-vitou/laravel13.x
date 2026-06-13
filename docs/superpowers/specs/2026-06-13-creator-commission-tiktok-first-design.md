# Creator Commission Model — TikTok-First Cross-Platform Distribution

**Date:** 2026-06-13  
**Status:** Approved  
**Segment:** Individual creators (personal brand, ~10K–500K followers)  
**Source platform:** TikTok → YouTube Shorts + Instagram Reels (Facebook Reels optional v2)

---

## Overview

A **Cross-Platform Ops Partner** service for busy individual creators. The operator does not create original content. They repackage, optimize metadata, schedule, publish, monitor, and report on cross-platform distribution of the creator’s existing TikTok videos.

Creators retain copyright and account ownership. The operator earns a **hybrid fee**: modest monthly ops minimum plus **15–20% commission** on attributable net revenue from destination platforms only.

This spec covers business model, legal/rights framework, workflow, reporting, attribution, pilot playbook, and optional internal tooling boundaries. It does not mandate a specific software stack for v1.

---

## Problem & Value Proposition

**Creator problem:** Strong on TikTok, no time to republish elsewhere; leaves YouTube Shorts / Reels reach and revenue on the table.

**Operator value:**

1. **Reach** — same asset on additional surfaces
2. **Packaging** — platform-native titles, descriptions, tags, timing
3. **Operations** — weekly batch publish loop with minimal creator friction
4. **Transparency** — reporting and settlement creators can trust

**One-line pitch:**

> You keep creating on TikTok; I turn your backlog into YouTube Shorts and Reels with better titles and timing — you approve everything, keep your accounts, and pay a small ops fee plus 15–20% only on what those cross-posts earn.

---

## Target Client (Segment A)

| Attribute | Definition |
|-----------|------------|
| Profile | Individual creator, personal brand |
| Followers | ~10K–500K (pilot-friendly, real workload) |
| Primary platform | TikTok (source of truth for creative) |
| Motivation | Too busy to manage cross-platform; wants incremental audience/income |
| Trust sensitivity | High — personal brand, manager access only, approval queue |

**Not in scope for v1:** media companies, batch catalog licensing, aggregator channels owned by operator (Model C).

---

## Service Tiers (Pilot Defaults)

| | **Lite** | **Standard** (default) |
|---|----------|---------------------------|
| Destinations | 2 (YT Shorts + IG Reels) | 3 (+ FB Reels or second dest) |
| Volume | ~15 reposts/month | ~30 reposts/month |
| Fee | Local **$X/month** + **15%** net attributable | **$X/month** + **20%** net attributable |
| Approval | Creator approves all metadata pre-publish | Spot-check after first 2 weeks |
| Reporting | Biweekly email + monthly statement | Weekly snapshot + monthly settlement |
| Term | Month-to-month | 3-month initial, then monthly |

**$X** = covers 2–4 hours/month baseline ops in local market (operator sets per region).

**Pilot concession:** waive or reduce $X month 1 for case study + testimonial.

---

## Revenue Model

### Model type

**Hybrid (Model B):** monthly ops minimum + performance commission on in-scope platform payouts.

Pure rev-share alone is risky for operator at modest Shorts RPM; pure retainer under-aligns on growth.

### Commission base — IN scope

- YouTube AdSense attributable to **Shorts uploaded by operator** (listed video IDs)
- Instagram / Facebook **program bonuses** tied to Reels the operator published, when platform reporting allows

### Commission base — OUT of scope (default)

- TikTok Creator Rewards / Pulse on creator’s main TikTok (unless separate agreement to manage TikTok)
- Brand deals, sponsorships, merch, affiliate — unless separate addendum and tracked links managed by operator
- Tips, Super Thanks, gifts — case-by-case; default exclude

### Net revenue

**Net** = platform payout after platform fees, not gross views or estimated RPM alone.

### Payment terms

| Term | Value |
|------|--------|
| Cadence | Monthly |
| Timing | 15–30 days after platform payout (mirror AdSense delay) |
| Currency | Creator local currency at invoice-date mid-market rate (document source) |
| Min payout to operator | e.g. commission ≥ local ~$25 equivalent |
| Disputes | 14-day window after statement, then final |
| Pilot ramp | 15% months 1–3 → 20% on renewal if retained |

---

## Attribution Formula (Creator-Owned YouTube)

When YouTube only provides **channel-level** AdSense (common):

```
attributed_revenue = channel_adsense_payout × (S / T)

S = views in period on Shorts video IDs operator published
T = total Shorts views on channel in same period
```

Every monthly statement must show **S, T, payout, formula, result**. Label rows:

- **Confirmed** — payout received
- **Estimated** — dashboard-only until settlement

Same principle for IG/Meta when only aggregate insights exist: document allocation method in contract.

---

## Ownership & Rights

### Copyright

Creator **retains copyright**. Operator receives **limited license** only.

### License grant (operator)

- Non-exclusive (unless premium tier negotiated otherwise)
- Worldwide
- Purpose: reformat, optimize metadata, publish, promote on **listed platforms**
- Term: contract duration + sunset on termination

### Creator warranties

- Owns or controls TikTok content submitted
- Has rights to cross-publish (or accepts skip/re-edit for music conflicts)
- Will not submit third-party content without rights

### Channel ownership

| Asset | Owner |
|-------|--------|
| TikTok @handle | Creator |
| YouTube channel | Creator |
| Instagram account | Creator |
| Publish log / internal ops data | Operator |

**Access:** platform **manager/collaborator** roles only — no password sharing.

### Music & Content ID

TikTok library sounds often **do not** clear YouTube Content ID.

**Onboarding choice:**

1. Skip video for YT
2. Replace/mute audio (operator edit)
3. Creator provides clean export without blocked sound

Document choice in agreement. Operator may edit for compliance with notification.

### Termination

| Item | Default policy |
|------|----------------|
| New publishes | Stop within 7 days of notice |
| Existing posts | **Leave live** (negotiable: unlist or delete) |
| Access | Revoke manager roles within 48h |
| Final statement | Within 30 days of last payout cycle |

**Out of scope** (unless separate fee): original filming, heavy editing, comment/DM management, brand deal negotiation.

---

## Asset Workflow (TikTok-First)

### Watermark policy

**Originals-first (default):** creator provides clean files (Drive/Telegram/weekly folder) or exports without watermark where TikTok allows.

**Authorized pull:** operator may pull from TikTok for **metadata and backup only** — not primary master for YouTube if watermarked.

### Weekly batch loop

```
1. BUILD LIST     — new TikTok posts since last run (manual or authorized metadata)
2. ELIGIBILITY    — public; music policy; on-brand; not duet/stitch-only
3. ASSET          — prefer creator file; else authorized download
4. PACKAGING      — per-platform title, description, tags, cover, schedule
5. APPROVAL       — queue (Sheet/Notion); creator 24–48h
6. PUBLISH + LOG  — URLs, IDs, datetime, variant notes
7. REPORT         — weekly light; monthly settlement
```

### Eligibility gates

- Public video
- Passes music policy for destination
- Fits brand guidelines (no accidental off-brand duets)
- Creator not marked “skip” for that video

### Time budget (operator planning)

| Case | Minutes/video |
|------|----------------|
| Clean file, no Content ID | 15–25 |
| Re-edit / audio swap / ID dispute | 40+ |

---

## Platform Packaging Guidelines

### YouTube Shorts

- **Title:** search intent + hook (not raw TikTok caption)
- **Description:** 1–2 sentences + keywords; optional link back to TikTok
- **Tags:** 5–10 relevant
- **Thumbnail:** intentional frame where useful
- **Timing:** test two windows (local evening + optional second timezone)

### Instagram Reels

- **Caption:** hook in first line; shorter than YT
- **Hashtags:** 3–5 niche, rotate weekly
- **Cover:** grid-aware frame selection

### Experiments log

Track title variant, post time, 7-day views per video (spreadsheet sufficient for v1).

---

## Reporting & Transparency

### Weekly snapshot (≤5 bullets)

- Videos published + links per platform
- Best performer + hypothesized reason
- Next week test (e.g. post time shift)

### Monthly settlement document

| Column | Content |
|--------|---------|
| Video / platform | Links and IDs |
| Views (period) | Per platform |
| Revenue status | Confirmed / Estimated |
| Attributed revenue | After formula |
| Commission rate | 15% or 20% |
| Operator fee | $X monthly + commission |
| Creator net | Payout minus operator total |

Attach redacted platform payout screenshot or CSV snippet per platform.

### v1 tooling

**Google Sheet** with tabs:

- `Publish log`
- `Weekly metrics`
- `Monthly settlement`

No custom app required for first pilot.

**Publish log columns:**

`date | tiktok_url | yt_url | ig_url | title_variant | posted_time | status | notes`

---

## Onboarding Checklist

- [ ] Distribution agreement signed
- [ ] Commission tiers and attribution formula agreed
- [ ] Music policy selected (skip / replace / creator clean export)
- [ ] TikTok @handle documented
- [ ] Destination platforms listed
- [ ] Manager access granted (YT, Meta) — no passwords
- [ ] Approval SLA (24–48h) and auto-skip rule if ghosting
- [ ] Termination / existing content policy agreed
- [ ] First batch scope defined (backlog depth: e.g. last 30 days vs new only)

---

## Pilot Playbook (30 Days, 1 Creator)

| Week | Goal |
|------|------|
| 1 | Onboard; publish 3–5 Shorts (conservative); 100% approved |
| 2–3 | 10–15 total live; begin title/time experiments |
| 4 | First monthly statement (estimated revenue acceptable) |

**Success criteria:** creator renews; operator hours/video sustainable — not viral dependency.

**Kill criteria:** repeated Content ID blocks with no creator cooperation; approval ghosting; attribution disputes without resolution path.

---

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| Watermarked YT uploads | Originals-first workflow |
| Content ID / music strikes | Eligibility + audio policy |
| Low Shorts RPM expectations | Set expectations in onboarding |
| Attribution disputes | Formula in contract + transparent S/T on statements |
| Scope creep | Written out-of-scope list |
| Platform ToS changes | Periodic review; creator warrant on rights |
| Duplicate content demotion | Platform-native packaging; not identical spam |

---

## Optional Internal Tooling (Boundary)

**TikTok metadata/archive script** (e.g. yt-dlp-based CLI) may support:

- Enumerate new posts since last run
- Capture caption, views, likes, post date for publish log
- Resume-safe download for **authorized backup**

**Must not:**

- Replace creator clean masters for YouTube publish
- Run without creator authorization in agreement
- Become the customer-facing product for v1

Tooling is **ops efficiency**, not the business model.

---

## Legal Note

This spec is operational design, not legal advice. Operator should have a local **distribution / revenue-share agreement** reviewed by qualified counsel covering: copyright license, revenue definition, attribution, termination, liability cap, indemnity for creator-uploaded unlicensed material.

---

## Open Decisions (Operator Sets Before Pilot)

1. **Local $X** monthly ops fee amount
2. **Exact termination policy** for existing posts (leave live vs unlist)
3. **First pilot creator** selection
4. **Whether FB Reels** included in v1 or deferred

---

## Approval

| Role | Status | Date |
|------|--------|------|
| Product / operator | Approved | 2026-06-13 |

---

## Next Step

**Implementation plan:** [../plans/2026-06-13-creator-commission-pilot.md](../plans/2026-06-13-creator-commission-pilot.md)

**Pilot kit (Phase 1):** [../../creator-commission/README.md](../../creator-commission/README.md)

Phase 2 optional: internal TikTok metadata CLI — see plan Phase 2.
