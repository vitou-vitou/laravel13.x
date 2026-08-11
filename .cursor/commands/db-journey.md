---
name: /db-journey
id: db-journey
category: Workflow
description: Long Direct Book user journey — all in-scope products × quote/policy (view+PDF)
---

# Direct Book user journey (long task)

**Canonical invoke (shortest):** `db-journey`  
**Also accept:** `/db-journey` · `pl-journey` · `7pj` · `7-product-journey` · `7-product-user-journey`

Load triad skill `spec-kit-openspec-superpowers` + playbook:  
`.cursor/skills/spec-kit-openspec-superpowers/references/db-journey.md`  
(or `~/.claude/skills/spec-kit-openspec-superpowers/references/db-journey.md`)

## What to do

Run a **long, phased user-journey** across **all in-scope Direct Book products** and **quote → policy** (endorsement only if user adds `+endt`).

### Products (authoritative — 6)

Bond `0195` is **out**. Treat “7-product” nicknames as:

| Code | Product |
|------|---------|
| `0189` | Marine Cargo |
| `0191` | Burglary |
| `0192` | Money |
| `0193` | Plate Glass |
| `0194` | CAR |
| `0196` | PI |

### Phases (default)

1. Quotation — create / edit / detail **view** / print **PDF**
2. Policy — issue / edit / detail **view** / print **PDF**
3. Optional: Endorsement — only if user said `+endt` / `with endt`

### Method

1. Triad ON · AFK · Claude Senior for heavy slices.
2. For each product × phase: **path map** (form · view · PDF) → smoke / evidence → note gaps.
3. Layout ambiguity → brainstorm + visual demo **before** code (`brainstorm-think-visual.md`).
4. Fat/SRP when touching shared shells (`srp-thin.md`) — prefer BUR slice files.
5. Evidence under `docs/evidence/db-journey/`.
6. No commit unless user asks. No legacy `0121`–`0125` refactor.
7. Prefer few records (`13-smoke-few-records`).

### Narrowing

| Phrase | Effect |
|--------|--------|
| `db-journey marine` | One product |
| `db-journey quote-only` | Skip policy |
| `db-journey policy-only` | Policy focus |
| `db-journey +endt` | Include endorsement |
| `db-journey audit` | Path map + gaps only, no code |

Start immediately; keep a **product × phase × view/PDF** matrix as the running checklist.
