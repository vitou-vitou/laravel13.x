# BBB 0197 — Bankers Blanket Bond Overview

**Date:** 2026-09-01  
**Product Code:** 0197  
**Status:** Direct Book scope (7 products)

## Architecture

| Layer | Component |
|-------|-----------|
| **Backend** | Routes to `DirectBook::class` service (quote, policy, endorsement) |
| **Frontend** | Shared `Burglary` component suite + product-specific `bbb.js` helpers |
| **Data Model** | Nested `banker_blanket_bond` payload; shares `LIAB_KEYS` with Bond (0195) + PI (0196) |
| **PDF** | Dedicated blade template + body section |

## Key Files

| Path | Purpose |
|---|---|
| `app/Constants/ProductCode.php` | Const `BANKERS_BLANKET_BOND = '0197'` → direct-book resolver routing |
| `app/Services/PL/Quote/Concerns/AppliesDbNested.php` | BBB nested hydrate via `applyBankersBlanketBondNested()` |
| `resources/js/services/property_liability/burglary/bbb.js` | Short helpers (`isBbb`, `bbbForm`, `bbbFill`, `bbbCarry`, `checkBbb`) reuse liability template |
| `resources/js/views/PropertyLiability/Components/Burglary/BbbDetailBody.vue` | Detail display — period, deductible, limits, extensions, clauses, docs, warranty, remark |
| `resources/views/pdf/quotations/pl/direct_book/bankers_blanket_bond.blade.php` | Quotation PDF shell |
| `resources/views/pdf/quotations/pl/direct_book/sections/bankers_blanket_bond_body.blade.php` | PDF body content |

## Data Shape

BBB uses **liability-based nested structure** — `banker_blanket_bond` object, shared field names with Bond + PI.

### Core Scalars

- **Period:** insurance period, retroactive date, prior & pending date
- **Form:** proposal form date, proposal form description
- **Limits:** original policy limits, location of risk
- **Terms:** deductible, territorial limit, choice of law, choice of jurisdiction, premium payment
- **Extensions:** automatic extensions, optional extensions
- **Clauses & Exclusions:** endorsements/clauses, general exclusions
- **Terms & Conditions:** warranty (EN/KH), memorandum (EN/KH), subjectivity (EN/KH), remark (EN/KH)
- **Files:** rating upload file, supporting documents
- **Premium:** total premium (USD), refund percentage (%)

## Lifecycle

### Quote → Policy → Endorsement

1. **Quotation** — Create; hydrate from PAI; display plan + premium tabs; PDF proof
2. **Policy** — Approve quote → issue policy; same tabs + detail view
3. **Endorsement** — Edit policy info / premium / clauses → new endorsement; cascade same fields

All three layers leverage shared `DirectBook` service + liability form template.

### Endorsement Edit Tabs

| Tab | Writable? | Note |
|-----|-----------|------|
| Policy Info | **Yes** | Full update (insured, period, channel, clauses, limits, …) |
| Policy Config | No | Browse-only (J4 accepted design) |
| Commission | No | Browse-only (J4 accepted design) |
| Reinsurance | No | Browse-only (J4 accepted design) |
| Endorsement Info | **Yes** | Save endt-specific meta / premium |

**Key:** Edit = Update policy info + Save endt info; Config/Cms/Reins = read-only navigation. Match legacy PUL edit behavior.

## Frontend Helpers

### bbb.js Exports

| Export | Purpose |
|--------|---------|
| `isBbb(code)` | Check if code is `0197` |
| `BBB_KEYS` | Field map (reuses `LIAB_KEYS`) |
| `bbbForm` | Form schema (reuses liability template) |
| `bbbCarry(source)` | Carry forward values; hydrate nested `banker_blanket_bond` |
| `bbbFill(source, current)` | Fill form from source; merge with current |
| `bbbFields` | All field names |
| `bbbGrid` | Grid layout for plan/premium |
| `bbbPrem` | Premium calculation / scaling |
| `bbbStash` | Cache premium for later retrieval |
| `checkBbb(data)` | Validate BBB data shape |
| `BBB_API_FIELD_MAP` | API ↔ form field mapping |

### Detail Component (BbbDetailBody.vue)

Displays all BBB scalars in read-only format:
- Period, retroactive & prior dates, proposal form info
- Limits, deductible, location, territorial limit, law/jurisdiction
- Extensions (auto + optional), clauses, exclusions
- Warranty, memorandum, subjectivity, remark (EN + KH bilingual)
- Rating upload + supporting documents
- Premium, refund %, issue date/by

**Bilingual:** EN field checked first; falls back to KH if empty.

## Backend Services

### Routing

- **Quote:** `QuoteServiceResolver` → `DirectBook::class`
- **Policy:** `PolicyServiceResolver` → `DirectBook::class`
- **Endorsement:** `EndorsementServiceResolver` → `Burglary::class` (within DirectBook scope)

### Nested Hydrate

`AppliesDbNested` trait → `applyBankersBlanketBondNested()` extracts / normalizes BBB nested payload from PAI response.

## PDF Output

### Quotation PDF

1. **Shell:** `bankers_blanket_bond.blade.php` — PDF header + routing
2. **Body:** `sections/bankers_blanket_bond_body.blade.php` — content table (period, limits, clauses, terms, …)

**Format:** Match Direct Book quotation PDF style (tabular layout, bilingual labels where applicable).

## Current State

✅ **Routing** — quote, policy, endorsement resolvers configured  
✅ **Frontend UI** — detail display, plan/premium tabs (shared Burglary shell)  
✅ **Helpers** — thin, readable, reuse liability template  
✅ **PDF** — quotation shell + body sections ready  
⚠️ **Endorsement Tabs** — Config/Cms/Reins browse-only by design (J4 accepted); Policy Info + Endt Info editable  

## Next Steps

1. **Quotation → Policy → Endorsement** end-to-end smoke (all 7 products including BBB)
2. **PDF refinement** if endorsement detail format needs tweaks
3. **Endorsement tab wiring** if Config save portal required (task 18 deferred; currently browse-only)

## Scope

BBB is one of 7 Direct Book products:
- Marine: `0189` + twin `0206`
- Burglary: `0191`
- Money: `0192`
- Plate Glass: `0193`
- CAR: `0194`
- Bond: `0195`
- PI: `0196`
- **BBB: `0197`** ← This product
- Additional: `0198` (D&O), `0199` (EAR), `0190` (EE), `0201` (Trade Credit), `0202` (Fidelity)

See `.cursor/rules/02-pl-seven-product-scope.mdc` for scope rules.

---

**Locked:** BBB naming, endorsement edit tab patterns, PDF format. Do not refactor legacy `0121`–`0125`.
