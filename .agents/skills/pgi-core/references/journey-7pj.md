# Direct Book user journey (`7pj`)

Answer to: *"what's the high-level user journey of 7pj?"*

Not the agent workflow (that's `7pj-wave` / `db-journey.md`). This is what the **underwriter sitting at the screen** actually does.

**Line count** comes from `resources/data/direct-book-lines.json`. The slash name stays **`7pj`**. Live + WIP are both on this journey. WIP = existing docs only (no new quote).

## The whole arc

```
L1 QUOTE            L2 POLICY              L3 ENDORSEMENT
price it            bind it                change it mid-term
     │                   │                       │
     └── accepted ───────┘                       └── (repeat as needed)
```

Same three levels for every Direct Book journey (live and WIP). Product code only swaps which plan/premium fields render — the path never changes. WIP blocks **new** quote create only.

## Count (catalog 2026-09-14)

| What | N | How |
|---|---|---|
| SM codes in catalog | **14** | every JSON row, including Marine twin `0206` |
| **Journeys on 7pj** | **13** | 11 live + 2 WIP; twin collapsed: Marine is one |
| Live SM codes | **12** | `0189` `0206` `0190`–`0199` |
| WIP SM codes | **2** | `0201` TCI · `0202` FGI |
| User-journey cells | **39** | 13 × L1+L2+L3 |
| Live cells | **33** | 11 × 3 — create quote allowed |
| WIP cells | **6** | 2 × 3 — list / view / PDF / edit existing; **no new quote** |
| Must ticks (basic) | **78** | 39 cells × (no surprise + no down) |
| Must ticks (basic + advanced) | **156** | 78 × 2 action bands |
| Catalog script | **1** | `node scripts/verify-7pj.mjs` (local only — not `package.json`) — drift can hide a live line (down) |

Legacy `0121`–`0125` = **0** on this journey.

## Live journeys — staff checklist

Tick a box only when that **level** works for that line (hydrate + save + view + PDF on that level). Marine: both SM codes on the same boxes.

| # | Line | SM | L1 Quote | L2 Policy | L3 Endorsement |
|---|---|---|---|---|---|
| 1 | Marine | `0189` + `0206` | [ ] | [ ] | [ ] |
| 2 | Burglary | `0191` | [ ] | [ ] | [ ] |
| 3 | Money | `0192` | [ ] | [ ] | [ ] |
| 4 | Plate Glass | `0193` | [ ] | [ ] | [ ] |
| 5 | CAR | `0194` | [ ] | [ ] | [ ] |
| 6 | Bond | `0195` | [ ] | [ ] | [ ] |
| 7 | PI | `0196` | [ ] | [ ] | [ ] |
| 8 | BBB | `0197` | [ ] | [ ] | [ ] |
| 9 | D&O | `0198` | [ ] | [ ] | [ ] |
| 10 | EAR | `0199` | [ ] | [ ] | [ ] |

| 11 | EEI | `0190` | [ ] | [ ] | [ ] |
**30** live boxes. Old “7 lines” list stopped at PI; BBB / D&O / EAR were already `sale: live` in the catalog.

## WIP — on 7pj, existing records only

| # | Line | SM | L1 | L2 | L3 |
|---|---|---|---|---|---|
| 12 | TCI | `0201` | [ ] | [ ] | [ ] |
| 13 | FGI | `0202` | [ ] | [ ] | [ ] |

**6** WIP boxes. Tick list / view / PDF / edit on existing records. Do **not** tick create-quote while `sale: wip`.

**7pj total = 39 boxes** (30 live + 9 WIP).

A cell is done only when **basic** and **advanced** both pass **and** there is no **surprise error** and the cell is not **down** (`CONTEXT.md` Must). Operator dumps `ERROR:` / `WARNING:` in chat before spec / PRD (`22-7pj-verify.mdc`).

## Basic action

Must pass on that cell (WIP: no new quote create).

| Level | Actions |
|---|---|
| L1 Quote | List → open existing or create (live) → Info + Plan save → detail view → quotation PDF (schedule not blank) |
| L2 Policy | Issue from Quote (live) or open existing → Info + Premium hydrate/save → detail view → policy PDF |
| L3 Endorsement | Generate → save-info → detail → endorsement print |

## Advanced action

Must pass on that cell unless the product has no such tab (then `pass` with fact, not skip).

| Level | Actions |
|---|---|
| L1 Quote | Approve + accept → proceed to policy · joint / customer pool · scroll-to-error on Next · Marine: both `0189` and `0206` |
| L2 Policy | Policy Config · Commission · Reinsurance (edit only) · invoice PDF · edit hydrate ≠ create |
| L3 Endorsement | Submit + approve · print-invoice or credit note on refund · parked Config/Cms/RI browse-only unless unparked |

Missing UAT record → `WARNING`, not a pass. Architecture grade B → `ERROR`.

## Staff clicks (same must)

Not a second journey. Same 13 lines × L1/L2/L3. **Basic** = open/save/view/PDF. **Staff click** = every other control on those screens. Shared `AuthorizeBlock` / list `Search` / trash = **one type**; prove on the line you touched, then sample another code if the click is product-gated.

| Page | Clicks |
|---|---|
| **List** (Q / P / E) | Filter · open row · Delete · Export · New (quote live / policy) |
| **View** (detail) | Edit · Delete · Approve / Reject · Accept / Reject (quote) · Proceed (quote) · Submit (policy/endt) · Generate endt · Print / PDF / invoice · Export (endt) |
| **Edit** (form) | Next / Prev · tabs · Save · scroll-to-error · Config / Commission / RI |

Reject is the other button in the Approve/Accept dialog (`REJ`). Earned confirm-cancel is not a surprise.

**Out of 7pj:** Claim, Renewal — own modules.

Daily bookmark stays Basic. Add staff-click ticks when you touch that button.

## L1 — Quote

Underwriter prices a risk and sends a PDF to the client.

| Step | Screen | API |
|---|---|---|
| Browse | Quotation list, filter by product | `GET /quotations` |
| Start | Pick product → wizard opens | `POST /quotations/init` |
| Tab 1 | Product & Customer Info | `PATCH /{id}/init/update` |
| Tab 2 | Plan Info — sum insured, coverage rows | `POST /init/plan` |
| Tab 3 | Premium — rates, discounts, totals | `POST /quotations` |
| Review | Detail view (read-only schedule) | `GET /{id}` |
| Send | Download quotation PDF | `GET /{id}/pdf` |
| Sign-off | Approve, then Accept | `PATCH /{id}/approve` → `/accept` |
| Convert | Proceed to policy | `GET /{id}/proceed` |

Three tabs on create. Premium tab hides for some products.

## L2 — Policy

Accepted quote becomes a **Policy**. More tabs appear, because now money and reinsurance are real.

| Tab | What |
|---|---|
| Policy Info | Insured, period, product |
| Premium | Final figures |
| Policy Config | Instalments, payment terms |
| Insured Person | Named persons (only where the product needs it) |
| Commission | Agent/broker split |
| Reinsurance | Treaty and facultative shares |

Last three appear **only in edit mode** — you cannot fill commission on a policy that doesn't exist yet.

Then: Submit → Approve → download policy PDF + invoice.

```
POST /policies/store → PATCH /{id}/config → /submit → /approve
GET /{id}/download · /{policyId}/download-invoice
```

## L3 — Endorsement

Something changed mid-term: sum insured up, address moved, policy cancelled early.

```
POST /endorsements/{id}/generate     pick type, snapshot the policy
PATCH /{dataId}/save-info            edit the changed values
PATCH /{dataId}/submit               send for review
PATCH /{dataId}/approve              underwriter signs off
GET /{dataId}/print                  endorsement PDF
GET /{dataId}/print-invoice          additional/refund premium invoice
```

Refund endorsements produce a **credit note**, not an invoice — different document type on the same path.

## Status vocabulary

`PND` pending · `SBM` submitted · `PRG` in progress · `APV` approved · `ACP` accepted · `ACT` active · `REJ` rejected

Source: `resources/js/enum.js` → `RECORD_STATUS`. Rejection sends the record back for edit; it isn't a dead end.

## Beside the main arc

**Renewal** (`routes/pl.php` `renewals`) — batch-generate at expiry, no-claim auto-approval, then approve/accept like a quote. Nearly a fourth level, but not counted in `7pj`.

**Claim** — separate module, hangs off an active policy.

## Why it matters for code

- Same three-level path for every catalog line means **shared components, config-driven per code** — the `burglary/` folder.
- A journey step failing on one product but not another is almost always a product-code gate, not a broken flow.
- Each level has its own PDF. View and PDF must agree on the schedule band (INSURED NAME → ISSUED BY); parity rules in `db-journey.md`.
- Default `/7pj` wave = **13 journeys** (11 live + 2 WIP). Live: create quote allowed. WIP: existing docs only. Pin with `bbb` / `0197`, `dno` / `0198`, `ear` / `0199`, `eei` / `0190`, `tci` / `0201`, `fgi` / `0202`.

## PDF fixture (URL → disk)

Agent review of a print / storage / view URL **before** copying bytes. Core PA (`/policy-service/…`) stays core. Direct Book additional uses local `STORAGE_BUCKET=upload`.

Local only (`/scripts/` exclude). **Not** `package.json`. No `verify:7pj` / `7pj:fixture` npm aliases for the team.

```bash
node scripts/verify-7pj.mjs
node scripts/7pj-pdf-fixture.mjs '<url>'
node scripts/7pj-pdf-fixture.mjs '<url>' --copy '/path/to/saved.pdf-or.png'
```

| URL shape | Parses | Save as |
|---|---|---|
| `/pl/quotations/{id}/pdf` | level `quote`, id | `.scratch/pdf-fixtures/{host}/{code}-{level}-{id}-{lang}.pdf` |
| `/pl/policies/{id}/download` | level `policy`, id | same pattern |
| `/quotation/pl/{slug\|code}/{id}` | product + id | `.txt` note (view, not bytes) |
| `/pl/download-from-storage/{file}` | storage key only | `storage/app/public/uploads/{file}` (exact name for `getFileUrl`) |
| `/policy-service/{id}/download-policy-schedule` | core PA | warn — not Direct Book additional |

**Same DB:** UAT/remote URL → rewrite host to `.env` `APP_URL`. Print `open` (local). View URL also emits `localPrint` PDF. Storage → local `/pl/download-from-storage/{file}`.

**Review:** source host · `open` · SM code · id · lang · flags. Do not commit PNG/PDF.
