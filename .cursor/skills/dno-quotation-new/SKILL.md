---
name: dno-quotation-new
description: Build or fix Directors & Officers (0198) Quotation New Plan Info — required fields, EN/KH CKEditor pairs, 25/25/50 dates, scroll-to-first-error. Use when D&O quote create, Location of Risk KH required, toolbar under editor, or Bandicam of Plan Next fail.
---

# D&O Quotation New (0198)

Code `PRODUCT_CODE.DIRECTORS_OFFICERS` = `0198`. `cfg.hasDno`. Nested PAI block `directors_and_officers`.

Quote create URL: `/quotation/pl/new` → shell `DirectBookQuotationFormShell.vue`.

Tabs: **Product & Customer Info** → **Plan Info.** → **Premium**.

This skill is the **Plan** slice (source video: Next with Location KH blank). Info tab = shared Direct Book, not D&O-only. Premium = `Premium.vue` `dno` branch.

## Files

| Job | Path |
|---|---|
| Plan UI | `resources/js/views/PropertyLiability/Components/Burglary/Plan.vue` |
| Quote Plan entry | `…/Quotation/Components/Burglary/DirectBookQuotationPlan.vue` |
| Quote Info | `…/Quotation/Components/Burglary/DirectBookQuotationInfo.vue` |
| DNO rules | `resources/js/services/property_liability/burglary/dno.js` |
| Scroll | `resources/js/services/property_liability/burglary/scroll.js` |
| Dates | `dnoDateFields` in `dno.js` |

Do **not** edit `0121`–`0125` Plan/Info.

## Plan layout (must match)

Dates **one row**, `grid-cols-4` (prod-safe; no `md:grid-cols-2`):

| Field | Span |
|---|---|
| Retroactive Date | 25% (`col-span-1`) |
| Prior & Pending Date | 25% (`col-span-1`) |
| Personal Form Date Signed | 50% (`col-span-2`) |

Then Business Channel / Business Name on the **next** row. Never beside Personal Form.

EN/KH editors: **pair** 50/50 (`pairGrid`). Video fields: Interest, Extended Reporting Period, Acquisition Threshold, Location of Risk, Deductible, Subjectivity, Remark.

Row loop `:key="rowKey(row)"` — never index. See skill `pl-scroll-to-error`.

## Client Next (quotation)

`checkDnoPlan(form, cfg, {})` in `validateForm` when `cfg.hasDno && isQuotation`.

Required (blank → inline `{Label} is required` + toast `Please complete all required fields.`):

Rich (empty HTML = blank via `dnoPlain`): `validity_en`, `validity_kh`, `location_of_risk`, `location_of_risk_kh`.

Scalar: `effective_date_from`, `effective_date_to`, `sale_channel`, `business_code`, `handler_code`.

Location labels use `cfg.locationLabel` → `Location of Risk (EN|KH)`. Deductible `*` is `cfg.deductibleRequired` (server / other check; keep UI required).

Fail: `errors.value = …` → `announce(REQUIRED_FIELDS_TOAST)` → `handleNext` `nextTick` → `scrollToError`.

## CKEditor (video bugs)

1. **Double editor** — index `:key` remount. Fix: `rowKey`.
2. **Toolbar under box** (Location KH after Next) — do not focus `.ck-host` / `[tabindex]` inside CK. `scrollToError` native inputs only.

Each cell: **one** toolbar **above** editable. `:data-field="field.name"` on the wrapper.

## Portal

- Validate: `checkDnoPlan` only. Do not duplicate blank checks in the SFC.
- Scroll: `scrollToError` only.
- Carry/hydrate: `dnoForm` / `dnoCarry` / `dnoPlain`.

## Verify (from the video)

1. D&O New → Plan → fill Location EN, leave KH empty → Next.
2. Toast + red `Location of Risk (KH) is required`.
3. Viewport centers that field. KH toolbar stays **on top**.
4. Dates 25/25/50 one row. `npm run build`.

## Do not

- Playwright unless `enable playwright`.
- Fork a second D&O Plan Vue.
- Guess extra required fields not in `checkDnoPlan` or `cfg.*Required`.
