# PGI Core Frontend (UAT) — 97-Task Parallel Agent Build Prompt

**Goal:** Take `phillipinsurancekh/pgi-core-frontend` on branch **`uat`** from zero tests and UAT-only stability to production-ready — work that normally spans **3 months** — compressed into a **~10-hour** orchestrated run using up to **97 parallel task slices** (10 agents per wave, 16 sequential batches).

**Local clone:** `examples/pgi-core-frontend-uat` (branch `uat`, Laravel 12, Vue 3, Keycloak)

**Calendar roadmap:** `examples/pgi-core-frontend-uat/docs/ROADMAP.md`

**How to use:** Paste the Master Orchestrator Prompt below into your main agent session.

---

## Master Orchestrator Prompt

```
You are the orchestrator for hardening and test-enabling PGI Core Frontend (UAT) —
Phillip Insurance Cambodia's internal core ops app: quotations, policies,
endorsements, claims, renewals, payments, reinsurance, PDFs, and security admin.

Project path: examples/pgi-core-frontend-uat
Branch: uat (do not re-scaffold; this is an existing 675-view Vue + Laravel monolith)

Use dispatching-parallel-agents (.agents/skills/dispatching-parallel-agents/SKILL.md):
one agent per INDEPENDENT slice, batches run sequentially, tasks within a batch in parallel.
After each batch: php artisan test (create tests/ if missing), fix conflicts, commit.

CONSTRAINTS:
- Wall-clock budget: 10 hours. Cut polish (Batch 13) and optional integrations (Batch 12 partial)
  before skipping tests or security (Batches 14–15).
- TDD: every slice adds or extends PHPUnit feature tests.
- No two tasks in the same batch edit the same file — split to sequential batches if needed.
- Private dep: plb/security_management — document stub/mirror if CI cannot reach GitHub VCS repo.
- Match existing patterns: Vue 3 + PrimeVue views, Laravel controllers, plb security package.

97 TASK SLICES:

BATCH 1 — Blocking foundation (1 agent, sequential)
1. Audit uat branch: composer.json, .env.example, bootstrap, document blockers in docs/SETUP.md

BATCH 2 — DevOps & reproducible env (parallel)
2. Docker Compose (nginx + php-fpm) OR Herd-oriented setup doc
3. start.sh / Dockerfile alignment with Laravel 12
4. CI workflow: composer install, npm ci, php artisan test (allow empty pass initially)
5. .env.example completeness (Keycloak, DB, Redis, queue)
6. Composer scripts + patches/post-install verification

BATCH 3 — Test infrastructure (parallel)
7. Create tests/TestCase.php + Feature + Unit directories
8. phpunit.xml + base RefreshDatabase / HTTP test helpers
9. Factory stubs for User + Customer (minimal)
10. Auth test trait (Keycloak/session mock strategy)
11. API JSON assertion helpers for controller tests
12. Sample smoke test: application returns 200 on login route

BATCH 4 — Auth & SecurityManagement (parallel)
13. AuthController login/logout feature tests
14. Keycloak Socialite config validation test
15. SM User index/search controller tests
16. SM Role CRUD + permission assignment tests
17. SM Group CRUD tests
18. SM Function CRUD tests
19. SM Application CRUD tests
20. Policy/middleware tests for unauthorized access (403)

BATCH 5 — Customer & user admin (parallel)
21. CustomerController CRUD feature tests
22. CustomerProfile + classification tests
23. Country LOV endpoint tests
24. UserManagement User CRUD tests
25. UserManagement Role/Group/Function tests
26. BankInformation CRUD tests

BATCH 6 — Product configuration (parallel)
27. Product + ProductLine controller tests
28. Make/Model/Vehicle LOV tests
29. VehicleUsage + classification tests
30. Travel rating configuration tests
31. PA extension option tests
32. Exchange rate + NCD configuration tests
33. Policy wording version tests
34. Formula / component formula element tests

BATCH 7 — Quotation by product line (parallel)
35. Auto quotation create/save tests
36. Travel quotation create/save tests
37. PA quotation create/save tests
38. PL quotation create/save tests
39. HS quotation create/save tests
40. Quotation PDF generation smoke (one line)
41. Quotation validation edge cases (required fields)
42. Quotation search/list filter tests

BATCH 8 — Policy & endorsement (parallel)
43. Policy issuance from quotation tests
44. Policy search/list + status filter tests
45. Policy PDF certificate smoke (travel + auto samples)
46. Policy register/invoice PDF smoke
47. Endorsement create/approve flow tests
48. Travel endorsement refund decimal validation (regression for #537)
49. Auto endorsement update tests (branch 108)
50. Policy wording default version tests (#540)

BATCH 9 — Claim & payment (parallel)
51. Claim register CRUD tests
52. Claim process workflow tests
53. Claim recovery + partial payment tests
54. Claim report export tests (#4)
55. Cause of loss / accident LOV tests
56. Service provider + third party tests
57. PaymentCollection list + filter tests
58. Payee + adjuster company tests

BATCH 10 — Renewal & reinsurance (parallel)
59. Renewal vehicle list/upload tests
60. SurchargeRule CRUD tests
61. Reinsurance partner + group tests
62. Reinsurance config + type tests
63. Reinsurance data export smoke
64. Cover maintenance tests

BATCH 11 — PDF & document regression (parallel)
65. Quotation PDF template regression (travel v1)
66. Policy certificate template regression
67. Claim register PDF smoke
68. Renewal notice PDF smoke
69. Excel export smoke (Maatwebsite)
70. QR code generation smoke (chillerlan)

BATCH 12 — API & integration (parallel — defer if over budget)
71. routes/api.php audit + Sanctum readiness doc
72. Database-to-API slice: Customer read API
73. Database-to-API slice: Product LOV API
74. Database-to-API slice: Policy summary API
75. Webhook/outbound event stub for policy issued
76. Calendar .ics export stub (if route exists)

BATCH 13 — UX & i18n (parallel — first to cut)
77. Khmer datetime helper usage audit
78. Translation key gap scan (lang/)
79. Responsive layout spot-check doc (mobile)
80. Toast/error message consistency pass
81. PrimeVue table pagination standardization doc
82. Empty state components for top 5 list views

BATCH 14 — Performance (parallel)
83. N+1 audit on Policy list endpoint + fix
84. N+1 audit on Claim list endpoint + fix
85. DB index recommendations doc for hot filters
86. Cache strategy for LOV endpoints
87. Queue offload for PDF generation jobs

BATCH 15 — Security & QA (parallel — must ship)
88. Mass-assignment review on top 10 models
89. File upload type/size validation hardening
90. CSRF/XSS review on comment/rich-text fields (CKEditor/Quill)
91. Authorization re-audit across Claim + Policy controllers
92. security-review subagent on full diff
93. Cross-role permission matrix test (admin vs member)

BATCH 16 — Docs & handoff (parallel)
94. README: setup, Keycloak, private package access
95. docs/NEXT_SESSION.md with open UAT defects
96. Update examples/pgi-core-frontend-uat/docs/ROADMAP.md batch checkboxes
97. Production cutover checklist (env, migrations, rollback)

---
EXECUTION NOTES:
- Batch 1 blocks everything. Batch 3 blocks all feature test batches.
- Max ~10 parallel agents per wave; reuse slots across waves.
- If hour 6 and still in Batch 7, skip Batches 12–13 entirely.
- MVP = Batches 1–11 + 14–16 (tests + security + docs).
- After completion: update docs/SESSION_STATE.md with pgi-core-frontend-uat entry.
- Run from repo root with: export PATH="/d/laravel13.x/bin:$PATH"
```

---

## 3-month ↔ 10-hour mapping

| Calendar month | Parallel batches | Approx. wall-clock (10 agents/wave) |
|----------------|------------------|-------------------------------------|
| Month 1 | 1–4 | ~2.5 h |
| Month 2 | 5–9 (+12 partial) | ~4 h |
| Month 3 | 10–11, 14–16 | ~3.5 h |

Total **~10 h** assumes agents finish slices in parallel and integrator only merges + runs tests between batches.

---

## Notes

- **UAT vs master:** Local folder `pgi-core-frontend(uat)` in your prompt = branch **`uat`** on `pgi-core-frontend` (not a separate repo name).
- **Laravel 12 on UAT:** Upgrade work is largely done on `uat`; focus is tests, API boundaries, and cutover — not greenfield Laravel 13 scaffold.
- **97 agents ≠ 97 concurrent:** Same model as `docs/prompts/task-portal-97-parallel-agents-prompt.md` — 97 slices, ~16 batch waves.
