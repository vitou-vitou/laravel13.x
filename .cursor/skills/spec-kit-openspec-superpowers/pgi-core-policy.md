# pgi-core-frontend — Spec-Kit / OpenSpec / Superpowers policy

**Repo:** `pgi-core-frontend` (Property Liability admin UI)  
**Handoff:** `docs/SESSION_STATE.md` (read on `continue`)  
**Active OpenSpec:** `openspec/changes/phase-ii-quotation-slice-only/`

## Locked decisions

| Phase | Tool | Rule |
|-------|------|------|
| Greenfield feature | **OpenSpec** + **Superpowers** (+ **Caveman** optional) | `/opsx:propose` or `/super-spec` → G1 → apply → archive |
| Small fix / typo | **Quick** path in orchestrator | Simplified propose → TDD → implement |
| Never | Spec-Kit **and** OpenSpec on same feature | Pick one SDD layer |
| Optional | **Caveman** | Voice / token compression — `/caveman`, `normal mode` to off |
| PL UI work | **impeccable** + Direct Book shell patterns | `01-impeccable-ui.mdc`, `DirectBookQuotationFormShell.vue` |
| **Every session** | **Default stack** | `spec-kit-openspec-superpowers` — treat as ON (99%); no need to `@` skill each chat |
| **Every session** | **Claude Senior listener** | Claude heavy work; Cursor thin apply+verify — save Cursor tokens (`08-claude-senior-listener.mdc`, skill `references/claude-senior-listener.md`) |
| UI / G4 browser proof | **agent-browser** (when needed) | Load `agent-browser` skill; smoke `APP_URL`; skip pure PHP/API/docs |

## Product scope (Property Liability)

**In scope — 7 Direct Book products only** (`ProductCode::directBookCodes()`):

| SM code | Constant |
|---------|----------|
| `0189` | `MARINE_CARGO` |
| `0191` | `BURGLARY` |
| `0192` | `MONEY_INSURANCE` |
| `0193` | `PLATE_GLASS` |
| `0194` | `CONSTRUCTION_ALL_RISKS` |
| `0195` | `BOND` |
| `0196` | `PROFESSIONAL_INDEMNITY` |

**Out of scope** — do not refactor `0121`–`0125` (Public Liability, Fire, Property, Home Package, Business Package).

See `.cursor/rules/02-pl-seven-product-scope.mdc` for paths and execution checklist.

## Superpowers skills to prefer during implement

1. `test-driven-development` — before production code  
2. `verification-before-completion` — before claiming done  
3. `systematic-debugging` — on test failures  
4. `requesting-code-review` — after major steps  
5. `subagent-driven-development` — parallel independent tasks (prefer **Claude** model when Senior listener ON)  
6. `agent-browser` — when UI/page/dropdown/form must be proven in a real browser  

**Optional:** **caveman** — terse voice; **cavecrew** subagents for compressed investigator/builder/reviewer.

## Claude Senior + agent-browser (session)

Default **ON** every session under `/super-spec` / this skill:

- Cursor does not re-spec Claude’s settled plan; short replies; apply → verify → done.
- Opt out: `normal cursor` / `you do it` / `cursor lead`.
- UI change → browser smoke via agent-browser (or IDE browser MCP if Chrome CDP fails).
- Full procedure: [references/claude-senior-listener.md](references/claude-senior-listener.md).

## Simple code + plain voice (locked)

During **Phase 4** and **G4**, always:

- **Talk:** short, plain, conclusion-first — anyone can understand (`caveman-mode.mdc`; `normal mode` to off).
- **Code:** small methods; **short names** (`syncCommission`, `patchForm`, `plCommRate` — not long compound names).
- **Comments:** no `/** ... */` on obvious helpers; code should read clear without essays.

Full guide: [references/simple-code-voice.md](references/simple-code-voice.md) · rule: `.cursor/rules/04-simple-code-voice.mdc`

## Zero edge-case confirm (locked)

After **file renames**, **module moves**, or **import-path** changes (e.g. `burglary/on.js` → `scope.js`), before claiming done:

1. Grep: no stale old paths/symbols in code + scripts.
2. Disk: new files present; old files gone; barrel `index.js` updated.
3. Docs/rules: update `05-pl-db-naming.mdc` (or equivalent) folder maps — no deleted filenames.
4. `npm run build` (and `node scripts/verify-burglary-routing.mjs` when PL routing touched).
5. Reply: **Confirmed — runtime edge cases: zero** (checklist table) **or** list leftovers.

Detail: skill `references/quality-gates.md` → *Zero edge-case confirm*.

## PHP / frontend (Windows Herd)

```bash
export PATH="/c/Users/PGI/.config/herd/bin:$PATH"
# or: /c/Users/PGI/.config/herd/bin/php.bat artisan test

cd /d/vitou/projects/pgi-core-frontend
php artisan test
npm run build
node scripts/verify-burglary-routing.mjs
```

Site URL: check `.env` `APP_URL` (e.g. `http://pgi-core-frontend.test` via Herd).

## Git

- **Do not** auto-commit or auto-push unless user explicitly asks (see `99-god-speed-session.mdc`).
- Match recent Conventional Commits style when user requests a commit.

## UI phase (Direct Book forms)

When polishing quotation/policy Vue:

1. Read **impeccable** skill + `PRODUCT.md` if present  
2. Extend `DirectBookQuotationFormShell.vue` vocabulary (PrimeVue + Tailwind, `slate-50` sections)  
3. `npm run build` before claiming UI done  

## Study docs (optional)

After major features: **system-study-packet** → project docs under `openspec/` or `docs/`.
