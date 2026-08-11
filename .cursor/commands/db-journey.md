---
name: /db-journey
id: db-journey
category: Workflow
description: Long Direct Book user journey — 7 products × quote/policy/endt (view+PDF)
---

# Direct Book user journey (long task)

**Canonical invoke (shortest):** `db-journey`  
**Also accept:** `/db-journey` · `pl-journey` · `7pj` · `7-product-journey` · `7-product-user-journey`

Load triad skill `spec-kit-openspec-superpowers` + playbook:  
`.cursor/skills/spec-kit-openspec-superpowers/references/db-journey.md`  
(or `~/.claude/skills/spec-kit-openspec-superpowers/references/db-journey.md`)

## What to do

Run a **long, phased user-journey** across **all 7 Direct Book products** and **quote → policy → endorsement** (L1–L3 all in scope by default).

### Products (authoritative — 7)

| Code | Product |
|------|---------|
| `0189` | Marine Cargo |
| `0191` | Burglary |
| `0192` | Money |
| `0193` | Plate Glass |
| `0194` | CAR |
| `0195` | Bond |
| `0196` | PI |

### Phases (default — all in scope)

1. **L1 Quotation** — create / edit / detail **view** / print **PDF**
2. **L2 Policy** — issue / edit / detail **view** / print **PDF**
3. **L3 Endorsement** — create / edit / detail **view** / print **PDF**

**63 cells** default (7 × 3 × 3).

### Method

1. Triad ON · AFK · Claude Senior for heavy slices.
2. For each product × phase: **path map** (form · view · PDF) → smoke / evidence → note gaps.
3. Keep checklist **grouped by lifecycle** (L1 → L2 → L3), then product.
4. Layout ambiguity → brainstorm + visual demo **before** code (`brainstorm-think-visual.md`).
5. Fat/SRP when touching shared shells (`srp-thin.md`) — prefer BUR slice files.
6. Evidence under `docs/evidence/db-journey/`.
7. No commit unless user asks. No legacy `0121`–`0125` refactor.
8. Prefer few records (`13-smoke-few-records`).

### Narrowing

| Phrase | Effect |
|--------|--------|
| `db-journey marine` | One product |
| `db-journey quote-only` | L1 only |
| `db-journey policy-only` | L2 only |
| `db-journey endt-only` | L3 only |
| `db-journey no-endt` | Skip L3 |
| `db-journey audit` | Path map + gaps only, no code |

Start immediately; keep a **product × phase × view/PDF** matrix as the running checklist.
