---
name: openspec
description: >-
  OpenSpec — flexible spec-driven development for existing codebases and post-MVP changes.
  Use for /opsx:* slash commands (new, continue, ff, apply, archive, explore), openspec CLI,
  change proposals, artifact workflows, and iteration on laravel13.x after MVP. Not for greenfield Spec-Kit.
---

# OpenSpec (Flexible SDD)

**Repo:** https://github.com/Fission-AI/OpenSpec  
**Stack:** TypeScript (npm)  
**Philosophy:** Action-based OPSX workflow — iterate, don't lock into rigid phases.

OpenSpec and Spec-Kit are **competitors** — pick one SDD tool per feature. Pair with the **superpowers** skill during `/opsx:apply`.

---

## When to use

- Existing codebase, fast iteration, change tracking
- Post-MVP work in laravel13.x (after Spec-Kit MVP complete)
- `/opsx:new`, `/opsx:continue`, `/opsx:ff`, `/opsx:apply`, `/opsx:archive`, `/opsx:explore`

**Do not use** at greenfield init — use **spec-kit** for new MVP projects in `examples/*`.

---

## OPSX commands

| Command | Action |
|---------|--------|
| `/opsx:new <name>` | Start change (creates proposal) |
| `/opsx:continue` | Create next artifact (specs → design → tasks) |
| `/opsx:apply` | Implement tasks, update artifacts live |
| `/opsx:archive <name>` | Move completed change to knowledge base |
| `/opsx:explore` | Think through ideas before committing |
| `/opsx:ff <name>` | Fast-forward: all planning artifacts at once |
| `/opsx:sync` | Sync to main branch (optional) |

---

## Install & init

```bash
npm install -g @fission-ai/openspec@latest
openspec init
openspec status --change add-user-profile-page --json
openspec list --json
```

Requires **openspec CLI** on each machine — not synced by Cursor.

---

## Sub-command routing

When user invokes an OPSX action, **read the matching reference file first** (non-negotiable):

| Trigger | Reference |
|---------|-----------|
| `/opsx:apply`, implement change, work through tasks | [reference/apply-change.md](reference/apply-change.md) |
| `/opsx:archive`, finalize change | [reference/archive-change.md](reference/archive-change.md) |
| `/opsx:explore`, think before committing | [reference/explore.md](reference/explore.md) |
| `/opsx:ff`, `/opsx:new` one-shot proposal | [reference/propose.md](reference/propose.md) |
| `/opsx:continue` | Use propose flow or continue artifact sequence per `openspec status --json` |

---

## Implementation pairing

During `/opsx:apply`, use the **superpowers** skill:

1. `test-driven-development` — per task
2. `verification-before-completion` — before claiming done
3. `systematic-debugging` — on failures

---

## Agent behavior

1. Always use `openspec` CLI for status/instructions — don't guess artifact paths
2. Read all `contextFiles` from apply instructions before coding
3. Mark tasks `- [x]` immediately after completing each one
4. Never combine OpenSpec + Spec-Kit on the same feature
5. On `continue` in laravel13.x → read `docs/SESSION_STATE.md` first

---

## Cross-machine sync

| Location | Purpose |
|----------|---------|
| `~/.cursor/skills/openspec/` | Personal skill (Cursor account sync) |
| `$HOME/.cursor/skills/openspec/` | Same path, portable |

Install CLI locally: `npm install -g @fission-ai/openspec@latest`

---

## Invocation examples

```text
Use openspec: /opsx:apply for add-order-lifecycle.
```

```text
Use openspec: explore whether we should add Redis caching.
```
