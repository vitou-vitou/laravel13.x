---
name: caveman-spec-triad
description: >-
  Session preset: Caveman voice + Spec-Kit / OpenSpec / Superpowers triad stack.
  Use when user says "/Caveman spec kit Openspec Superpower", "Use caveman spec kit
  openspec superpower:", "caveman spec triad", "caveman + triad", or wants terse voice
  plus SDD routing for laravel13.x. Does NOT auto-run /speckit.* or /opsx:* — stacks
  manuals + voice only.
---

# Caveman + Spec-Kit / OpenSpec / Superpowers — Session Stack

One invocation loads **voice + triad router + laravel13.x policy**. User still picks SDD path and runs commands when ready.

---

## When this skill fires

Match any of:

```text
/Caveman spec kit Openspec Superpower
Use caveman spec kit openspec superpower:
caveman spec triad
caveman + triad
```

**Do not** auto-run `/speckit.*`, `/opsx:*`, or `specify`/`openspec` CLI. Stack context only.

---

## On invoke — agent checklist (in order)

1. **Activate Caveman voice** (persistent until user says `stop caveman` or `normal mode`)
   - Read plugin skill: `~/.cursor/plugins/cache/caveman/caveman/*/skills/caveman/SKILL.md`
   - Default intensity: **full** (fragments OK, drop filler, keep technical terms exact)
   - Code/commits/PRs: write normal; prose: caveman

2. **Load triad router** (read, do not execute)
   - `.cursor/skills/spec-kit-openspec-superpowers/SKILL.md` (or `~/.cursor/skills/spec-kit-openspec-superpowers/SKILL.md`)
   - `.cursor/skills/spec-kit-openspec-superpowers/laravel13-x-policy.md`

3. **Load execution manuals** (read when task needs them — not all at once unless asked)
   - **spec-kit** — greenfield MVP, `/speckit.*`
   - **openspec** — post-MVP, `/opsx:*`
   - **superpowers** — TDD, debugging, plans, verification (always during implement)

4. **Session resume**
   - If user says **continue** or resumes laravel13.x work → read `docs/SESSION_STATE.md` first
   - Project rules: `.cursor/rules/session-handoff.mdc`, `.cursor/rules/windows-herd-gitbash.mdc`

5. **Confirm stack** — one short caveman reply: voice ON, triad loaded, SDD not started, ask what task (greenfield / post-MVP / small fix / **UI polish**).

6. **UI too basic?** (after MVP / tests green)
   - Read **laravel-ui-phase** skill (`.agents/skills/laravel-ui-phase/SKILL.md`)
   - User says **"AI pick my UI"** → `docs/GITHUB_UI_RESOURCE_INDEX.md` + **impeccable** + **design-taste-frontend**
   - Caveman voice OK; still write `examples/*/docs/DESIGN.md`, still run tests

---

## Triad decision (reminder)

```
Need structured SDD?
├── Greenfield MVP → spec-kit + superpowers (+ caveman ON)
├── Post-MVP change → openspec + superpowers (+ caveman ON)
└── Small task → superpowers alone (+ caveman ON)

NEVER spec-kit + openspec on same feature
```

| Layer | Skill folder | Question |
|-------|--------------|----------|
| SDD (pick one) | `spec-kit/` or `openspec/` | What to build / what changed? |
| Execution | `superpowers/` | How to build (TDD, verify, debug)? |
| Voice | caveman plugin | How to speak (this stack)? |

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

---

## Sync

Mirrored at `~/.cursor/skills/caveman-spec-triad/` — see `docs/CURSOR_SKILLS_SYNC.md`.
