---
name: caveman-spec-triad
description: >-
  Session preset: ALWAYS caveman + karpathy-guidelines + clean-code + reader rules +
  humanizer + Spec-Kit/OpenSpec/Superpowers triad. Use when user says
  "/Caveman spec kit Openspec Superpower", "Use caveman spec kit openspec superpower:",
  "caveman spec triad", "caveman + triad". Does NOT auto-run /speckit.* or /opsx:*.
  Optionals (impeccable, laravel-specialist, …) by keyword.
---

# Caveman + Spec-Kit / OpenSpec / Superpowers — Session Stack

One invocation loads **ALWAYS combo** (voice + karpathy + clean-code + reader brand + humanizer + triad + laravel13.x policy). User still picks SDD path when ready.

Full table: `../spec-kit-openspec-superpowers/references/session-combo-stack.md`
Token levers: `../spec-kit-openspec-superpowers/references/token-budget.md`

---

## When this skill fires

Match any of:

```text
/Caveman spec kit Openspec Superpower
Use caveman spec kit openspec superpower:
caveman spec triad
caveman + triad
```

Also honor same ALWAYS stack on `/spec-kit-openspec-superpowers` or `/super-spec`.

**Do not** auto-run `/speckit.*`, `/opsx:*`, or CLI. Stack only.

---

## On invoke — ALWAYS load (in order)

1. **Caveman voice** (ON until `stop caveman` / `normal mode`)
2. **Karpathy guidelines** — `~/.cursor/skills/karpathy-guidelines/SKILL.md`
3. **Clean Code** — `~/.cursor/skills/clean-code/SKILL.md` (or `~/.agents/skills/clean-code/`)
4. **Reader rules** — `08-reader-loved-code`, `04-simple-code-voice`, `commit-humanizer`
5. **Humanizer** — `~/.cursor/skills/humanizer/SKILL.md`
6. **Triad** — `spec-kit-openspec-superpowers/SKILL.md` + `laravel13-x-policy.md`
7. **Session** — `docs/SESSION_STATE.md` on continue
8. **Confirm:** `Stack: caveman + karpathy + clean-code + reader + humanizer + triad. Ready. What task?`

---

## OPTIONAL load (only if needed)

| Trigger | Load |
|---------|------|
| UI / polish / Vue | `impeccable` |
| AI pick my UI | `laravel-ui-phase` |
| Deep Laravel | `laravel-specialist` |
| Implement / TDD | `superpowers` |
| Greenfield SDD | `spec-kit` |
| Post-MVP SDD | `openspec` |
| Arch decision | `senior-architect` |

---

## Triad decision

```
Greenfield MVP → spec-kit + superpowers (+ ALWAYS stack)
Post-MVP → openspec + superpowers (+ ALWAYS stack)
Small task → superpowers alone (+ ALWAYS stack)
NEVER spec-kit + openspec on same feature
```

---

## Sync

`~/.cursor/skills/caveman-spec-triad/` · `docs/CURSOR_SKILLS_SYNC.md`  
Combo: `spec-kit-openspec-superpowers/references/session-combo-stack.md`
