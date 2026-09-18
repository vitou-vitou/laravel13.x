---
name: pl-direct-book-onboard
description: Build, diagnose, review, and auto-triage Direct Book and PL insurance products across Quote, Policy, and Endorsement (FGI 0202, TCI 0201, Bond, Marine, D&O, CAR, EAR, etc.). Automatically inspects local `storage/logs/laravel.log`, permissions, and product line filters without manual pastes, delivering instant root-cause classification and automated zero-regression fixes.
---

# PL Direct Book Automated Diagnosis, Perm Triaging & Fast-Fix Engine

High-velocity troubleshooting playbook for PL Direct Book journeys across `pgi-core-frontend` and `pgi-core-backend`.

---

## 1. Zero-Paste Diagnostic Pipeline (Speed + Precision)

When debugging any PL Direct Book issue (Quote, Policy, Endorsement):

```
[Issue Triggered / Error Toast / Missing Dropdown Item]
                         ↓
[1. Check Local storage/logs/laravel.log (Last 100-200 lines)]
  ├─ Has Log Entry? → Read exact `body`, `response`, `code`, and `trace_id`
  └─ No Log Entry? → 100% Client-Side Validation Block (`validateForm()` / `validateTab1()`)
                         ↓
[2. Check Permission & Catalog Alignment]
  ├─ MultiSelect Missing Item? → Check `permissions.js` (Primary + Fallback `_INSURANCE_` keys)
  └─ Product Line Filtering Out? → Check `enum.js` (`PRODUCT_LINE_CODE`) vs MySQL `products.product_line_code`
                         ↓
[3. Instant Root Cause Verdict & Auto-Fix]
  ├─ Frontend Fault: Apply patch immediately in Vue/JS with 0 commentary
  └─ Backend Fault: Generate 1-paragraph actionable ticket with table/payload specifics
```

---

## 2. Root Cause Classification Matrix

| Symptom / Log Output | Layer | Exact Root Cause | Immediate Action |
|---|---|---|---|
| No new log created on Save/Next | **Frontend Validation** | Form blocked in `validateForm()` or `validateTab1()`. | Check product conditionals in `Premium.vue` / `BurglaryPolicyInfo.vue` for hidden field validations (e.g. row premiums). |
| Product missing in List MultiSelect | **Frontend Perm / Line** | Mismatched permission slug (`TRADE_CREDIT_INSURANCE_QUOTATION` vs `TRADE_CREDIT_QUOTATION`) or wrong `PRODUCT_LINE_CODE` in `enum.js`. | Add alias to `QUOTE_PERM_FALLBACK` / `POLICY_PERM_FALLBACK` in `permissions.js` and align `enum.js` with DB. |
| Red error hints stay after typing/picking | **Frontend Watcher** | Missing reactive watcher on `form`. | Add `watch(form, ...)` error clearing watcher. |
| Values wiped on Prev/Next navigation | **Frontend Stash** | Missing state retention in `stashPremium()` / `fill()`. | Add product keys to `stashPremium()` and `fill()` in `Premium.vue`. |
| `relation "..." does not exist` (500) | **Backend DB** | PostgreSQL table missing on UAT DB. | Output BE migration ticket with table name. |
| `violates not-null constraint` (500) | **Backend PAI** | DB column requires value, frontend sent `null`. | Add fallback (`"-"` or default) in `docs.js` (`infoPayload`). |
| `HTTP 422` with "Please complete required fields" | **Backend PAI** | Payload shape mismatch, unescaped HTML, or missing key. | Inspect `body` in `laravel.log` vs Swagger schema; scrub in `docs.js`. |

---

## 3. High-Quality Code Implementation Standards

### A. Permission Fallback Pattern (`permissions.js`)
Support both short and long SM permission slugs:
```javascript
const QUOTE_PERM_FALLBACK = {
  '0201': 'TRADE_CREDIT_QUOTATION',
  '0202': 'FIDELITY_GUARANTEE_QUOTATION',
};

export const quoteViewPermKeys = (productCode) => {
  const code = padProCode(productCode);
  const keys = [];
  if (PER_QUOTE_BY_PRO_CODE[code]) keys.push(PER_QUOTE_BY_PRO_CODE[code]);
  if (QUOTE_PERM_FALLBACK[code]) keys.push(QUOTE_PERM_FALLBACK[code]);
  return keys;
};
```

### B. Product-Guarded Validation (`Premium.vue`)
```javascript
if (kind.value === 'car') {
    const nextErrors = {};
    if (isFgiKind.value) {
        const amountMsg = checkAmount(form.amount_guaranteed, {
            kind: 'sum_insured',
            required: totalsRequired.value,
        });
        if (amountMsg) addFieldErr(nextErrors, 'amount_guaranteed', amountMsg);
    }
    form.section_i_items.forEach((row, index) => {
        validateSectionItemText(row, index, nextErrors, 'section_i_items');
        checkRowAmount(row, index, nextErrors, { prefix: 'section_i_items', field: 'sum_insured', kind: 'sum_insured' });
        if (!isFgiKind.value) {
            checkRowAmount(row, index, nextErrors, { prefix: 'section_i_items', field: 'premium', kind: 'premium' });
        }
    });
}
```

### C. PDF & View Parity Law
- Every field displayed in View Detail (`Detail.vue`) must have exact parity in PDF blade section (`pdf.quotations.pl.direct_book.sections.<product>_body`).
- Phantom sections (e.g. Automatic Extensions on products that don't have them) must be suppressed in `ProductCode.php` via `hideAutomaticExtCodes()` and `hideOptionalExtCodes()`.
