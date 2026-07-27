# Prompt — Product reject / approval journey + PDF

Copy into Cursor when you need the same investigation + docs pack as Auto endorsement.

---

## Full prompt

```text
You are working in pgi-core-frontend (Laravel + Vue, Windows Herd + Git Bash).

Use: /spec-kit-openspec-superpowers · caveman voice · Quick triage for docs/investigate (no code change unless I ask). Skip agent-browser unless UI proof is needed.

Task: Map FULL user journey for {PRODUCT} {MODULE} approve/reject (and related lifecycle).

### Scope fill-in
- Product: {Auto | HS | PA | Travel | PL Direct Book …}
- Module: {Quotation | Endorsement | Policy | Claim …}
- List URL: {e.g. /endorsements/auto}
- Detail URL: {e.g. /endorsements/auto/:id}

### Deliverables (all required)
1) Journey map — ASCII flow create/generate → edit → submit → approve/reject → revise/delete/print/next.
2) Step table — when allowed, API, controller, exact table + columns.
3) Approve/Reject gate — every FE + BE condition; for each: how checked, table/column or API.
4) Payload trap — FE JSON keys vs DB column names.
5) Status matrix — status / approved_status / accepted_status vs user can do.
6) Mermaid sequences: happy path, reject, revise/delete/accept if exists.
7) Verify SQL — SELECT after reject; optional gate checks.
8) vs sibling module — short compare if same product has both.
9) Save `docs/diagrams/{slug}-journey.md` (append shared reject doc if present).
10) PDF at `docs/diagrams/{slug}-journey.pdf` via HTML + Mermaid CDN → Puppeteer (A4). Keep .md + .html + `_render-*.cjs`.
11) Sync reading backup → `D:\laravel13.x\docs\pgi-core-frontend\diagrams\` (+ this prompt folder if new). Commit + push laravel13.x main unless I say "pgi only".
12) Grep code only — no invented schema. Next 5. No pgi commit unless asked.

### Investigation order
1. Router → Vue Detail/Index/Form (canApproveCond, Submit, Revise, Delete)
2. Routes → Controller approve/accept/revise/destroy/submit
3. Model $table + status columns
4. Gate helpers (isReinsuranceCompleted, isPolicyConfigurationCompleted, …)
5. SP names if generate/cancel uses them

### Style reference
- `docs/pgi-core-frontend/diagrams/auto-endorsement-journey.*` (this hub)
- or pgi: `docs/diagrams/auto-endorsement-journey.*`
```

---

## Short variant

```text
Map full {PRODUCT} {MODULE} user journey for approve/reject in pgi-core-frontend.

Need: ASCII journey, step→API→table/column, FE+BE gates with exact cols, payload vs DB traps, status matrix, Mermaid (happy + reject + revise/delete), verify SQL, save docs/diagrams/{slug}-journey.md + PDF (HTML+Mermaid→Puppeteer), sync to D:\laravel13.x\docs\pgi-core-frontend\diagrams\ and push laravel13.x unless "pgi only". Grep only. Caveman + Next 5. No pgi commit.
```

---

## Slug examples

| Scope | Slug |
|-------|------|
| Auto quotation reject | `auto-quotation-reject` |
| HS quotation reject | `hs-quotation-reject` |
| Auto endorsement journey | `auto-endorsement-journey` |
| HS endorsement journey | `hs-endorsement-journey` |
