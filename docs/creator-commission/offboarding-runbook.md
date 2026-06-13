# Offboarding Runbook

**Trigger:** Termination notice from either party, pilot end without renewal, or cause termination per agreement.

---

## Checklist

| # | Step | Owner | Done |
|---|------|-------|------|
| 1 | **Receive / send termination notice** — email; save PDF or export to `{creator_handle}-legal/` | Operator | [ ] |
| 2 | Record **effective termination date** on Sheet tab **Onboarding** | Operator | [ ] |
| 3 | **Stop scheduling new posts** within **7 days** of effective date | Operator | [ ] |
| 4 | Cancel any queued drafts not yet published | Operator | [ ] |
| 5 | **Revoke manager access** — Creator removes Operator from YT + Meta within **48h** (document dates) | Creator | [ ] |
| 6 | Confirm **existing posts policy** per agreement: leave live / unlist / delete | Both | [ ] |
| 7 | **Export Publish log** — File → Download CSV; store in `{creator_handle}-archive/` | Operator | [ ] |
| 8 | Prepare **final monthly settlement** (partial month if needed) | Operator | [ ] |
| 9 | Attach payout evidence (redacted); label Confirmed vs Estimated | Operator | [ ] |
| 10 | Send **final invoice + statement** within **30 days** of last applicable payout cycle | Operator | [ ] |
| 11 | Archive Google Sheet as **read-only**; remove from active ops folder | Operator | [ ] |
| 12 | **Close-out email** — summary, final amounts, access confirmed revoked, thank you | Operator | [ ] |

---

## Existing content policy

Document what was agreed in the signed contract:

| Policy | Operator action |
|--------|-----------------|
| **Leave live** (default) | No changes to published YT/IG URLs |
| **Unlist** | Operator unlists YT / archives IG per Creator list within `[X]` days |
| **Delete** | Operator deletes listed URLs within `[X]` days; Creator confirms list in writing |

If Operator access is revoked before unlist/delete, Creator performs removal; Operator supplies URL list from exported Publish log.

---

## Final settlement

Include in last **Monthly settlement** row:

- Period start / end (partial month OK)
- All in-scope platform payouts through termination date
- Final S/T attribution for YouTube Shorts Operator published
- Outstanding monthly ops fee pro-rata if contract requires
- Commission at agreed rate
- **Creator net** and **amount due Operator**

Dispute window: **14 days** per agreement, then final.

---

## Archive package (operator retains)

```
{creator_handle}-archive/
├── publish-log-final.csv
├── monthly-settlement-final.csv
├── agreement-signed.pdf
└── termination-notice.pdf
```

Retention period: `[counsel to advise]` — suggest minimum 24 months for tax/disputes.

---

## Sign-off

| | Name | Date |
|---|------|------|
| Operator | | |
| Creator | | |
