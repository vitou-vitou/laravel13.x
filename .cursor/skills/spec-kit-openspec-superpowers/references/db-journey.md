# Direct Book journey (`db-journey`)

**Short names (any of these starts the long task):**

| Say | Same meaning |
|-----|----------------|
| **`db-journey`** | Canonical (shortest clear) |
| `/db-journey` | Cursor command |
| `pl-journey` | Alias |
| `7pj` | Ultra-short (nickname; **6** in-scope products) |
| `7-product-journey` / `7-product-user-journey` | Legacy nickname → same list |

## Scope

**In scope (6):** `0189` Marine · `0191` Burglary · `0192` Money · `0193` Plate · `0194` CAR · `0196` PI  

**Out:** Bond `0195` (removed from Direct Book) · legacy `0121`–`0125`

## Default journey matrix

For **each** product, cover:

| Phase | Surfaces |
|-------|----------|
| **Quote** | Form/edit · Detail **view** · Print **PDF** |
| **Policy** | Issue/edit · Detail **view** · Print **PDF** |
| **Endt** | Only if user said `+endt` / `endt-only` |

Path map every cell: shared shell? BUR slice? PDF partial?

## View ↔ PDF style parity (locked experience)

**`view-only` and `pdf-only` use the same content-style checklist** for schedule fields. Compare label wording, uppercase, order, and value presentation — not admin chrome.

### In parity (from top of schedule through end)

Typical Direct Book Detail / print block — treat as one band:

- THE INSURED NAME  
- CORRESPONDENCE ADDRESS  
- BUSINESS / OCCUPATION  
- … (period, wording, extensions, voyage / interest / location, premium, deductible, clauses, warranty, …)  
- INTEREST INSURED (table + Total / extras under Total)  
- … continue through remaining schedule rows …  
- **ISSUED BY** (end of band)

Agent must check **view and PDF** against each other for these fields (and siblings in the same schedule) unless the user narrowed to one surface.

### Out of parity (do not force PDF ≈ view)

| Skip | Why |
|------|-----|
| Colon gutter `:` in admin flex rows | View chrome; PDF uses `Label:` in label cell / 2-col print |
| Buttons, tabs, AuthorizeBlock, download links | Admin only |
| Loading / toast / PrimeVue chrome | Admin only |
| Form edit controls | Not schedule view/PDF |
| Sample black print borders vs admin grey table | Print contract ≠ admin polish (`impeccable` skip) |

### Alignment discipline (when touching amounts)

Follow locked **S2**: Sum Insured body + Total (+ rows under Total) **left**; label against amount column. Same on view + PDF where that Total exists.

## How agent runs it

1. Load `spec-kit-openspec-superpowers` (priority).
2. Build checklist matrix; tick as you go.
3. Prefer **reuse existing records** (`13-smoke-few-records`).
4. Layout / footer / colon issues → brainstorm + visual demo before code.
5. Fat shared → BUR slice (`srp-thin`, `12-claude-task-bur-slice`).
6. Evidence: `docs/evidence/db-journey/<code>-<phase>/`.
7. **`crop png`** (optional flag): save **tight crops** of schedule bands (view screenshot + PDF page/region) as PNG under that folder — not full-desktop noise. Pair view crop + PDF crop per product when both surfaces run.
8. No commit unless asked.

## Narrowing flags

| Flag | Meaning |
|------|---------|
| `marine` / `0189` / product name | One product |
| `quote-only` / `policy-only` | One phase |
| `+endt` / `endt-only` | Include / only endorsement |
| `audit` | Path map + gaps only, no code |
| **`view-only`** | Detail **view** — style parity checklist; skip PDF |
| **`pdf-only`** | **PDF** — same checklist as view-only; skip admin chrome |
| **`view-only` + `pdf-only`** | Full view↔PDF parity pass |
| **`crop png`** / `crops` | Capture PNG crops for evidence (schedule band INTEREST…ISSUED BY); store under `docs/evidence/db-journey/` |

## Example prompts

```text
/spec-kit-openspec-superpowers
7pj view-only pdf-only crop png
```

```text
7pj view-only
```

```text
7pj pdf-only
```

```text
7pj view-only pdf-only
```

```text
7pj pdf-only burglary crop png
```

```text
db-journey focus: schedule labels INTEREST INSURED → ISSUED BY view↔PDF
```
