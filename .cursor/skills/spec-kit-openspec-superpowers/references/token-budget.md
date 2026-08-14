# Token budget — saving pattern (locked)

**Triggers:** `save tokens` · `token budget` · `cheap mode` · `less tokens` · `be brief` · `/caveman`

**Goal:** Same gates, fewer tokens. Levers already exist across this skill — this file is the single index so no session has to re-derive them.

**Rule of thumb:** cut **input** first (what you load), then **routing** (who thinks), then **output** (how you talk). Input is the biggest and quietest cost.

---

## The five levers

| # | Lever | Cuts | Where it lives |
|---|-------|------|----------------|
| 1 | **Progressive disclosure** — load `SKILL.md` only; read a `references/*.md` on demand | Input | this dir |
| 2 | **Claude Senior listener** — Claude does heavy thinking; Cursor applies + verifies | Routing | [claude-senior-listener.md](claude-senior-listener.md) |
| 3 | **Size gate** — tiny Quick (1 file, <~20 lines) → solo, no `Task` spawn | Routing | [claude-senior-listener.md](claude-senior-listener.md) |
| 4 | **`load index`** — orient read-only, stop, wait for the task | Input | [load-index.md](load-index.md) |
| 5 | **Caveman voice** — terse prose; code/commits/docs stay normal | Output | `.cursor/rules/caveman-mode.mdc` · skill `caveman-spec-triad` |

Supporting: **AFK** ([afk-default.md](afk-default.md)) removes confirm round-trips. **LDA-PO** ([solve-plan-pattern.md](solve-plan-pattern.md)) front-loads structure so implement does not thrash. **SRP + thin** ([srp-thin.md](srp-thin.md)) keeps files small enough to re-read cheaply.

---

## Decision table

| Situation | Do | Skip |
|-----------|----|------|
| New session, task not given yet | `load index` → stop | Full file reads, `Task` spawn |
| Tiny fix (label, typo, 1 file, <~20 lines) | Cursor solo, foreground edit | Claude `Task`, browser proof, new refs |
| Heavy slice (multi-file, design needed) | Claude Senior `Task` or pasted plan | Cursor rival plan |
| Settled files already in context | Reuse | Re-read, re-explore |
| Need a reference doc | Read that one file | Read all of `references/` |
| Pure PHP / API / docs task | Skip agent-browser | Chrome launch |
| Prose reply to user | Caveman | Long recap of what you just did |
| Public README / PR / changelog | Humanizer, normal English | Caveman |

---

## Anti-patterns (real token burn)

- Dumping every `references/*.md` "to be safe" — metadata alone is tens of k.
- Re-deriving `progress.md` state instead of reading it.
- Spawning a subagent for work a foreground edit finishes in seconds.
- Two plans for one task (Claude plan + Cursor rival plan).
- Restating the diff in prose after the diff is already shown.
- Full ECC install — see [framework-best-used.md](framework-best-used.md) for the context-tax warning.
- agent-browser on an edit with no UI surface.

---

## Never compress these

Token saving is a **voice and loading** discipline, not a correctness discount.

- Spec gates **G1–G4** still run — see [quality-gates.md](quality-gates.md)
- Technical terms, file paths, status enums stay **exact**
- Verification evidence still written to `progress.md`
- No fabricated results to save a test run

---

## Opt out

`normal mode` · `stop caveman` · `normal cursor` · `cursor lead` · `verbose`

## Wire locations

| Layer | Path |
|-------|------|
| Skill entry | `SKILL.md` → Session defaults + Reference Files |
| Combo stack | [session-combo-stack.md](session-combo-stack.md) |
| Voice rule | `.cursor/rules/caveman-mode.mdc` |
| Listener rule | `.cursor/rules/08-claude-senior-listener.mdc` |
