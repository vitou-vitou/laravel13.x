---
name: pl-scroll-to-error
description: Scroll Direct Book Plan/Info to the topmost failed field on Next, without remounting or flipping CKEditor. Use when adding required-field validation, scroll-to-error, doubled CKEditor, or toolbar under the editable after Next.
---

# PL scroll to first error

## Symptom

- Next with blank required fields: toast fires, page stays at the bottom (Prev/Next).
- After scroll ships: CKEditor **doubles** (two toolbars in one cell) or **toolbar drops under** the editable (KH Location of Risk).

## Cause

| Bug | Cause |
|---|---|
| No scroll | `errors` set, no DOM hook, no `scrollIntoView` |
| Doubled editor | row loop `:key` is **index** (`q-row-0`). Add/remove a row remounts every CKEditor below |
| Toolbar under box | `scrollToError` focused `[tabindex]` / CKEditor hidden textarea. ClassicEditor re-lays out mid-scroll |

## Portal (reuse)

`resources/js/services/property_liability/burglary/scroll.js` → `scrollToError(errors, root?)`.

```js
await nextTick()
scrollToError(errors.value)
```

Call from **one** fail site (`handleNext` / persist catch). Do not copy into each `return false`.

## Checklist

1. Wrapper `:data-field="field.name"` on every field cell (pair / triple / datePair / editor grid).
2. Fail path → `nextTick` → `scrollToError`.
3. Row loops that contain CKEditor: **content key**, not index.

```js
const rowKey = (row) => {
    if (row.kind === 'datePair') return `r-${row.left.name}`
    if (row.kind === 'marinePeriod') return `r-${row.periodType.name}`
    if (row.kind === 'productType') return 'r-productType'
    return `r-${row.fields?.[0]?.name ?? 'row'}`
}
```

4. **Never focus CKEditor.** `scroll.js` focuses only native `input/textarea/select` outside `.ck-host` / `.ck-editor`. Scroll the wrapper; user sees the red line.
5. `prefers-reduced-motion` → `behavior: 'auto'`. Topmost = `getBoundingClientRect().top`, not object-key order.

## Wired shells (Direct Book)

| Shell | File |
|---|---|
| Quote Plan | `views/PropertyLiability/Components/Burglary/Plan.vue` |
| Quote Info | `views/PropertyLiability/Quotation/Components/Burglary/DirectBookQuotationInfo.vue` |
| Policy / Endt Info | `views/PropertyLiability/Policy/Components/Burglary/BurglaryPolicyInfo.vue` |

Same helper. New tab = `data-field` + one `scrollToError` call. Do not fork a second scroller.

## Do not

- `:key="'row-' + index"` on a grid that hosts CKEditor.
- `querySelector('[tabindex]')` after scroll (hits CK editable).
- Touch legacy `0121`–`0125` Info/Plan (`Quotation/Tabs/Info.vue`, `FireProperty/Plan.vue`).
- Playwright / agent-browser unless user says `enable playwright`. IDE browser MCP OK.

## Verify

- Blank Next → viewport centers on **topmost** red field.
- Location EN/KH: **one** toolbar **above** each editable (no nest, no flip).
- `npm run build` after Vue/JS change.
