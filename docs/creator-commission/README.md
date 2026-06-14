# Creator Commission — Pilot Kit

TikTok-first cross-platform distribution for individual creators (Segment A).

**Spec:** [../superpowers/specs/2026-06-13-creator-commission-tiktok-first-design.md](../superpowers/specs/2026-06-13-creator-commission-tiktok-first-design.md)

**Plan:** [../superpowers/plans/2026-06-13-creator-commission-pilot.md](../superpowers/plans/2026-06-13-creator-commission-pilot.md)

## Contents

| File | Use |
|------|-----|
| [pitch-one-pager.md](pitch-one-pager.md) | Send to prospective pilot creator |
| [agreement-outline.md](agreement-outline.md) | Clause checklist before counsel review |
| [onboarding-runbook.md](onboarding-runbook.md) | Week 0 |
| [weekly-batch-checklist.md](weekly-batch-checklist.md) | Every batch |
| [offboarding-runbook.md](offboarding-runbook.md) | End of relationship |
| [templates/](templates/) | CSV → import to Google Sheets |

## 30-day pilot sequence

1. Send pitch → sign agreement (after local legal review)
2. Run [onboarding-runbook.md](onboarding-runbook.md)
3. Import CSV templates into one Google Sheet per creator
4. Execute [weekly-batch-checklist.md](weekly-batch-checklist.md) (weeks 1–4)
5. Deliver first monthly settlement tab + invoice
6. Decide: renew, adjust %, or stop

## Google Sheets setup

1. Create a new Google Sheet named `{creator_handle}-ops-2026`
2. Import each CSV from [templates/](templates/) as a separate tab:
   - `publish-log.csv` → tab **Publish log**
   - `weekly-metrics.csv` → tab **Weekly metrics**
   - `monthly-settlement.csv` → tab **Monthly settlement**
3. Add a tab **Onboarding** with music policy, account emails, and batch scope notes

### Settlement formulas (Monthly settlement tab)

After importing, add formulas in new columns or a summary row:

```
attributed_revenue (YouTube) = gross_payout_local * (s_views / t_views)
commission_amount = attributed_revenue * (commission_rate_pct / 100)
creator_net = SUM(attributed_revenue) - monthly_ops_fee - SUM(commission_amount)
```

Label each revenue row **Confirmed** (payout received) or **Estimated** (dashboard only).

### Publish log status values

| Status | Meaning |
|--------|---------|
| `pending_approval` | Packaged; waiting on creator |
| `approved` | Creator approved; ready to publish |
| `published` | Live on destination platform(s) |
| `skipped_music` | Blocked by music / Content ID policy |
| `skipped_creator` | Creator rejected or marked skip |
| `error` | Publish failed; see notes |

## Optional tooling

- [TikTok metadata CLI](../../tools/tiktok-metadata/README.md) — authorized BUILD LIST helper (`--metadata-only`)
- **[Creator Operator portal](../../examples/creator-operator-v1/docs/NEXT_SESSION.md)** — web UI ([UX map](../../examples/creator-operator-v1/docs/DESIGN.md), [slice roadmap + Mode D parallel](../../examples/creator-operator-v1/docs/ROADMAP.md)) for publish log + creator approvals (`http://creator-operator-v1.test`)

## Open operator decisions (set before day 1)

- Monthly ops fee `$X` in local currency
- Termination default for existing posts (leave live vs unlist)
- Music policy default (skip / replace / creator export)
- Facebook Reels included or deferred
