# Direct Book journey (`db-journey`)

**Short names (any of these starts the long task):**

| Say | Same meaning |
|-----|----------------|
| **`db-journey`** | Canonical (shortest clear) |
| `/db-journey` | Cursor command |
| `pl-journey` | Alias |
| `7pj` | Ultra-short |
| `7-product-journey` / `7-product-user-journey` | Legacy nickname → **6** in-scope products |

## Scope

**In scope (6):** `0189` Marine · `0191` Burglary · `0192` Money · `0193` Plate · `0194` CAR · `0196` PI  

**Out:** Bond `0195` (removed from Direct Book) · legacy `0121`–`0125`

## Default journey matrix

For **each** product, cover:

| Phase | Surfaces |
|-------|----------|
| **Quote** | Form/edit · Detail **view** · Print **PDF** |
| **Policy** | Issue/edit · Detail **view** · Print **PDF** |
| **Endt** | Only if user said `+endt` |

Path map every cell: shared shell? BUR slice? PDF partial?

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
| `quote-only` | Quote surfaces only |
| `policy-only` | Policy surfaces only |
| `+endt` | Add endorsement phase |
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
/db-journey +endt
```

```text
db-journey focus: Interest Insured parity view↔PDF all products
```
