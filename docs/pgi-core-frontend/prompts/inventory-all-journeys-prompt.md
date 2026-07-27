# Prompt — Inventory all approval journeys

Use when you need a **coverage map** (no missed Product×Module) before generating journey PDFs.

---

## Short (paste this)

```text
inventory all journeys

AFK. No confirm. In pgi-core-frontend, inventory EVERY Product×Module approval lifecycle.

Products: Auto, HS, PA, Travel, PL (note Direct Book 0189/0191–0196 vs legacy).
Modules: Quotation, Policy, Endorsement, Claim Register, Claim Payment(s), Renewal if exists.

Output ONE markdown table only first:
# | Product | Module | List URL | Apv | Acc | Sub | Rev | Del | Gen | Primary table | Doc status
Doc status = DONE | PARTIAL | TODO | N/A

Grep routers + Controllers approve/accept/revise + Detail canApprove*. No invented rows.
Group into pattern families (Quote 2-step / Policy+Submit / Endorsement / Claim / Renewal).
Recommend generate waves W1…Wn.
Save docs/diagrams/approval-lifecycle-inventory.md → auto-sync+push laravel13.x docs/pgi-core-frontend/diagrams/.
Caveman. Next 5. No pgi commit. Do NOT generate full journey PDFs unless I say generate W# or generate {slug}.
```

---

## Ultra-short

```text
inventory all journeys — AFK no confirm. Full Product×Module approval matrix (Apv/Acc/Sub/Rev/Del/Gen + table + DONE/PARTIAL/TODO). Save + hub sync. Waves only — no PDF packs until I say generate.
```

---

## Follow-ups

| Say | Effect |
|-----|--------|
| `generate W1` | Build packs for wave 1 from inventory |
| `generate hs-endorsement-journey` | One slug pack + PDF + hub sync |
| `don't sync` / `pgi only` | Skip laravel13.x |
| Full journey prompt | See `journey-reject-flow-prompt.md` |

## Style refs

- Inventory: `diagrams/approval-lifecycle-inventory.md`
- Example DONE pack: `diagrams/auto-endorsement-journey.*`
- Reject tables: `diagrams/auto-hs-reject-flow.md`
