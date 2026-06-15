# Weekly Batch Checklist — Operator

**Cadence:** Once per week (or per agreed batch day)  
**Time target:** 15–25 min/video (clean file, no Content ID); escalate if 40+ min

**Sheet:** `{creator_handle}-ops-2026` → tab **Publish log**

---

## Pre-batch

- [ ] Note `last_run_date` from previous batch (Onboarding tab or last publish log row)
- [ ] Confirm Creator shared any new clean files since last run
- [ ] Open approval queue from prior week — publish any rows still `approved`

---

## 1. BUILD LIST

- [ ] List new TikTok URLs since `last_run_date` (manual review of profile or internal metadata tool)
- [ ] Add candidate URLs to draft section of Sheet or scratch pad
- [ ] Count total candidates for this batch → `N`

---

## 2. ELIGIBILITY

For each candidate:

- [ ] Public video (not private / friends-only)
- [ ] Passes **music policy** (Schedule B / Onboarding tab)
- [ ] On-brand (not accidental duet/stitch-only unless Creator wants it)
- [ ] Creator has not marked **skip**
- [ ] Not already in **Publish log** with status `published`

If fail → set status `skipped_music` or `skipped_creator` with note; do not package.

---

## 3. ASSET

- [ ] **Creator clean file received** for YouTube master (preferred)
- [ ] If missing → request file **before** YouTube publish; IG may use creator file or approved source
- [ ] Do **not** use watermarked TikTok download as YT master unless Creator approved in writing

---

## 4. PACKAGING

Per eligible video:

**YouTube Shorts**

- [ ] Title: search intent + hook (not raw TikTok caption)
- [ ] Description: 1–2 sentences + keywords
- [ ] Tags: 5–10 relevant
- [ ] Thumbnail frame selected if applicable
- [ ] Proposed `posted_time` (test window documented in notes)

**Instagram Reels**

- [ ] Caption: hook in line 1; shorter than YT
- [ ] Hashtags: 3–5 niche
- [ ] Cover frame for grid

- [ ] Add row to **Publish log** with status `pending_approval`
- [ ] Fill: `date`, `tiktok_url`, `title_variant`, draft metadata in `notes` or linked doc

---

## 5. APPROVAL

- [ ] Message Creator: batch ready, link to Sheet rows, deadline **24–48h**
- [ ] Lite tier: wait for explicit **approved** before publish
- [ ] Standard tier (after week 2): spot-check unless row flagged
- [ ] If no response by deadline → apply agreement rule: `[hold / auto-skip]` per contract
- [ ] Update status: `approved` or `skipped_creator`

---

## 6. PUBLISH

For each `approved` row:

- [ ] Upload to YouTube Shorts → copy `yt_url`, `yt_video_id`
- [ ] Publish IG Reel → copy `ig_url`
- [ ] Set `posted_time` (actual)
- [ ] Set status `published`
- [ ] On failure → status `error` + note; notify Creator same day

---

## 7. REPORT

- [ ] Update **Weekly metrics** tab: `week_start`, `videos_published`, best performer URL/views
- [ ] Document any experiment (title/time) in `experiment` / `experiment_result`
- [ ] Send Creator email ≤ **5 bullets**:
  1. What published (links)
  2. Best performer + guess why
  3. Next week test
  4. Anything blocked (music/assets)
  5. Action needed (if any)

---

## Escalation

| Situation | Action |
|-----------|--------|
| Content ID claim on publish | Stop YT publish; notify Creator; status `error` or `skipped_music` |
| Creator ghosting approvals 2+ weeks | Email + apply contract auto-skip; pause new packaging |
| Scope creep request (edit TikTok, DMs) | Refer to out-of-scope list; offer separate quote |

---

## Batch complete

- [ ] Update `last_run_date` on **Onboarding** tab
- [ ] Log operator hours for pilot sustainability review

**Next:** repeat weekly until [offboarding-runbook.md](offboarding-runbook.md) or month-end → **Monthly settlement** tab.
