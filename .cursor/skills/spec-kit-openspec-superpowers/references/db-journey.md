# Direct Book journey (`db-journey`)

**Short names (any of these starts the long task):**

| Say | Same meaning |
|-----|----------------|
| **`db-journey`** | Canonical (shortest clear) |
| `/db-journey` | Cursor command |
| `pl-journey` | Alias |
| `7pj` | Ultra-short |
| `7-product-journey` / `7-product-user-journey` | Same — **7** products × L1–L3 |

## Scope

**In scope (7):** `0189` Marine · `0191` Burglary · `0192` Money · `0193` Plate · `0194` CAR · `0195` Bond · `0196` PI  

**Out:** legacy `0121`–`0125` only

## Default journey matrix (all phases in scope)

For **each** of the 7 products, cover **L1–L3**:

| Lifecycle | Surfaces |
|-----------|----------|
| **L1 Quote** | Form/edit · Detail **view** · Print **PDF** |
| **L2 Policy** | Issue/edit · Detail **view** · Print **PDF** |
| **L3 Endorsement** | Create/edit · Detail **view** · Print **PDF** |

**Cells:** 7 × 3 phases × 3 surfaces = **63** (default).

Path map every cell: shared shell? BUR slice? PDF partial?

### Checklist shape (agent must keep ticking)

Group by lifecycle, then product — same as chat matrix:

- L1 Quotation — 7 products × Form / View / PDF  
- L2 Policy — 7 products × Issue-edit / View / PDF  
- L3 Endorsement — 7 products × Create-edit / View / PDF  

## How agent runs it

1. Load `spec-kit-openspec-superpowers` (priority).
2. Build checklist matrix (product × phase × view/PDF); tick as you go.
3. Prefer **reuse existing records** (`13-smoke-few-records`) — at most 1 new per product if none fit.
4. Layout / footer / colon issues → brainstorm + visual demo before code.
5. Fat shared files → extract BUR slice (`srp-thin`, `12-claude-task-bur-slice`).
6. Evidence: `docs/evidence/db-journey/<code>-<phase>/` (screens / PDF page / notes).
7. End with gap list + Next actions; **no commit** unless asked.

## Narrowing flags

| Flag | Meaning |
|------|---------|
| `marine` / `0189` / product name | One product |
| `quote-only` | L1 only |
| `policy-only` | L2 only |
| `endt-only` | L3 only |
| `no-endt` | Skip L3 (override default) |
| `audit` | Path map + gaps only, no code |
| `view-only` / `pdf-only` | One surface class |

## Example prompts

```text
db-journey
```

```text
db-journey audit
```

```text
7pj marine quote-only
```

```text
/db-journey endt-only
```

```text
db-journey focus: Interest Insured parity view↔PDF all products
```
