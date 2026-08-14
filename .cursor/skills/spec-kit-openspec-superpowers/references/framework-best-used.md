# Framework best-used (Superpowers · ECC · gstack)

Policy for this triad router. Source method: [Nebutra autopsy — Claude Code skill frameworks](https://nebutra.com/blog/claude-code-skill-frameworks-autopsy) (2026). Star count ≠ fit. Skills stay in context once loaded.

## What each sells

| Framework | Sells | Strength | Weakness |
|-----------|-------|----------|----------|
| **Superpowers** (`obra/superpowers`) | Discipline | Executable gates (TDD delete-and-restart, verify-before-done, no code before design approval) | Can feel heavy on solo speed runs |
| **gstack** | Momentum / persona company | Sharp product questions; auto-fix convenience for solo | Weak gates; `/review` `/qa` auto-commit risky on shared branches; silent failures |
| **ECC** (`affaan-m/ECC`) | Breadth (directory, not methodology) | Extract `tdd-workflow`, `eval-harness`; language reviewers; AgentShield | Context tax (metadata alone can burn tens of k tokens); skills ~50–80% fire rate; do not full-install |

**This router’s default spine is Superpowers** (with Spec-Kit / OpenSpec for SDD). ECC is cherry-pick only. gstack is opt-in for solo speed only.

## Four workflows → four prescriptions

### Team delivery (default for shared repos / PRs / CI)
1. **Skeleton: Superpowers** — hard gates + reviewable plans/worktrees.
2. **ECC:** cherry-pick **`tdd-workflow`** + **`eval-harness`** only. Never wholesale ECC.
3. **gstack:** at most `/codex`, `/retro`. **Avoid** `/review` and `/qa` on shared branches.

### Solo fast iteration
- Prefer **gstack** for momentum if you accept auto-fix blast radius.
- Superpowers’ 1% rule + mandatory brainstorm can slow you down — keep if jump-to-code is your failure mode.
- ECC only via **minimal profile** / individual skills.

### Legacy refactor
- Superpowers **`systematic-debugging`** + **`verification-before-completion`**.
- Optional ECC **`tdd-workflow`** (reachable-checkpoint rule for messy history).
- No gstack auto-fix skills.

### Learning
- **Superpowers** — being stopped from shortcuts is the lesson.
- ECC = **reference book** (read `SKILL.md` files); do not daily-drive the full tree.
- gstack risks doing the work for you.

## Universal install rule

Only install a skill into a **shared** environment if its core prohibition is **script-verifiable** (exit code, file content, or git-log). Slogan-only skills are decoration — polite suggestions do not constrain LLMs.

## Default stack for this triad (vitou)

```text
Spec-Kit OR OpenSpec (one SDD layer)
  + Superpowers (process rails — ALWAYS for Phase 4+)
  + ECC cherry-picks only when needed (tdd-workflow, eval-harness, one language reviewer)
  + AgentShield once before enabling heavy ECC hooks
  + NEVER: ECC full install + gstack auto-fix on shared branches
```

## Optional keyword triggers (session-combo-stack)

| Trigger words | Action |
|---------------|--------|
| `ECC`, `everything claude code`, `agentshield` | Load this doc; allow cherry-picks only; warn against full install |
| `gstack`, `garry tan skills` | Solo-only warning; block `/review` `/qa` advice on team branches |
| `superpowers`, TDD, verify, brainstorm | Normal Superpowers path (default) |
| `framework autopsy`, `best used frameworks` | Cite this doc + Nebutra link |

## Agent behavior when user asks to “use ECC instead of Superpowers”

1. Do **not** swap the triad spine to full ECC.
2. Explain: Superpowers = gates; ECC = directory/breadth.
3. Offer: keep Superpowers + cherry-pick named ECC skills that fill a gap.
4. If user insists on ECC-only: require **minimal profile**, one language rule pack, MCP cap, and list which Superpowers gates they are giving up.
