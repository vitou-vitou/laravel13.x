---
name: dno-quotation-new
description: Build or fix Directors & Officers (0198) quotation — create, edit hydrate, print. Nested PAI directors_and_officers. Blank Plan/Premium after save, swagger nest, PDF. Layout, checkDnoPlan, scroll. Remote IDE browser smoke. Use when D&O edit fields empty, print blank, or user says remote the D&O journey.
---

# D&O Quotation New (0198)

`PRODUCT_CODE.DIRECTORS_OFFICERS` = `0198`. `cfg.hasDno`. Nested PAI `directors_and_officers`.

URL: `/quotation/pl/new` → `DirectBookQuotationFormShell.vue`.

| Tab | Key | Component |
|---|---|---|
| Product & Customer Info | `prod` | `DirectBookQuotationInfo.vue` |
| Plan Info. | `plan` | `DirectBookQuotationPlan.vue` → `Plan.vue` |
| Premium | `premium` | `DirectBookQuotationPremium.vue` → `Premium.vue` `kind === 'dno'` |

Create: Plan + Premium tabs stay **disabled** until Info save (`isEditMode` = route id). Do not unlock them client-side.

Do **not** edit `0121`–`0125`. Do not fork a second D&O Plan/Premium Vue.

## Files

| Job | Path |
|---|---|
| Shell | `…/Quotation/Components/Burglary/DirectBookQuotationFormShell.vue` |
| Info | `…/Quotation/Components/Burglary/DirectBookQuotationInfo.vue` |
| Info rules | `resources/js/services/property_liability/burglary/tab1-check.js` |
| Plan | `resources/js/views/PropertyLiability/Components/Burglary/Plan.vue` |
| Plan rules | `resources/js/services/property_liability/burglary/dno.js` (`checkDnoPlan`) |
| Premium | `…/Components/Burglary/Premium.vue` |
| Premium rules | `dno.js` (`checkDno`, `dnoPrem`, `dnoStash`, `dnoFill`) |
| Sub / extra limits | `DnoSubLimits.vue`, `DnoAdditionalLimits.vue` |
| Scroll | `resources/js/services/property_liability/burglary/scroll.js` |

Shared scroll skill: `pl-scroll-to-error`. Remote drive: `ide-browser-remote`.

## Tab 1 — Product & Customer Info

Portal: `checkTab1` / `hasTab1Err`. Shared Direct Book, not D&O-only.

Required: `product_code`, `customer_type`, `customer_no`, `joint_status`, `insured_name_en`, `insured_name_kh`. Joint `J` → at least one joint row (`customer_no`, `joint_level`, `permission`).

Fail: toast → `nextTick` → `scrollToError`. Wrappers `:data-field="field.name"`.

## Tab 2 — Plan Info.

### Layout

Dates **one row**, `grid-cols-4` (no `md:grid-cols-2`):

| Field | Span |
|---|---|
| Retroactive Date | 25% |
| Prior & Pending Date | 25% |
| Personal Form Date Signed | 50% |

Business Channel / Business Name = **next** row. Never beside Personal Form.

EN/KH CKEditor pairs 50/50: Interest, Extended Reporting Period, Acquisition Threshold, Location of Risk, Deductible, Subjectivity, Remark (video). Also Validity, Geographical Scope, Governing Law, Coverage textareas in `dno.js` defs.

`:key="rowKey(row)"` — never index.

### Next (quotation)

`checkDnoPlan(form, cfg, {})` when `cfg.hasDno && isQuotation`.

Rich blank (`dnoPlain`): `validity_en`, `validity_kh`, `location_of_risk`, `location_of_risk_kh`.

Scalar: `effective_date_from`, `effective_date_to`, `sale_channel`, `business_code`, `handler_code`.

Inline `{Label} is required`. Location uses `cfg.locationLabel`. Deductible `*` = `cfg.deductibleRequired` (keep UI; not in `checkDnoPlan`).

Fail: `handleNext` → `nextTick` → `scrollToError`.

Carry: `dnoForm` / `dnoCarry` / `dnoPlain`. `dnoCarry` + `dnoFromQuote` read **nested** `directors_and_officers` (JSON string OK). `mergeTabs` flattens `dnoFromQuote` on edit.

## Tab 3 — Premium

`kind === 'dno'`. Client: `checkDno` → `total_premium` amount only.

Grid `grid-cols-4`: Total of Premium 50%, Limit of Liability EN/KH, Limit of Liability Description EN/KH. Then Sub Limit + Additional Limit tables.

`data-field` on those scalar wrappers. Submit fail: `handleSubmit` → toast → `nextTick` → `scrollToError`.

Stash/save: `dnoStash` + `filterLimitRows`. Nested key `directors_and_officers`. `dnoFieldMap()` for 422.

Empty limit rows: keep one blank row in UI; strip empty on payload.

## Edit hydrate (save → edit blank)

PAI POST nests D&O extras under `directors_and_officers` and **unsets** the same keys at top level (`AppliesDbNested::applyDirectorsOfficersNested`).

Swagger nest (not BBB `banker_blanket_bond`): `geographical_scope_*`, `governing_law_*`, `interest_*`, `extended_reporting_period_*`, `acquisition_threshold_*`, `personal_form_date_signed`, `sub_limits[]` (`items`, `coverage_en/kh`, `limit_liability_en/kh`), `additional_limits[]`.

Do **not** look for Retroactive / geo inside BBB schema.

Edit GET often has nested-only, plus top-level `sub_limits: []` that must **not** win over nested rows.

| Portal | Job |
|---|---|
| `plHoistDnoDetailScalars` / `plMergeDirectorsOfficersFromSource` | PHP flatten + /detail merge (same idea as PI) |
| `plDirectBookMissingDnoScheduleFields` | Force `/detail` when nest incomplete |
| `dnoFill` / `pickDnoRows` | Premium; nonempty nested rows beat empty `[]` |
| `dnoFromQuote` in `mergeTabs` | Flatten nest onto Plan/Premium init |

Print / detail: `plPrepareDirectBookProductPrintFields` hoists **DNO keys** (not BBB `territorial_limit` / `proposal_form_date`). PDF body `directors_officers_body` — Geographical Scope, Governing Law, ERP, Acquisition, sub/additional limit rows. VM `liabilityBody()` nests `directors_and_officers`.

Policy edit: `PolicyForm` + `PolicyService::edit` merge nest from main GET / parent quote. Endorsement edit: `EndorsementForm::toArray` same hoist. FE `fillEdit` uses `dnoFromQuote` + `dnoCarry` + `dnoFill`. Endt print note still uses `plPrepDetail` → `plFlatEndt` (already hoists DNO).

## CKEditor (Plan video)

1. Double editor → index `:key`. Fix `rowKey`.
2. Toolbar under box after Next → do not focus `.ck-host` / `[tabindex]`. `scrollToError` native inputs only.

One toolbar **above** each editable.

## Portal

| Job | One function |
|---|---|
| Info validate | `checkTab1` |
| Plan validate | `checkDnoPlan` |
| Premium validate | `checkDno` |
| Scroll | `scrollToError` |
| Edit flatten | `dnoFromQuote` / `plHoistDnoDetailScalars` |

No duplicate blank checks in SFCs.

## Verify

Quote video (save → edit blank → swagger nest → print blank) is enough for this bug. **Do not** record a full Quote → Policy → Endorsement Bandicam unless policy/endt edit is still empty after this hydrate.

Manual or **remote** (`ide-browser-remote`): one D&O quote edit + print. Policy/endt: open edit of an existing converted row if you have one — no new journey required.

1. New → Info blank Next → scroll topmost (Customer / Insured).
2. Save Info → Plan. Location EN filled, KH empty → Next → toast + `Location of Risk (KH) is required` + scroll. KH toolbar **on top**.
3. Dates 25/25/50 one row.
4. Premium blank Total of Premium → Submit → scroll to that field.
5. `npm run build`.
6. Save all → edit same quote → Plan geo/interest/ERP + Premium descriptions + sub/additional limits still filled.
7. Print PDF shows those DNO labels (not blank BBB Territorial Limit).

## Remote (reuse 1958-class row)

Follow `ide-browser-remote`. Overlay:

| Item | Value |
|---|---|
| Edit | `/quotation/pl/directors-officers/{id}/edit` |
| API | `GET /pl/quotations/{id}/edit` → nest `directors_and_officers` |
| Print | `/pl/quotations/{id}/pdf?letterHead=0&lang=EN&signature=0&noStamp=1` |
| Nest tables | `sub_limits[]`, `additional_limits[]` — nonempty nest beats top `[]` |

Save on **Premium**. Reload edit. Pass hydrate if nest tables still show. Pass print if `pdftotext` has `DIRECTORS AND OFFICERS INSURANCE QUOTATION` (IDE viewer black ≠ fail).

Plan geo / Interest / ERP empty **and** nest scalars `null` = PAI did not persist those keys — not FE wipe. Do not open BBB swagger for Retroactive.

## Do not

- Playwright unless `enable playwright`.
- Guess extra required fields.
- Enable Plan/Premium tabs before Info creates an id.
