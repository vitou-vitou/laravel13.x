---
name: superpowers
description: >-
  Superpowers execution methodology — TDD, brainstorming, debugging, plans, subagents,
  code review, verification, git worktrees. Use when implementing features or bugfixes,
  before creative work, on test failures, before claiming done, or when executing plans.
  Complements spec-kit or openspec — not an SDD tool itself.
---

# Superpowers (Execution Methodology)

**Repo:** https://github.com/obra/superpowers  
**Philosophy:** Make AI work like a senior engineer — disciplined process over ad-hoc coding.

**Not an SDD tool.** Use with **spec-kit** (greenfield) or **openspec** (post-MVP).

Also available as a Cursor **plugin** — this personal skill syncs via account for machines without the plugin.

---

## The rule

If a sub-skill might apply (even 1% chance), **read its reference file before acting**. User instructions override skill rules.

**Priority:** User explicit instructions > Superpowers > default behavior.

---

## Sub-skill routing

| Situation | Read first |
|-----------|------------|
| Starting any session / skill discipline | [reference/using-superpowers.md](reference/using-superpowers.md) |
| Creative work, new features, behavior changes | [reference/brainstorming.md](reference/brainstorming.md) |
| Implementing feature or bugfix | [reference/test-driven-development.md](reference/test-driven-development.md) |
| About to claim done, commit, or PR | [reference/verification-before-completion.md](reference/verification-before-completion.md) |
| Bug, test failure, unexpected behavior | [reference/systematic-debugging.md](reference/systematic-debugging.md) |
| Multi-step task needs a plan | [reference/writing-plans.md](reference/writing-plans.md) |
| Plan exists, ready to implement | [reference/executing-plans.md](reference/executing-plans.md) |
| Independent parallel tasks | [reference/subagent-driven-development.md](reference/subagent-driven-development.md) |
| 2+ independent tasks, no shared state | [reference/dispatching-parallel-agents.md](reference/dispatching-parallel-agents.md) |
| Major step complete, need review | [reference/requesting-code-review.md](reference/requesting-code-review.md) |
| Received review feedback | [reference/receiving-code-review.md](reference/receiving-code-review.md) |
| UI polish after MVP (laravel13.x `examples/*`) | **laravel-ui-phase** + **impeccable** + **design-taste-frontend**; read `docs/GITHUB_UI_RESOURCE_INDEX.md` |
| Isolated feature workspace | [reference/using-git-worktrees.md](reference/using-git-worktrees.md) |
| All tests pass, decide merge/PR/cleanup | [reference/finishing-a-development-branch.md](reference/finishing-a-development-branch.md) |
| Authoring or editing skills | [reference/writing-skills.md](reference/writing-skills.md) |

---

## Skill priority (when multiple apply)

1. **Process skills first** — brainstorming, debugging (HOW to approach)
2. **Implementation skills second** — TDD, domain-specific (WHAT to build)

Examples:
- "Let's build X" → brainstorming → writing-plans → TDD
- "Fix this bug" → systematic-debugging → TDD

---

## Rigid vs flexible

**Rigid** (follow exactly): TDD, debugging, verification-before-completion  
**Flexible** (adapt principles): patterns, plans

The reference file tells you which.

---

## laravel13.x defaults

During Spec-Kit implement or OpenSpec apply:

1. `test-driven-development`
2. `verification-before-completion`
3. `systematic-debugging` on failures
4. `requesting-code-review` after major steps
5. `subagent-driven-development` for parallel independent tasks

PHP tests (Windows Herd): `/c/Users/vitou/.config/herd/bin/php.bat artisan test` from each `examples/*` directory.

**UI phase (optional, after tests green):** **laravel-ui-phase** → DESIGN.md → impeccable polish → verify tests again. User trigger: **AI pick my UI**.

---

## Deprecated slash commands

| Old | Use reference |
|-----|---------------|
| `/brainstorm` | brainstorming.md |
| `/write-plan` | writing-plans.md |
| `/execute-plan` | executing-plans.md |

---

## Cross-machine sync

| Location | Purpose |
|----------|---------|
| `~/.cursor/skills/superpowers/` | Personal skill (Cursor account sync) |
| `$HOME/.cursor/skills/superpowers/reference/` | Sub-skill detail files |

**Also install plugin** (recommended): Cursor → Plugins → Superpowers (obra/superpowers). Plugin and personal skill can coexist.

---

## Invocation examples

```text
Use superpowers: implement this bugfix with TDD.
```

```text
Use superpowers: verify all tests pass before we commit.
```
