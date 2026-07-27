---
name: /super-spec
id: super-spec
category: Workflow
description: Spec-first workflow — auto mode (OpenSpec/Spec-Kit) + complexity triage before any code
---

Start the **Spec-First + Superpowers** orchestrator. No implementation until the user confirms the spec.

**MANDATORY**: Read and follow `.cursor/skills/spec-kit-openspec-superpowers/SKILL.md` for the full pipeline, quality gates (G0–G4), and anti-rush rules.

**Input**: Optional variant after `/super-spec`:

| Argument | Effect |
|----------|--------|
| *(none)* | Full workflow — auto-detect mode + auto complexity triage |
| `force-openspec` | Force OpenSpec mode (write `openspec` to `.spec-mode`) |
| `force-spec-kit` | Force Spec-Kit mode (write `spec-kit` to `.spec-mode`) |
| `reset` | Delete `.spec-mode` and re-run auto-detection on next step |
| `upgrade` | Version sync — follow `references/upgrade-protocol.md` in the skill |

If the user describes a feature/bugfix/refactor after the command (e.g. `/super-spec add dark mode toggle`), treat that text as the task to spec — do not start coding.

---

## Step 1 — Resolve Mode

1. If argument is `force-openspec` → write `openspec` to project-root `.spec-mode`
2. If argument is `force-spec-kit` → write `spec-kit` to project-root `.spec-mode`
3. If argument is `reset` → delete `.spec-mode`, report mode cleared, then stop (unless user also gave a task)
4. If argument is `upgrade` → run upgrade protocol from skill `references/upgrade-protocol.md`, then stop
5. Otherwise, detect mode:

| Signal | Mode |
|--------|------|
| `.spec-mode` file exists | Use its value |
| `openspec/` directory | OpenSpec |
| `.specify/` directory | Spec-Kit |
| Brand new project, < 30 files | Spec-Kit |
| Everything else | **OpenSpec** (default) |

6. If no `.spec-mode` yet, write the detected mode to `.spec-mode`

**This project**: `openspec/` exists → default **OpenSpec** unless `.spec-mode` says otherwise.

Mode workflows: `references/openspec-workflow.md` or `references/spec-kit-workflow.md` in the skill directory.

---

## Step 2 — Triage Complexity

Suggest a level; user must confirm or override:

| Level | When | Path |
|-------|------|------|
| **Quick** | Single-file bugfix, typo, config | Simplified spec → TDD → archive |
| **Standard** | Single feature, clear scope | All phases (Phase 3 only if UI) |
| **Thorough** | Multi-module, architecture decisions | All phases + Agent Teams evaluation |

---

## Step 3 — Execute Pipeline

**Phase 0 — Session Recovery** (automatic)
If `task_plan.md` exists, read `task_plan.md`, `findings.md`, `progress.md`, run the 5-Question Reboot Test, resume from last checkpoint.

**Phase 1 — Specification**
- OpenSpec Quick → use project command `/opsx-propose` (or `openspec` CLI)
- OpenSpec Standard/Thorough → `/opsx-explore` then `/opsx-propose`
- Spec-Kit → `/speckit.specify` flow per `references/spec-kit-workflow.md`

**Gate G1**: User explicitly confirmed spec + inline spec review passed.

**Phase 2 — Planning**
Generate `task_plan.md`, `findings.md`, `progress.md` via `planning-with-files` + `writing-plans`.

**Gate G2**: Tasks have file paths, acceptance criteria, test strategy.

**Phase 3 — UI/UX** (only if UI keywords detected)
Invoke `ui-ux-pro-max --design-system --persist`.

**Gate G3**: Pre-delivery checklist + user confirmed design.

**Phase 4 — Implementation**
TDD throughout. User picks subagent-driven or executing-plans strategy.
OpenSpec implementation → `/opsx-apply` when tasks exist.

**Gate G4**: Tests pass + verification evidence in `progress.md`.

**Phase 5 — Archive**
`finishing-a-development-branch` → archive artifacts → final `progress.md` entry.
OpenSpec → `/opsx-archive` when change is complete.

---

## Guardrails

- **Never skip spec** — if user pushes to code first, decline and redirect here
- **Never implement** before G1 user confirmation
- Read `references/quality-gates.md` when evaluating any gate
- Delegate OpenSpec steps to existing project commands: `/opsx-explore`, `/opsx-propose`, `/opsx-apply`, `/opsx-archive`

**Output after Phase 1**: Summarize spec location, complexity level, mode, next gate, and what the user must confirm before implementation.
