# AFK default + LDA-first (locked)

**Status:** default ON for pgi-core-frontend / `/super-spec`.  
**Proven:** Endorsement / Exclusion / Automatic Ext `+`; location_of_risk FE↔PAI align (2026-08).

## AFK (unless opted out)

| Step | Behavior |
|------|----------|
| Mode | Auto from `.spec-mode` / `openspec/` (OpenSpec) |
| Complexity | Auto Quick/Standard; one-line lock; no debate |
| G1 | **Auto-approved** — no wait for "confirm" / "go" |
| Implement | Write OpenSpec Quick artifacts + code without blocking |
| Done | Verify (gate script / Playwright / build) + LDA one-liner |

Opt out: `no afk` · `ask me` · `gate G1` · `interactive` · `plain agent`

## LDA-first (every task)

Order matters — sketch before code:

1. **Logic** — rules (must / nice / fail paths)
2. **Data structure** — form fields, LOV keys, API payload, persistence
3. **Architecture** — UI → portal helper → service/API (reuse; no second gate)
4. **Portal** — shared module path (or none + why)
5. **Others** — scope, verify, risks

Quick = compress to 5 bullets. Full template: [solve-plan-pattern.md](solve-plan-pattern.md).

## Working recipe (reuse)

```
OpenSpec Quick + LDA compress
→ one portal file (gate / normalize / options)
→ TDD or node assert
→ Playwright when UI create/appear
→ progress.md PASS + zero edge-case
```

## Wire locations

| Layer | Path |
|-------|------|
| Repo rule | `.cursor/rules/09-afk-lda-default.mdc` |
| User rule | `~/.cursor/rules/afk-lda-default.mdc` |
| Skill | `SKILL.md` Session defaults · `pgi-core-policy.md` |
