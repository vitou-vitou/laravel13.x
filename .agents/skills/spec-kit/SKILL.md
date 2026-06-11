---
name: spec-kit
description: >-
  GitHub Spec-Kit — spec-driven development for greenfield and governed projects.
  Use for /speckit.* slash commands, constitution/spec/plan/tasks/implement pipeline,
  Spec-Kit git extensions (feature branches, auto-commit, validate), specify CLI setup,
  or greenfield MVP work in laravel13.x examples. Not for OpenSpec post-MVP changes.
---

# Spec-Kit (GitHub Official SDD)

**Repo:** https://github.com/github/spec-kit  
**Stack:** Python + uv  
**Philosophy:** Write the spec first, then code.

Spec-Kit and OpenSpec are **competitors** — pick one SDD tool per feature. Pair with the **superpowers** skill during implementation.

---

## When to use

- Greenfield / complex system needing full design docs
- `/speckit.constitution`, `/speckit.specify`, `/speckit.plan`, `/speckit.tasks`, `/speckit.implement`
- Spec-Kit git hooks (feature branch, auto-commit, validate)
- **laravel13.x:** greenfield MVP in `examples/*` only — see [reference/laravel13-x-policy.md](reference/laravel13-x-policy.md)

**Do not use** for post-MVP iteration on existing code — use **openspec** instead.

---

## Five-phase pipeline

```
/speckit.constitution  →  project governance (constitution.md)
/speckit.specify       →  feature spec (what & why, no tech stack)
/speckit.plan          →  technical plan (stack, architecture, API contracts)
/speckit.tasks         →  executable task list
/speckit.implement     →  build the feature
```

**Optional:** `/speckit.clarify` (before plan), `/speckit.analyze` (after tasks), `/speckit.checklist`

### Key artifacts

| File | Purpose |
|------|---------|
| `.specify/memory/constitution.md` | Code quality, testing, UX, performance rules |
| `.specify/specs/<feature>/spec.md` | User stories, functional requirements |
| `.specify/specs/<feature>/plan.md` | Tech stack, architecture, API contracts |
| `.specify/specs/<feature>/tasks.md` | Implementation steps |

---

## Install & init

```bash
curl -LsSf https://astral.sh/uv/install.sh | sh
uv tool install specify-cli --from git+https://github.com/github/spec-kit.git
specify check
specify init my-project --ai cursor-agent
specify init . --here --ai cursor-agent
```

Supported AI targets: `claude`, `copilot`, `cursor-agent`, `gemini`, `windsurf`, `codex`, and 20+ others.

---

## Git extension sub-commands

When a Spec-Kit git hook or user request matches, **read the matching reference file first** (non-negotiable):

| Trigger | Reference |
|---------|-----------|
| Auto-commit after `/speckit.*` | [reference/git-commit.md](reference/git-commit.md) |
| Create feature branch | [reference/git-feature.md](reference/git-feature.md) |
| Init git repo | [reference/git-initialize.md](reference/git-initialize.md) |
| Detect GitHub remote | [reference/git-remote.md](reference/git-remote.md) |
| Validate branch naming | [reference/git-validate.md](reference/git-validate.md) |
| laravel13.x policy | [reference/laravel13-x-policy.md](reference/laravel13-x-policy.md) |

Scripts live in the project at `.specify/extensions/git/scripts/` — not in this skill folder.

---

## Implementation pairing

During `/speckit.implement`, use the **superpowers** skill:

1. `test-driven-development` — before production code
2. `verification-before-completion` — before claiming done
3. `systematic-debugging` — on test failures
4. `requesting-code-review` — after major steps

---

## Agent behavior

1. Keep spec = what/why; plan = how — never mix tech stack into spec phase
2. Decompose into tasks.md before implementing
3. Never combine Spec-Kit + OpenSpec on the same feature
4. On `continue` in laravel13.x → read `docs/SESSION_STATE.md` first

---

## Cross-machine sync

| Location | Purpose |
|----------|---------|
| `~/.cursor/skills/spec-kit/` | Personal skill (Cursor account sync) |
| `$HOME/.cursor/skills/spec-kit/` | Same path, portable |

Install CLI locally on each machine: `uv tool install specify-cli --from git+https://github.com/github/spec-kit.git`

---

## Invocation examples

```text
Use spec-kit: run /speckit.tasks for the current feature branch.
```

```text
Use spec-kit: create a feature branch for add-coupon-system.
```
