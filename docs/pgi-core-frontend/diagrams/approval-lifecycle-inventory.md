# Approval lifecycle inventory — pgi-core-frontend

**Date:** 2026-07-27  
**Scope:** Product × Module with Approve / Accept / Submit / Revise / Delete / Generate  
**Legend:** Y = yes · N = no · — = N/A · ? = needs deep dive when generating pack  
**Doc status:** DONE = journey pack exists · PARTIAL = tables/seq in reject-flow md only · TODO = not written · N/A = no approve/reject

Source grep: `resources/js/router/*` + `app/Http/Controllers/**` approve/accept/revise + Detail.vue `canApprove*`.

---

## 1. Coverage matrix

| # | Product | Module | List URL | Apv | Acc | Sub | Rev | Del | Gen | Primary table (typical) | Doc |
|---|---------|--------|----------|-----|-----|-----|-----|-----|-----|-------------------------|-----|
| 1 | Auto | Quotation | `/quotation/autos` | Y | Y | N | Y | Y | N | `ins_quotation` | **PARTIAL** |
| 2 | Auto | Policy | `/policies/auto` | Y | N | Y | Y | ? | Y→endt | `ins_policy` | TODO |
| 3 | Auto | Endorsement | `/endorsements/auto` | Y | N | Y | Y | Y | Y | `ins_policy` | **DONE** |
| 4 | Auto | Claim Register | `/claim/auto/registers` | Y | N | — | Y | ? | — | claim tables | TODO |
| 5 | Auto | Claim Partial Pay | `/claim/auto/partial-payments` | Y | N | — | Y | ? | — | claim pay | TODO |
| 6 | Auto | Claim Process | `/claim/auto/processes` | Y | N | — | Y | ? | — | claim process | TODO |
| 7 | Auto | Claim Recovery | `/claim/auto/recoveries` | Y | N | — | ? | ? | — | recovery | TODO |
| 8 | Auto | Claim Payment | `/claim/auto/payments` | ? | N | — | ? | ? | — | payment | TODO |
| 9 | HS | Quotation | `/quotations/hs` | Y | Y | N | N | Y | N | `ins_hs_quotation` | **PARTIAL** |
| 10 | HS | Policy | `/policies/hs` | Y | N | Y | Y | ? | Y→endt | `ins_hs_policy` | TODO |
| 11 | HS | Endorsement | `/endorsements/hs` | Y | N | Y | ? | Y | Y | `ins_hs_policy` | TODO |
| 12 | HS | Claim Register | `/claim/hs/registers` | Y | N | — | Y(schema) | ? | — | `ins_hs_claim` | TODO |
| 13 | HS | Claim Payment | `/claim/hs/payments` | Y | N | — | ? | ? | — | claim pay | TODO |
| 14 | PA | Quotation | `/quotation/pa` | Y | Y | N | ? | ? | N | `ins_pa_quotation` | TODO |
| 15 | PA | Policy | `/policies/pa` | Y | N | ? | ? | ? | ? | `ins_pa_policy`? | TODO |
| 16 | PA | Batch Policy | `/policies/batch/pa` | Y | N | — | — | — | — | batch | TODO |
| 17 | PA | Endorsement | `/endorsements/pa` | Y | N | ? | ? | ? | Y | PA endt | TODO |
| 18 | PA | Claim Register | `/claim/pa/registers` | Y | N | — | Y(schema) | ? | — | PA claim | TODO |
| 19 | PA | Claim Partial | `/claim/pa/partial-payments` | Y | N | — | ? | ? | — | PA pay | TODO |
| 20 | PA | Claim Full | `/claim/pa/full-payments` | Y | N | — | ? | ? | — | PA pay | TODO |
| 21 | Travel | Quotation | `/quotation/travel` | Y | Y | N | ? | ? | N | `ins_tv_quotation` | TODO |
| 22 | Travel | Policy | `/policies/travel` | Y | N | Y | ? | ? | Y→endt | TV policy | TODO |
| 23 | Travel | Endorsement | `/endorsements/travel` | Y | N | Y | ? | Y | Y | TV endt | TODO |
| 24 | Travel | Claim Register | `/claim/travel/registers` | Y | N | — | Y(schema) | ? | — | TV claim | TODO |
| 25 | Travel | Claim Partial | `/claim/travel/partial-payments` | Y | N | — | ? | ? | — | TV pay | TODO |
| 26 | Travel | Claim Full | `/claim/travel/full-payments` | Y | N | — | ? | ? | — | TV pay | TODO |
| 27 | PL | Quotation | `/quotation/pl` | Y | Y | N | ? | ? | N | `ins_pc_quotation` | TODO |
| 28 | PL | Policy | `/policies/pl` | Y | N | ? | ? | ? | Y→endt | `ins_pc_policy`? | TODO |
| 29 | PL | Endorsement | `/endorsements/pl` | Y | N | ? | ? | ? | Y | PL endt | TODO |
| 30 | PL | Claim Register | `/claim/pl/registers` | Y | N | — | Y(schema) | ? | — | PL claim | TODO |
| 31 | PL | Claim Partial | `/claim/pl/partial-payments` | Y | N | — | ? | ? | — | PL pay | TODO |
| 32 | PL | Claim Full | `/claim/pl/full-payments` | Y | N | — | ? | ? | — | PL pay | TODO |
| 33 | PL | Renewal | `/renewals/pl` | Y | Y | — | Y | ? | — | PL renewal | TODO |
| 34 | Auto/shared | Renewal | `/renewals` | Y | Y | — | Y | ? | — | renewal | TODO |
| 35 | Config | Exchange Rate | (config UI) | Y | N | — | Y | — | — | exchange | N/A† |
| 36 | Config | Vehicle Spec | pending-specs | Y | N | — | — | — | — | vehicle | N/A† |

† Config modules: approve exists but out of insurance journey pack unless requested.

**PL note:** Direct Book (0189, 0191–0196) + legacy 0121–0125 share `/quotation/pl` shell; journey packs should call out `isDirectBook` branches when documenting.

---

## 2. Counts

| Status | Count |
|--------|------:|
| DONE (full journey PDF pack) | 1 (Auto Endorsement) |
| PARTIAL (reject tables/seq in md) | 2 (Auto Quote, HS Quote) |
| TODO (has Apv and needs pack) | ~30 |
| N/A / config | 2 |

---

## 3. Pattern families (reuse when writing packs)

| Family | Products | Shape |
|--------|----------|-------|
| **A — Quote 2-step** | Auto, HS, PA, Travel, PL | Approve → Accept; reject on either; table `ins_*_quotation` or `ins_quotation` |
| **B — Policy 1-step + Submit** | Auto, HS, Travel, PA, PL | `approved_status` submit PRG/SBM; `status` APV/REJ; RI + config gates common |
| **C — Endorsement** | Auto (**DONE**), HS, Travel, PA, PL | Generate from APV policy; Submit; Approve/Reject; often RI+config |
| **D — Claim register** | All | Approve/Reject claim; often schema approve/revise separate |
| **E — Claim payment** | All | Approve payment PND→APV/REJ |
| **F — Renewal** | Auto shared + PL | Approve + Accept + Revise |

---

## 4. Recommended generate order (no miss)

| Wave | Packs | Why |
|------|-------|-----|
| **W1** | Auto Quotation full · HS Quotation full | Finish PARTIAL → DONE |
| **W2** | HS Endorsement · Auto Policy · HS Policy | Match Auto endt style |
| **W3** | Travel Quotation / Policy / Endorsement | Same family as HS |
| **W4** | PA Quotation / Policy / Endorsement / Batch | Same family |
| **W5** | PL Quotation / Policy / Endorsement (flag Direct Book) | Shared shell |
| **W6** | Claims register+payment per product | Separate claim tables/SPs |
| **W7** | Renewals Auto + PL | Last insurance lifecycle |

After each pack: write `docs/diagrams/{slug}-journey.{md,html,pdf}` → AFK sync `laravel13.x/docs/pgi-core-frontend/`.

---

## 5. Already on disk / hub

| Artifact | Path |
|----------|------|
| Auto+HS quote reject + Auto endt section | `docs/diagrams/auto-hs-reject-flow.md` |
| Auto endorsement full | `docs/diagrams/auto-endorsement-journey.{md,html,pdf}` |
| Hub mirror | `laravel13.x/docs/pgi-core-frontend/` |
| Reusable prompt | `…/prompts/journey-reject-flow-prompt.md` |

---

## 6. API cheat (controllers with approve)

| Area | Controller |
|------|------------|
| Auto Quote | `Quotation\AutoController` |
| HS Quote | `Quotation\HSController` |
| PA Quote | `PA\QuotationController` |
| Travel Quote | `Travel\QuotationController` |
| PL Quote | `PL\QuotationController` |
| Auto Policy | `Policy\PolicyController` |
| HS Policy | `Policy\HSPolicyController` |
| PA Policy | `PA\PolicyController` |
| Travel Policy | `Travel\Policy\PolicyApprovalController` |
| PL Policy | `PL\PolicyController` |
| Auto Endt | `Insurance\EndorsementController` |
| HS Endt | `Endorsement\HSEndorsementController` |
| Travel Endt | `Endorsement\TravelEndorsementController` (+ Travel Policy EndorsementController) |
| PA Endt | `PA\EndorsementController` |
| PL Endt | `PL\EndorsementController` |
| Claims / payments | `Claim\*` · `Claim\HS\*` · `Claim\PA\*` · `Travel\Claim\*` · `PL\Claim\*` · `PL\PaymentController` |
| Renewal | `Renewal\RenewalController` · `PL\RenewalController` |

---

## 7. Next action (AFK)

Say / continue with: **`generate W1`** or **`generate hs-endorsement-journey`**.

Inventory only this file — no PDF packs beyond what already exists.
