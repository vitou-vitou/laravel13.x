---
name: caveman-spec-triad
description: >-
<<<<<<< HEAD
  Session preset: ALWAYS caveman + karpathy-guidelines + clean-code + reader rules +
  humanizer + Spec-Kit/OpenSpec/Superpowers triad. Use when user says
  "/Caveman spec kit Openspec Superpower", "Use caveman spec kit openspec superpower:",
  "caveman spec triad", "caveman + triad". Does NOT auto-run /speckit.* or /opsx:*.
  Optionals (impeccable, laravel-specialist, …) by keyword.
=======
  Session preset: Caveman voice + Spec-Kit / OpenSpec / Superpowers triad stack.
  Use when user says "/Caveman spec kit Openspec Superpower", "Use caveman spec kit
  openspec superpower:", "caveman spec triad", "caveman + triad", or wants terse voice
  plus SDD routing for pgi-core-frontend. Does NOT auto-run /speckit.* or /opsx:* — stacks
  manuals + voice only.
>>>>>>> 407e4c65870a68aebf9c1272b5f464cca78d0e3d
---

# Caveman + Spec-Kit / OpenSpec / Superpowers — Session Stack

<<<<<<< HEAD
One invocation loads **ALWAYS combo** (voice + karpathy + clean-code + reader brand + humanizer + triad + laravel13.x policy). User still picks SDD path when ready.

Full table: `../spec-kit-openspec-superpowers/references/session-combo-stack.md`
=======
One invocation loads **voice + triad router + pgi-core policy**. User still picks SDD path and runs commands when ready.
>>>>>>> 407e4c65870a68aebf9c1272b5f464cca78d0e3d

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

<<<<<<< HEAD
1. **Caveman voice** (ON until `stop caveman` / `normal mode`)
2. **Karpathy guidelines** — `~/.cursor/skills/karpathy-guidelines/SKILL.md`
3. **Clean Code** — `~/.cursor/skills/clean-code/SKILL.md` (or `~/.agents/skills/clean-code/`)
4. **Reader rules** — `08-reader-loved-code`, `04-simple-code-voice`, `commit-humanizer`
5. **Humanizer** — `~/.cursor/skills/humanizer/SKILL.md`
6. **Triad** — `spec-kit-openspec-superpowers/SKILL.md` + `laravel13-x-policy.md`
7. **Session** — `docs/SESSION_STATE.md` on continue
8. **Confirm:** `Stack: caveman + karpathy + clean-code + reader + humanizer + triad. Ready. What task?`
=======
1. **Activate Caveman voice** (persistent until user says `stop caveman` or `normal mode`)
   - Read plugin skill: `~/.cursor/plugins/cache/caveman/caveman/*/skills/caveman/SKILL.md`
   - Or project rule: `.cursor/rules/caveman-mode.mdc`
   - Default intensity: **full** (fragments OK, drop filler, keep technical terms exact)
   - Code/commits/PRs: write normal; prose: caveman

2. **Load triad router** (read, do not execute)
   - `.cursor/skills/spec-kit-openspec-superpowers/SKILL.md`
   - `.cursor/skills/spec-kit-openspec-superpowers/pgi-core-policy.md`

3. **Load execution manuals** (read when task needs them — not all at once unless asked)
   - **openspec** — default for this repo (`openspec/`, `/opsx:*`, `/super-spec`)
   - **spec-kit** — greenfield only if user forces Spec-Kit
   - **superpowers** — TDD, debugging, plans, verification (always during implement)

4. **Session resume**
   - If user says **continue** or resumes PL Direct Book work → read `docs/SESSION_STATE.md` first
   - Then `openspec/changes/phase-ii-quotation-slice-only/progress.md` + `task_plan.md` if present
   - Project rules: `.cursor/rules/session-handoff.mdc`, `.cursor/rules/windows-herd-gitbash.mdc`, `.cursor/rules/02-pl-seven-product-scope.mdc`

5. **Confirm stack** — one short caveman reply: voice ON, triad loaded, SDD not started, ask what task (PL slice / bugfix / UI polish).

6. **UI polish?** (Vue / PrimeVue / Tailwind)
   - Read **impeccable** + `.cursor/rules/01-impeccable-ui.mdc`
   - Extend Direct Book shell — no drive-by refactor of legacy PL products
>>>>>>> 407e4c65870a68aebf9c1272b5f464cca78d0e3d

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
<<<<<<< HEAD
Greenfield MVP → spec-kit + superpowers (+ ALWAYS stack)
Post-MVP → openspec + superpowers (+ ALWAYS stack)
Small task → superpowers alone (+ ALWAYS stack)
NEVER spec-kit + openspec on same feature
```

=======
Need structured SDD?
├── This repo (existing) → openspec + superpowers (+ caveman ON)
├── Brand-new isolated app → spec-kit + superpowers (+ caveman ON)
└── Small task → superpowers alone (+ caveman ON)

NEVER spec-kit + openspec on same feature
```

| Layer | Skill folder | Question |
|-------|--------------|----------|
| SDD (pick one) | `openspec/` or `spec-kit/` | What to build / what changed? |
| Execution | `superpowers/` | How to build (TDD, verify, debug)? |
| Voice | caveman plugin / caveman-mode.mdc | How to speak (this stack)? |

---

## Downstream invocations (after stack is active)

User narrows to one tool:

```text
Use spec-kit: /speckit.tasks
Use openspec: /opsx:new my-change
Use superpowers: TDD for next task
Use caveman: /caveman ultra
stop caveman
```

>>>>>>> 407e4c65870a68aebf9c1272b5f464cca78d0e3d
---

## Sync

`~/.cursor/skills/caveman-spec-triad/` · `docs/CURSOR_SKILLS_SYNC.md`  
Combo: `spec-kit-openspec-superpowers/references/session-combo-stack.md`
