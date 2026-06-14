# PGI Agency Portal — 97-task / 3-month / ~10-hour roadmap

> **Source:** Clone of `phillipinsurancekh/pgi-agency-portal` **uat** branch  
> **Example path:** `examples/pgi-agency-portal`  
> **Upstream:** `git@github.com:phillipinsurancekh/pgi-agency-portal.git` (branch `uat`)

---

## Project pick

From the **180+ Buildable Project Types** catalog (insurance vertical):

> **Insurance agency partner portal** — Keycloak SSO; multi-entity (PLB/HTB/PGI); Filament panels (Login, Agent, Partners); API-driven product lines (Auto, EV, MTC, Fire, Home, Building, PA, Travel, Medical); KHQR payment; OCR KYC; e-policy cover notes.

**Existing maturity (UAT):** Auto + Fire well tested (~80% of PHPUnit); Home/Building/PA/Travel wizards exist but untested; Agent H&S panel mature; Admin panel not built; Staff/Referral APIs permanently mocked.

**Stack:** Laravel 12, Filament 5, Keycloak Socialite, KHQR (PaymentQr + webhook), CamDX OCR, Reverb, Spatie Data, Pest/PHPUnit, `pgi/cambodia-address` (PUMI).

---

## 3-month roadmap (12 weeks)

| Phase | Weeks | Outcome |
|-------|-------|---------|
| **A — Clone & foundation** | 1–2 | Herd link, schema debt fix, CI, architecture README |
| **B — Auth & access** | 3 | Keycloak hardening, role cleanup, access-request E2E |
| **C — Payment core** | 4 | KHQR idempotency, webhook tests all lines, Reverb polish |
| **D — Auto / EV / MTC** | 5 | Regression suite, CIF real-mode, promo parity |
| **E — Fire** | 6 | Promo code ship, occupation validation, API sign-off |
| **F — Home & Building** | 7 | Feature tests quote→pay→e-policy, wizard dedupe |
| **G — PA & Travel** | 8 | Split/clarify flows, passport OCR tests, localization |
| **H — Agent panel** | 9 | NewSale/PolicyEdit tests, policy mgmt contracts |
| **I — OCR & e-policy** | 10 | CamDX contract tests, public download security audit |
| **J — Bank integrations** | 11 | Real Staff/Referral lookup, HTB entity wiring |
| **K — Admin & ship** | 12 | Admin panel MVP, Dusk smoke, UAT checklist, docs |

**Calendar:** 12 weeks if built sequentially.  
**Claude Code:** same 97 tasks in **10 waves × ~1 hour** (4–5 Agent Team mates or Dynamic Workflow batches).

---

## Domain invariants (never violate)

- Policy data is **source of truth in OIS API** — portal never invents policy numbers
- Payment marked **paid only via KHQR webhook** (never success-page redirect alone)
- **Entity context** (PLB/HTB/PGI) must match gateway credentials and CIF lookup mode
- **Vendor/partner isolation** — Partners panel users cannot access Agent-only Medical sale unless role allows
- **OrderLine / premium snapshot** immutable after policy bound
- **E-policy download tokens** expire per `EPOLICY_DOWNLOAD_LINK_TTL_DAYS`
- **OCR fields** are advisory — user must confirm before submit
- **Keycloak `sub`** is the only user identifier (no local `users` table)
- **Mock services** only in `APP_ENV=testing` or explicit `mock` bindings — never in production
- **AccessRequest** must not FK to dropped `users` table on fresh migrate

---

## 97-task assignment portal

Columns: **ID | Slice | Agent role | Week | Deps | Branch | Definition of done**

### Weeks 1–2 — Foundation (T001–T020)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T001 | Clone to `examples/pgi-agency-portal` + Herd link | bootstrap | 1 | — | `feat/T001-clone` | App loads at `pgi-agency-portal.test` |
| T002 | Local `.env` matrix (mock APIs, sqlite) | devops | 1 | T001 | `feat/T002-env` | `artisan test` runs without live keys |
| T003 | Fix `access_requests.user_id` FK after users drop | backend | 1 | T001 | `feat/T003-migration-fix` | Fresh `migrate:fresh` green |
| T004 | Remove or archive legacy Product/Plan/Application models | backend | 1 | T003 | `feat/T004-legacy-cleanup` | No dead model references |
| T005 | Architecture README + panel map | docs | 1 | T001 | `feat/T005-arch-readme` | Panels, services, flows documented |
| T006 | Env/deployment matrix doc | docs | 1 | T002 | `feat/T006-env-doc` | Keycloak, gateway, OCR vars listed |
| T007 | PHPUnit baseline green in clone | tester | 1 | T002 | `feat/T007-baseline-tests` | All existing tests pass |
| T008 | GitHub Actions CI (Pint + PHPUnit mock) | devops | 2 | T007 | `feat/T008-ci` | CI green on push |
| T009 | Pint + strict types audit pass | backend | 2 | T007 | `feat/T009-pint` | `./vendor/bin/pint --test` clean |
| T010 | Service binding inventory test | tester | 2 | T002 | `feat/T010-bindings-test` | Mock/real bindings asserted in testing |
| T011 | `config/entities.php` validation | backend | 2 | T001 | `feat/T011-entities` | PLB/HTB/PGI capabilities documented |
| T012 | `config/roles.php` → panel routing test | tester | 2 | T001 | `feat/T012-role-routing` | RoleRoutingService unit tests |
| T013 | Dev impersonation guard (non-prod only) | backend | 2 | T002 | `feat/T013-impersonation` | Prod blocks impersonation |
| T014 | Telescope gated to local | devops | 2 | T001 | `feat/T014-telescope` | Not registered in prod |
| T015 | OpenAPI docs linked from README | docs | 2 | T005 | `feat/T015-openapi-link` | `docs/openapi/*` indexed |
| T016 | `docs/NEXT_SESSION.md` handoff stub | docs | 2 | T005 | `feat/T016-next-session` | Resume file for agents |
| T017 | `./bin/verify-example pgi-agency-portal` script hook | devops | 2 | T007 | `feat/T017-verify` | Verify script passes |
| T018 | Session driver file for local Herd | devops | 2 | T002 | `feat/T018-session-file` | No redis dependency locally |
| T019 | Queue sync + cache file for local | devops | 2 | T002 | `feat/T019-queue-cache` | `.env.example` Herd-safe defaults |
| T020 | Database seeder for AccessRequest demo | backend | 2 | T003 | `feat/T020-seed-access` | Factory + seeder for agent tests |

### Week 3 — Auth & access (T021–T028)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T021 | Keycloak JWT validation edge-case tests | tester | 3 | T012 | `feat/T021-jwt-tests` | Expired/invalid token handled |
| T022 | KeycloakTokenService refresh tests | tester | 3 | T021 | `feat/T022-token-refresh` | Refresh before expiry |
| T023 | Dedicated `partner:*` role mapping | backend | 3 | T012 | `feat/T023-partner-roles` | Legacy `agency:*` deprecated note |
| T024 | UnauthorizedPage + AccessRequest flow test | tester | 3 | T020 | `feat/T024-unauthorized-e2e` | Request → email → agent view |
| T025 | AccessRequestFollowUp mail test | tester | 3 | T024 | `feat/T025-access-mail` | Mailable renders |
| T026 | EntityContextService domain detection tests | tester | 3 | T011 | `feat/T026-entity-context` | PLB email → plb entity |
| T027 | SsoRouter redirect by role test | tester | 3 | T012 | `feat/T027-sso-router` | Agent vs Partners landing |
| T028 | AUTH-003 token expiry banner UX | frontend | 3 | T022 | `feat/T028-auth-banner` | User sees re-login prompt |

### Week 4 — Payment core (T029–T037)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T029 | KHQR webhook idempotency handler | backend | 4 | T007 | `feat/T029-webhook-idempotent` | Duplicate IPN ignored safely |
| T030 | Webhook replay + logging | backend | 4 | T029 | `feat/T030-webhook-log` | Structured log per event |
| T031 | PaymentService confirm state machine test | tester | 4 | T007 | `feat/T031-payment-sm` | All transitions covered |
| T032 | HandlesKhqrPayment trait — Fire path test | tester | 4 | T031 | `feat/T032-khqr-fire` | Fire sale payment smoke |
| T033 | HandlesKhqrPayment — Home path test | tester | 4 | T031 | `feat/T033-khqr-home` | Home sale payment smoke |
| T034 | HandlesKhqrPayment — Building path test | tester | 4 | T031 | `feat/T034-khqr-building` | Building payment smoke |
| T035 | HandlesKhqrPayment — PA path test | tester | 4 | T031 | `feat/T035-khqr-pa` | PA sale payment smoke |
| T036 | Reverb broadcast payment status test | tester | 4 | T031 | `feat/T036-reverb-pay` | Event fires on webhook |
| T037 | Payment failure UX (timeout, expired QR) | frontend | 4 | T031 | `feat/T037-pay-failure-ux` | User can retry payment |

### Week 5 — Auto / EV / MTC (T038–T045)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T038 | AutoSale full flow regression audit | tester | 5 | T007 | `feat/T038-auto-regression` | Existing 8 auto tests still green |
| T039 | EV product line edge cases | tester | 5 | T038 | `feat/T039-ev-cases` | EV-specific quote test |
| T040 | MTC motorcycle regression expand | tester | 5 | T038 | `feat/T040-mtc-expand` | MTC wizard steps covered |
| T041 | CIF real-mode integration test (gated) | tester | 5 | T026 | `feat/T041-cif-real` | Skipped unless env set |
| T042 | CIF mock-mode default in CI | backend | 5 | T041 | `feat/T042-cif-mock-ci` | CI uses mock CIF |
| T043 | Vehicle manual entry + API fallback doc | docs | 5 | T038 | `feat/T043-vehicle-doc` | Documented in README |
| T044 | Auto promo code field parity check | backend | 5 | T038 | `feat/T044-auto-promo` | Promo wired if API supports |
| T045 | AutoSale step schema regression guard | tester | 5 | T038 | `feat/T045-schema-regression` | StepSchema test extended |

### Week 6 — Fire (T046–T052)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T046 | Fire promo code UI (per superpowers spec) | frontend | 6 | T007 | `feat/T046-fire-promo-ui` | Promo partial renders |
| T047 | Fire promo applied to quote API payload | backend | 6 | T046 | `feat/T047-fire-promo-api` | Payload test with promo |
| T048 | FireRiskData validation tests expand | tester | 6 | T007 | `feat/T048-fire-risk` | Risk data edge cases |
| T049 | Fire occupation config validation | backend | 6 | T048 | `feat/T049-fire-occupation` | Invalid occupation rejected |
| T050 | FireSale full flow feature test | tester | 6 | T032,T047 | `feat/T050-fire-full` | Quote → policy → pay |
| T051 | MockFireService contract parity | tester | 6 | T048 | `feat/T051-mock-fire` | Mock matches interface |
| T052 | Fire UAT API sign-off checklist | docs | 6 | T050 | `feat/T052-fire-uat` | Checklist in docs |

### Week 7 — Home & Building (T053–T059)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T053 | HomeSale quote → policy feature test | tester | 7 | T033 | `feat/T053-home-flow` | Full wizard test |
| T054 | BuildingSale quote → policy feature test | tester | 7 | T034 | `feat/T054-building-flow` | Full wizard test |
| T055 | HomePackageService payload tests | tester | 7 | T053 | `feat/T055-home-payload` | API payload asserted |
| T056 | Shared property wizard trait extract | backend | 7 | T053,T054 | `feat/T056-property-trait` | Dedupe Home/Building |
| T057 | PUMI address edge-case tests | tester | 7 | T007 | `feat/T057-pumi-edge` | Invalid commune handled |
| T058 | Home/Building OCR step tests | tester | 7 | T053 | `feat/T058-property-ocr` | NID capture mocked |
| T059 | Home/Building e-policy after pay | tester | 7 | T053,T054 | `feat/T059-property-epolicy` | Cover note link works |

### Week 8 — PA & Travel (T060–T066)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T060 | PaSale PA-only flow feature test | tester | 8 | T035 | `feat/T060-pa-flow` | PA quote → policy |
| T061 | PaSale Travel flow feature test | tester | 8 | T035 | `feat/T061-travel-flow` | Travel-specific fields |
| T062 | Travel destination/duration payload test | tester | 8 | T061 | `feat/T062-travel-payload` | QuotationService travel |
| T063 | Passport OCR mock test | tester | 8 | T061 | `feat/T063-passport-ocr` | OCR response mapped |
| T064 | EN/KM lang keys for PA/Travel | frontend | 8 | T060 | `feat/T064-pa-i18n` | `lang/km/partners.php` complete |
| T065 | ProductSelection routing for all lines | tester | 8 | T001 | `feat/T065-product-routing` | Each line reachable |
| T066 | Medical line decision ADR | docs | 8 | T065 | `feat/T066-medical-adr` | Agent-only vs Partners doc |

### Week 9 — Agent panel (T067–T073)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T067 | Agent NewSale smoke test | tester | 9 | T024 | `feat/T067-agent-newsale` | Medical wizard starts |
| T068 | Agent PolicyManagement table test | tester | 9 | T067 | `feat/T068-policy-mgmt` | List loads with mock API |
| T069 | Agent PolicyEdit amendment test | tester | 9 | T068 | `feat/T069-policy-edit` | Edit form validates |
| T070 | Agent PolicyView payment test | tester | 9 | T031 | `feat/T070-agent-pay` | KHQR on agent policy view |
| T071 | Agent CoverNote PDF preview test | tester | 9 | T070 | `feat/T071-agent-cover` | DocsService mocked |
| T072 | AccessRequestResource agent actions | tester | 9 | T024 | `feat/T072-access-resource` | Approve/reject flows |
| T073 | Agent vs Partners isolation test | tester | 9 | T023 | `feat/T073-panel-isolation` | Cross-panel 403 |

### Week 10 — OCR & e-policy (T074–T080)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T074 | OCRService NID response mapper test | tester | 10 | T007 | `feat/T074-ocr-nid` | Fields mapped correctly |
| T075 | OCRService failure/retry UX | frontend | 10 | T074 | `feat/T075-ocr-retry` | User can retry scan |
| T076 | DocsService per-line endpoint test | tester | 10 | T007 | `feat/T076-docs-service` | All InsuranceType lines |
| T077 | EPolicyService router test | tester | 10 | T076 | `feat/T077-epolicy-router` | Correct method per line |
| T078 | PublicEPolicyDownloadController security | tester | 10 | T077 | `feat/T078-public-download` | Expired/invalid token 403 |
| T079 | CoverNote Partners page test | tester | 10 | T077 | `feat/T079-cover-note-page` | Preview renders |
| T080 | E-policy TTL + audit log | backend | 10 | T078 | `feat/T080-epolicy-audit` | Download attempts logged |

### Week 11 — Bank integrations (T081–T088)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T081 | Real StaffLookupService impl | backend | 11 | T007 | `feat/T081-staff-real` | Per `bank-partner-api.yaml` |
| T082 | Real ReferralLookupService impl | backend | 11 | T081 | `feat/T082-referral-real` | Per OpenAPI spec |
| T083 | Staff/Referral contract tests | tester | 11 | T081,T082 | `feat/T083-bank-contract` | Payload/response asserted |
| T084 | ApiCifLookupService HTB entity path | backend | 11 | T026 | `feat/T084-htb-cif` | HTB config wired or documented out-of-scope |
| T085 | EntityServiceResolver per-entity override test | tester | 11 | T011 | `feat/T085-entity-resolver` | PLB vs generic services |
| T086 | ApiGatewayService token cache test | tester | 11 | T026 | `feat/T086-gateway-token` | OAuth client-credentials |
| T087 | PLB UAT integration checklist | docs | 11 | T083 | `feat/T087-plb-uat` | Sign-off doc |
| T088 | HTB readiness matrix | docs | 11 | T084 | `feat/T088-htb-matrix` | Blockers listed |

### Week 12 — Admin, ops & ship (T089–T097)

| ID | Slice | Role | Wk | Deps | Branch | DoD |
|----|-------|------|-----|------|--------|-----|
| T089 | Admin Filament panel scaffold | admin | 12 | T023 | `feat/T089-admin-panel` | `agency:admin` can login |
| T090 | Admin vendor/agency list (read-only) | admin | 12 | T089 | `feat/T090-admin-list` | Access requests + audit view |
| T091 | Dusk smoke: SSO → Auto sale → mock pay | tester | 12 | T038,T031 | `feat/T091-dusk-smoke` | Critical path browser test |
| T092 | Production secrets rotation guide | docs | 12 | T006 | `feat/T092-secrets-guide` | Keycloak + gateway rotation |
| T093 | Monitoring alerts for webhook/OCR | devops | 12 | T030,T075 | `feat/T093-monitoring` | Alert rules documented |
| T094 | Full PHPUnit suite green | lead | 12 | T050–T073 | `feat/T094-full-tests` | 150+ tests target |
| T095 | N+1 audit Filament tables | perf | 12 | T068 | `feat/T095-nplus1` | Eager loads on policy lists |
| T096 | Security headers + CSP review | backend | 12 | T001 | `feat/T096-security-headers` | Headers on web responses |
| T097 | Ship checklist + tag release notes | lead | 12 | T094,T091 | `feat/T097-ship` | UAT → prod checklist complete |

**Total: 97 tasks.**

---

## ~10 hour Claude Code execution plan

**Not** 97 simultaneous agents — **10 waves**, ~10 tasks each, **4–5 teammates** (or Dynamic Workflow ~16 concurrent on independent slices).

| Hour | Wave | Task IDs | Teammate focus |
|------|------|----------|----------------|
| H1 | 1 | T001–T010 | clone setup, migrations, CI baseline |
| H2 | 2 | T011–T020 | config, docs, verify script, seed |
| H3 | 3 | T021–T028 | Keycloak, roles, access request |
| H4 | 4 | T029–T037 | KHQR webhook + all-line payment tests |
| H5 | 5 | T038–T045 | Auto/EV/MTC regression |
| H6 | 6 | T046–T052 | Fire + promo |
| H7 | 7 | T053–T059 | Home + Building |
| H8 | 8 | T060–T066 | PA + Travel + i18n |
| H9 | 9 | T067–T080 | Agent panel + OCR + e-policy |
| H10 | 10 | T081–T097 | Bank APIs, admin, Dusk, ship |

### Wave rules

1. Lead owns this task board; marks `blocked` / `ready` / `done`.
2. One git worktree per active task (`feat/T0xx-*`).
3. No two agents touch the same Filament page file concurrently.
4. Lead runs `artisan test` + merge after each wave.
5. Wave N+1 starts only when wave N tests are green.
6. **Never commit live Keycloak/gateway secrets** — use `.env.example` placeholders only.

### Claude Code setup

Enable Agent Teams in `settings.json`:

```json
{
  "env": {
    "CLAUDE_CODE_EXPERIMENTAL_AGENT_TEAMS": "1"
  }
}
```

---

## Zero-miss roadmap generation (use before rewriting tasks)

Full reusable prompt: **`docs/ZERO-MISS-97-TASK-ROADMAP-PROMPT.md`**

Quick re-audit prompt:

```markdown
Follow docs/ZERO-MISS-97-TASK-ROADMAP-PROMPT.md Phases 0–6.
Audit examples/pgi-agency-portal (uat). Print coverage matrix (every panel, product line, service, webhook, Livewire, mail, i18n, docker, upstream branch).
Map each row → T001–T097 or OUT OF SCOPE. Rewrite this file. No "build" tasks for features that already exist — use [verify].
```

**Known gaps in v1 roadmap (fix in v2):** Vehicle QR, DocumentUpload, payment email notify, Reverb prod, Docker deploy, full KM i18n, Medical Partners, upstream sync; T046–T047 promo → verify only.

---

## Claude Code opener (paste to start build)

```markdown
Harden and complete PGI Agency Portal (examples/pgi-agency-portal, uat clone).

Read roadmap: docs/pgi-agency-portal-97-task-roadmap.md

Enable Agent Teams. Create shared task board with all rows T001–T097.

Project rules:
- Keycloak sub is sole user id; payment paid only via KHQR webhook
- Entity context (PLB/HTB/PGI) drives gateway + CIF
- Mock services in testing/CI only; PaymentQr/Payment always real bindings
- Pest/PHPUnit for every new flow; no live API keys in CI
- Filament 5 panels: Login (/pgi-sso), Agent (/agent), Partners (/partners)

Execute in 10 waves (~1 hour each, max 5 teammates):
Wave 1: T001–T010 … Wave 10: T081–T097

Each teammate: claim one ready task → worktree branch → implement DoD → tests green → mark done.

Start Wave 1 now. After T010, run full test suite and report board status before Wave 2.
```

For massive fan-out after the board exists:

```markdown
Run as a dynamic workflow: implement each ready row in parallel with reviewer verification.
Do not stop until all 97 rows are Done or explicitly blocked.
```

---

## Realistic scope note

In **~10 hours of agent time**, expect **Waves 1–7 complete** (foundation through property lines) if merges stay clean. **Waves 8–10** (PA/Travel, Agent, bank APIs, Admin, Dusk) may need an **11th hour** or follow-up session — especially real Staff/Referral API wiring (T081–T083) which needs live credentials.

The **12-week** calendar is the product/UAT planning horizon; the **10-hour** plan is the parallel Claude Code build horizon.

---

## Local setup (clone already done)

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd examples/pgi-agency-portal
composer install
cp .env.example .env   # if missing
./../../bin/fix-example-app-key pgi-agency-portal
herd link pgi-agency-portal --update-env
php artisan migrate
php artisan test
```

**App URL:** http://pgi-agency-portal.test  
**SSO entry:** http://pgi-agency-portal.test/pgi-sso/login  
**Resume file:** `examples/pgi-agency-portal/docs/NEXT_SESSION.md`

---

## Build status

| Waves | Tasks | Status |
|-------|-------|--------|
| — | T001–T097 | **Not started** — UAT clone imported 2026-06-14 |

**Upstream sync:** `git remote add upstream git@github.com:phillipinsurancekh/pgi-agency-portal.git` (already present from clone)
