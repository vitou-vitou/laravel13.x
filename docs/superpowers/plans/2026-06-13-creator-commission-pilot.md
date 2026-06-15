# Creator Commission Pilot — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship **pilot-ready ops artifacts** for the TikTok-first Cross-Platform Ops Partner model — agreement outline, Sheet templates, onboarding/offboarding runbooks, weekly workflow checklist, and (Phase 2) optional internal TikTok metadata CLI.

**Architecture:** Process-first v1 lives under `docs/creator-commission/` as markdown + CSV templates importable into Google Sheets. No Laravel app for pilot. Optional Python CLI under `tools/tiktok-metadata/` supports authorized metadata pull only; it does not replace creator clean masters.

**Tech Stack:** Markdown, CSV (Google Sheets import), Python 3.11+ + yt-dlp (Phase 2 only), pytest (Phase 2)

**Spec:** `docs/superpowers/specs/2026-06-13-creator-commission-tiktok-first-design.md`

---

## File Map

```
docs/creator-commission/
├── README.md                          # Index + how to run pilot
├── agreement-outline.md               # Clause checklist (not legal advice)
├── onboarding-runbook.md              # Week 0 steps
├── offboarding-runbook.md             # Termination steps
├── weekly-batch-checklist.md          # Operator weekly loop
├── pitch-one-pager.md                 # Creator-facing summary
└── templates/
    ├── publish-log.csv                # Import → Sheet tab
    ├── weekly-metrics.csv             # Import → Sheet tab
    └── monthly-settlement.csv         # Import → Sheet tab + formulas note

tools/tiktok-metadata/                 # Phase 2 (optional)
├── README.md
├── requirements.txt
├── scrape_tiktok.py                   # CLI: --username --limit --since-date
└── tests/
    ├── fixtures/sample_video.json     # yt-dlp --dump-json fixture
    └── test_normalize_metadata.py
```

---

## Phase 1 — Pilot ops artifacts (no code)

### Task 1: Pilot folder README

**Files:**
- Create: `docs/creator-commission/README.md`

- [ ] **Step 1: Create README with index and pilot sequence**

```markdown
# Creator Commission — Pilot Kit

TikTok-first cross-platform distribution for individual creators (Segment A).

**Spec:** [../superpowers/specs/2026-06-13-creator-commission-tiktok-first-design.md](../superpowers/specs/2026-06-13-creator-commission-tiktok-first-design.md)

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
2. Run onboarding runbook
3. Import CSV templates into one Google Sheet per creator
4. Execute weekly batch checklist (weeks 1–4)
5. Deliver first monthly settlement tab + invoice
6. Decide: renew, adjust %, or stop

## Open operator decisions (set before day 1)

- Monthly ops fee `$X` in local currency
- Termination default for existing posts (leave live vs unlist)
- Music policy default (skip / replace / creator export)
- FB Reels included or deferred
```

- [ ] **Step 2: Commit**

```bash
git add docs/creator-commission/README.md
git commit -m "docs: add creator commission pilot kit index"
```

---

### Task 2: Creator pitch one-pager

**Files:**
- Create: `docs/creator-commission/pitch-one-pager.md`

- [ ] **Step 1: Write one-pager from approved spec**

Include sections: **What I do**, **What you keep**, **What you do**, **Pricing (pilot)**, **Platforms**, **Music/watermark note**, **Reporting**, **Next step** (15-min call + pilot agreement).

Use exact pilot terms from spec:
- Hybrid: `$X/month` + 15% months 1–3 (pilot may waive month 1)
- TikTok source → YT Shorts + IG Reels
- Creator-owned accounts, manager access only
- Approval queue 24–48h
- Commission on destination-platform revenue only (not main TikTok)

- [ ] **Step 2: Commit**

```bash
git add docs/creator-commission/pitch-one-pager.md
git commit -m "docs: add creator commission pitch one-pager"
```

---

### Task 3: Agreement outline (clause checklist)

**Files:**
- Create: `docs/creator-commission/agreement-outline.md`

- [ ] **Step 1: Write clause checklist**

Required sections (each as H2 with bullet clauses to fill with counsel):

1. **Parties & term**
2. **Services** (cross-post, metadata, scheduling, reporting; explicit out-of-scope list from spec)
3. **Creator warranties** (ownership, rights, no infringing third-party content)
4. **License grant** (non-exclusive, listed platforms, term + sunset)
5. **Account access** (manager roles only; no password sharing)
6. **Asset delivery** (originals-first; authorized metadata pull)
7. **Music / Content ID policy** (three onboarding choices)
8. **Revenue definition** (in-scope vs out-of-scope buckets from spec)
9. **Attribution formula** (S/T AdSense allocation + Confirmed vs Estimated labels)
10. **Fees & payment** (monthly ops + commission %, cadence, min payout, disputes 14-day)
11. **Approval SLA** (24–48h; auto-skip if no response — define behavior)
12. **Reporting obligations** (weekly snapshot, monthly statement)
13. **Termination** (stop new posts 7d; existing posts policy; access revoke 48h; final statement 30d)
14. **Liability cap & indemnity** (placeholder amounts for counsel)
15. **Governing law** (placeholder for local jurisdiction)

Top of file: **This is not legal advice. Have qualified local counsel review before signing.**

- [ ] **Step 2: Commit**

```bash
git add docs/creator-commission/agreement-outline.md
git commit -m "docs: add distribution agreement clause outline"
```

---

### Task 4: Onboarding runbook

**Files:**
- Create: `docs/creator-commission/onboarding-runbook.md`

- [ ] **Step 1: Write Week 0 checklist**

Copy spec onboarding items into ordered steps with owner column (Operator | Creator):

1. Kickoff call — confirm TikTok @handle, destinations, tier (Lite/Standard)
2. Agreement signed
3. Music policy choice recorded in Sheet `Publish log` notes tab or onboarding row
4. Creator grants YT manager + Meta Business Suite access (document account emails)
5. Create Google Sheet from templates; name `{creator_handle}-ops-2026`
6. Define first batch scope (e.g. last 14 public videos vs new-only)
7. Creator sets up shared Drive folder OR confirms watermark-free export process
8. Schedule first weekly batch day
9. Send pitch recap email with links to Sheet (view/comment) and approval expectations

Include **Do not** list: password sharing, publishing before approval (Lite tier), watermarked TikTok rips as YT master.

- [ ] **Step 2: Commit**

```bash
git add docs/creator-commission/onboarding-runbook.md
git commit -m "docs: add creator commission onboarding runbook"
```

---

### Task 5: Weekly batch checklist

**Files:**
- Create: `docs/creator-commission/weekly-batch-checklist.md`

- [ ] **Step 1: Write operator checklist mapped to spec loop**

Steps 1–7 from spec as checkboxes:

- [ ] BUILD LIST — new TikTok URLs since `last_run_date` (manual or Phase 2 CLI)
- [ ] ELIGIBILITY — public, music policy, on-brand, not duet-only
- [ ] ASSET — creator file received; if not, request before YT publish
- [ ] PACKAGING — YT title/description/tags; IG caption/hashtags/cover
- [ ] APPROVAL — rows added to Sheet with status `pending_approval`; message creator
- [ ] PUBLISH — after approval; log `yt_url`, `ig_url`, video IDs, `posted_time`
- [ ] REPORT — update weekly metrics tab; send ≤5 bullet email

Add **time box**: target 15–25 min/video clean; escalate Content ID to creator same day.

- [ ] **Step 2: Commit**

```bash
git add docs/creator-commission/weekly-batch-checklist.md
git commit -m "docs: add weekly cross-post batch checklist"
```

---

### Task 6: Offboarding runbook

**Files:**
- Create: `docs/creator-commission/offboarding-runbook.md`

- [ ] **Step 1: Write termination checklist**

1. Receive termination notice (email; save PDF/export)
2. Stop scheduling new posts within 7 days
3. Revoke manager access within 48h (document date)
4. Export final publish log CSV from Sheet
5. Prepare final monthly settlement (even if partial month)
6. Send final invoice + statement within 30 days of last payout cycle
7. Confirm existing posts policy (leave live / unlist) per agreement
8. Archive Sheet as read-only; remove from active ops folder

- [ ] **Step 2: Commit**

```bash
git add docs/creator-commission/offboarding-runbook.md
git commit -m "docs: add creator commission offboarding runbook"
```

---

### Task 7: Google Sheet CSV templates

**Files:**
- Create: `docs/creator-commission/templates/publish-log.csv`
- Create: `docs/creator-commission/templates/weekly-metrics.csv`
- Create: `docs/creator-commission/templates/monthly-settlement.csv`

- [ ] **Step 1: Create publish-log.csv**

Header row (exact):

```csv
date,tiktok_url,yt_url,ig_url,yt_video_id,title_variant,posted_time,status,views_yt_7d,views_ig_7d,notes
```

Add one example row (fake URLs) as row 2 for import testing.

`status` enum documented in README: `pending_approval`, `approved`, `published`, `skipped_music`, `skipped_creator`, `error`

- [ ] **Step 2: Create weekly-metrics.csv**

```csv
week_start,videos_published,best_video_url,best_video_views,experiment,experiment_result,operator_notes
```

- [ ] **Step 3: Create monthly-settlement.csv**

```csv
period_start,period_end,platform,gross_payout_local,currency,payout_status,s_views,t_views,attributed_revenue,commission_rate_pct,monthly_ops_fee,commission_amount,creator_net,notes
```

Add comment block at top of README (not CSV): Google Sheets formulas for operator:

```
attributed_revenue (YT) = gross_payout_local * (s_views / t_views)
commission_amount = attributed_revenue * (commission_rate_pct / 100)
creator_net = sum(attributed_revenue by platform) - monthly_ops_fee - commission_amount
```

- [ ] **Step 4: Commit**

```bash
git add docs/creator-commission/templates/
git commit -m "docs: add Sheet CSV templates for creator commission pilot"
```

---

### Task 8: Link spec to pilot kit

**Files:**
- Modify: `docs/superpowers/specs/2026-06-13-creator-commission-tiktok-first-design.md` (Next Step section only)

- [ ] **Step 1: Update Next Step section**

Replace "when created" with link:

```markdown
## Next Step

**Implementation plan:** [../plans/2026-06-13-creator-commission-pilot.md](../plans/2026-06-13-creator-commission-pilot.md)

**Pilot kit:** [../../creator-commission/README.md](../../creator-commission/README.md)
```

- [ ] **Step 2: Commit**

```bash
git add docs/superpowers/specs/2026-06-13-creator-commission-tiktok-first-design.md
git commit -m "docs: link creator commission spec to pilot plan and kit"
```

---

## Phase 2 — Optional TikTok metadata CLI (internal ops)

Skip Phase 2 until Phase 1 is committed and operator has a signed pilot creator.

### Task 9: Scaffold tools/tiktok-metadata

**Files:**
- Create: `tools/tiktok-metadata/requirements.txt`
- Create: `tools/tiktok-metadata/README.md`

- [ ] **Step 1: requirements.txt**

```
yt-dlp>=2024.1.0
```

- [ ] **Step 2: README with legal/ToS note**

State: authorized use only with creator consent; public content; personal research/ops; respect rate limits; not for bypassing login unless `--cookies` provided by creator.

Document output layout matching earlier scraper spec:

```
./downloads/{username}/YYYY-MM-DD/{video_id}.mp4
./downloads/{username}/metadata.jsonl
```

Clarify: **metadata + backup for publish log**, not replacement for clean YT masters.

- [ ] **Step 3: Commit**

```bash
git add tools/tiktok-metadata/requirements.txt tools/tiktok-metadata/README.md
git commit -m "chore: scaffold tiktok metadata tool for creator ops"
```

---

### Task 10: Metadata normalization tests (TDD)

**Files:**
- Create: `tools/tiktok-metadata/tests/fixtures/sample_video.json`
- Create: `tools/tiktok-metadata/tests/test_normalize_metadata.py`
- Create: `tools/tiktok-metadata/scrape_tiktok.py` (minimal normalize function first)

- [ ] **Step 1: Add fixture** (minimal yt-dlp dump-json shape)

```json
{
  "id": "7123456789012345678",
  "title": "Test caption #khmer #food",
  "description": "Test caption #khmer #food",
  "view_count": 12000,
  "like_count": 800,
  "repost_count": 40,
  "upload_date": "20260601",
  "webpage_url": "https://www.tiktok.com/@example/video/7123456789012345678",
  "track": "original sound - example",
  "artist": "example"
}
```

- [ ] **Step 2: Write failing test**

```python
# tools/tiktok-metadata/tests/test_normalize_metadata.py
from scrape_tiktok import normalize_metadata

def test_normalize_metadata_maps_core_fields():
    raw = {
        "id": "7123456789012345678",
        "description": "Test caption",
        "view_count": 12000,
        "like_count": 800,
        "repost_count": 40,
        "upload_date": "20260601",
        "webpage_url": "https://www.tiktok.com/@example/video/7123456789012345678",
        "track": "original sound - example",
    }
    out = normalize_metadata(raw)
    assert out["video_id"] == "7123456789012345678"
    assert out["caption"] == "Test caption"
    assert out["views"] == 12000
    assert out["likes"] == 800
    assert out["shares"] == 40
    assert out["posted_date"] == "2026-06-01"
    assert out["video_url"] == raw["webpage_url"]
    assert out["music_title"] == "original sound - example"
```

- [ ] **Step 3: Run test — expect FAIL**

```bash
cd tools/tiktok-metadata
pip install -r requirements.txt pytest
pytest tests/test_normalize_metadata.py -v
```

Expected: `ModuleNotFoundError` or `ImportError`

- [ ] **Step 4: Implement normalize_metadata**

```python
# scrape_tiktok.py (excerpt)
from datetime import datetime

def normalize_metadata(raw: dict) -> dict:
    upload = raw.get("upload_date") or ""
    posted = ""
    if len(upload) == 8:
        posted = f"{upload[0:4]}-{upload[4:6]}-{upload[6:8]}"
    return {
        "video_id": raw.get("id"),
        "caption": raw.get("description") or raw.get("title") or "",
        "views": raw.get("view_count"),
        "likes": raw.get("like_count"),
        "shares": raw.get("repost_count"),
        "posted_date": posted,
        "video_url": raw.get("webpage_url"),
        "music_title": raw.get("track") or raw.get("artist"),
    }
```

- [ ] **Step 5: Run test — expect PASS**

```bash
pytest tests/test_normalize_metadata.py -v
```

- [ ] **Step 6: Commit**

```bash
git add tools/tiktok-metadata/
git commit -m "feat: add tiktok metadata normalizer with tests"
```

---

### Task 11: CLI list + jsonl append (minimal)

**Files:**
- Modify: `tools/tiktok-metadata/scrape_tiktok.py`

- [ ] **Step 1: Add argparse CLI**

Arguments: `--username` (required), `--limit` (int, default 0 = no limit), `--since-date` (`YYYY-MM-DD`), `--metadata-only` (flag: no mp4 download)

Behavior:
- Flat-playlist `yt-dlp --dump-json` on `https://www.tiktok.com/@{username}`
- Filter by `since-date` on normalized `posted_date`
- Apply `--limit` after filter
- Append one json line per video to `./downloads/{username}/metadata.jsonl`
- Log progress `N/total`
- Skip download if mp4 already exists (when not `--metadata-only`)

- [ ] **Step 2: Manual smoke test** (public handle, `--limit 2 --metadata-only`)

```bash
python scrape_tiktok.py --username SOME_PUBLIC_HANDLE --limit 2 --metadata-only
```

Expected: jsonl file with ≤2 lines; no crash on private entries (log skip)

- [ ] **Step 3: Commit**

```bash
git add tools/tiktok-metadata/scrape_tiktok.py
git commit -m "feat: add tiktok metadata CLI for creator ops"
```

---

## Spec Coverage Self-Review

| Spec section | Plan task |
|--------------|-----------|
| Service tiers / pricing | Task 2 pitch, Task 3 agreement |
| Revenue / commission scope | Task 3 clauses 8–10, Task 7 settlement CSV |
| Attribution S/T formula | Task 3 clause 9, Task 7 README formulas |
| Rights / music / termination | Task 3, Task 4, Task 6 |
| Weekly workflow | Task 5, Task 7 publish log |
| Reporting | Task 7 weekly + monthly templates |
| Pilot 30-day playbook | Task 1 README sequence |
| Optional internal tooling | Phase 2 Tasks 9–11 |
| Legal note | Task 3 header, Task 9 README |

No TBD placeholders in task steps. Phase 2 skipped until Phase 1 complete (explicit gate).

---

## Verification (Phase 1 done when)

- [ ] All files in File Map exist under `docs/creator-commission/`
- [ ] Operator can import CSVs into Google Sheets without column edits
- [ ] Onboarding → weekly → offboarding runbooks are runnable without opening spec
- [ ] Spec links to plan and pilot kit

## Verification (Phase 2 done when)

- [ ] `pytest tools/tiktok-metadata/tests/` passes
- [ ] CLI `--metadata-only --limit 2` produces valid jsonl on a public handle
- [ ] README states authorized-use + not a substitute for clean YT masters

---

## Execution Handoff

**Plan saved to:** `docs/superpowers/plans/2026-06-13-creator-commission-pilot.md`

**Two execution options:**

1. **Subagent-Driven (recommended)** — fresh subagent per task, review between tasks  
2. **Inline Execution** — execute tasks in this session with checkpoints  

**Recommended order:** Complete **Phase 1** before Phase 2. Push to git after Phase 1 or after each task batch.
