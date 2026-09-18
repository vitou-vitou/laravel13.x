---
name: product-driver
description: Manage and extend the Direct Book Product Driver & 4-Family architecture (Property, Engineering, Liability, Transit). Use when adding new products, cleaning product conditionals, checking family mappings, resolving slow page load bottlenecks, or verifying driver contracts.
---

# Product Driver Skill

Core + 4-Family pattern for Direct Book products (`0189`–`0206` + new lines).

## Daily Triggers & Optimization

| Command | Action |
|---|---|
| `/product-driver optimize` | Apply Fast-Shell + Reference Cache pattern to kill ~30s whole-page loading bottlenecks |
| `/product-driver add <code>` | Add new product line to JS driver & PHP profile with zero shared-file pollution |
| `/product-driver verify` | Run driver & family test suites (`verify-product-driver.mjs`, `verify-product-profile.php`) |
| `/product-driver clean <code>` | Replace hardcoded if-else checks with `getProductDriver` / `isFamily` / `shouldHideEmpty` |
| `/product-driver list` | Show all registered products, families, nestKeys, and layouts |

## Registries & Core Slices

- **Frontend Registry:** `resources/js/services/property_liability/core/product-driver.js`
- **Frontend Helpers:** `resources/js/services/property_liability/core/driver-helpers.js`
- **Backend Registry:** `app/Services/PL/DirectBookProductProfile.php`

## 4 Core Families

1. **Property** (`property`): Burglary (0191), Money (0192), Plate Glass (0193)
2. **Engineering** (`engineering`): CAR (0194), EAR (0199), EEI (0190)
3. **Liability** (`liability`): PI (0196), BBB (0197), D&O (0198), Bond (0195), Trade Credit (0201), Fidelity (0202)
4. **Transit** (`transit`): Marine Cargo (0189, 0206)

## Performance & Quality Rules (Fast-Shell & Minimal Diff)

1. **Never block core sidebar/header:** Keep root `<LoadingIndicator />` out of the master shell.
2. **Cache static lookups & customer options:** Option dropdowns, enums, customer type lookups, and clauses use backend caller cache (`180s–300s`).
3. **Silent Background Prefetch:** Pre-warm heavy dropdown options (`warm-customers-individual`, `warm-customers-corporate`, `warm-biz-categories`) in `warmBurglaryInfo()` on initial form load for 0ms dropdown response.
4. **Driver-direct mounting:** Family bodies mount directly via `getProductDriver(code).family` without cascading multi-file evaluations.
5. **Zero Teammate Invasiveness (Decorator Pattern):** Never refactor internal bodies of existing teammate services to add caching or formatting. Wrap at the caller boundary with 1-line decorators (e.g. `DirectBookProductProfile::memo(...)`).
6. **Shortest Working Diff Wins:** Favor 1-line callers over multi-line file modifications for zero git merge friction.
7. **Read-Local, Write-API (RLWA):** When remote API returns unpaginated 5k+ records causing UI timeouts (15s–30s), query local MySQL replica with `limit 200` + `LIKE` search (<15ms). Keep all write operations 100% through upstream API.
8. **State-Pair Law on Defaults:** When auto-setting a parent default (e.g. `customer_type = 'IC'`), never reset dependent option arrays (`customers = []`) without immediately dispatching its feeder query (`loadCustomersForType('IC')`).
