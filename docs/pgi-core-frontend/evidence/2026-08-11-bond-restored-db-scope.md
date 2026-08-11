# Bond 0195 restored to Direct Book scope — 2026-08-11

## Ask

Apply Bond `0195` **in scope for all** — rules, journey (`db-journey` L1–L3), session handoff.
Supersedes 2026-07-29 removal.

## Matrix — 7 products

| SM code | Constant |
|---------|----------|
| `0189` | `MARINE_CARGO` |
| `0191` | `BURGLARY` |
| `0192` | `MONEY_INSURANCE` |
| `0193` | `PLATE_GLASS` |
| `0194` | `CONSTRUCTION_ALL_RISKS` |
| `0195` | `BOND` |
| `0196` | `PROFESSIONAL_INDEMNITY` |

Journey phases (all default): **L1 Quote · L2 Policy · L3 Endorsement**.

## Code status (already present)

No code restore needed this pass — Bond already in:

- `ProductCode::directBookCodes()`
- `resources/js/services/property_liability/burglary/scope.js`
- `mock-burglary-service/constants.js` (`BURGLARY_PRODUCT_CODES`, UI codes)
- Plan/Premium `makeEntry` / Detail Bond value row

## Docs / rules updated

- `.cursor/rules/02-pl-seven-product-scope.mdc` — 7-product matrix
- `docs/SESSION_STATE.md` — restore note
- `spec-kit-openspec-superpowers/references/db-journey.md` + `/db-journey` command
- `references/pl-db-edit-lock-portal.md` — scope line
- laravel13.x hub sync

## Historical

See `docs/evidence/2026-07-29-bond-removed-from-db-scope.md` (superseded).
