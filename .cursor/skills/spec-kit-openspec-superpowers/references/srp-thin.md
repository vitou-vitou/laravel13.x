# SRP + thin modules (avoid fat)

Load on **Phase 4 implement** + **G4** with [simple-code-voice.md](simple-code-voice.md).  
Deep Fowler rules: skill `refactor` → `struct-single-responsibility` · `struct-extract-method` · `struct-extract-class` (`~/.agents/skills/refactor/`).

## Rule

| Unit | One job |
|------|---------|
| Function | One reason to change |
| File / Vue SFC slice | One concern (leave / print / hydrate — not all) |
| Shared shell | Narrow gate + delegate — no fat shared methods |

## Do

- Split before grow: new BUR/DB helper file > balloon shared `Detail.vue` / legacy service
- Extract method when handler does toast + mutate + route + error soup
- Prefer `vendor` / `node_modules` / existing helper over new util
- Gate Direct Book with `isBurglaryProductCode` / `isBur` — leave legacy path alone

## Don't

| Fat smell | Prefer |
|-----------|--------|
| God `handleSubmit*` with 5 concerns | Small steps or extract |
| Guess chains `a ?? b ?? c` | One real field from API contract |
| “While here” polish in same PR | Out of must (`14-code-must-benefit`) |
| Fat legacy edit for DB-only | New slice under `Burglary/` (`12-claude-task-bur-slice`) |

## G4 checklist (SRP)

- [ ] New/changed function = one job; name ≤ ~3 words
- [ ] Shared file diff = thin branch only (or new slice file)
- [ ] No speculative fallbacks / extra error trees
- [ ] Reuse before invent (`AGENTS.md` · vendor/node_modules)

## Triggers

User says `SRP` / `avoid fat` / `thin` / `single responsibility` → read this + simple-code-voice before coding.
