# Port trajectory insights → Cursor

Use after reading a Hub Trajectory. One pattern → one file.

## Extract template

For each candidate pattern, fill:

```text
Pattern:
Source: Hub trial {job}/{trial} step #{n} block {name}
Behavior: (one sentence — what the agent must do)
Trigger: (when Cursor should apply it)
Conflicts: (existing rules in this repo?)
Destination: rule | skill | skip
```

## Rule template (always-on behavior)

Path: `.cursor/rules/{short-name}.mdc`

```yaml
---
description: One line — when this applies (shown in rule picker)
alwaysApply: true
---
```

Body: bullets only, no identity preamble. Max ~40 lines.

**Good candidates from grok `<work_policy>`** (check overlap with `ponytail.mdc`, `14-code-must-benefit.mdc`, `verification-before-completion`):

- Match intent: implement requests; answer questions without editing
- Verify before “done” (tool output, tests, verifier)
- Reversible local work in the same turn
- Scoped diff; no comment narration

**Good candidates from `<communication>`** (overlap `08-reader-loved-code.mdc`, `caveman-mode.mdc`):

- Lead with answer; define terms once
- Final reply stands alone (no “as I said in step 12”)

**Good candidates from `<browser_verification>`** (overlap `16-playwright-archived.mdc` in this repo — respect archive unless user re-enables):

- UI change → manual or IDE browser E2E when user asks

## Skill template (study / workflow)

Path: `.cursor/skills/{name}/SKILL.md`

```yaml
---
name: {kebab-name}
description: Third person. WHAT + WHEN triggers (Hub, ATIF, task name).
---
```

Use for: reading ATIF, Harbor CLI, task-specific playbooks (e.g. photonics artifact layout), links.

## Example: wdm-design → project skill snippet

Not a rule — too domain-specific. Add to a **task playbook** skill section:

```markdown
## wdm-design artifact contract

Before long Meep runs:
- Write `/app/meta.json` (geometry within documented bounds)
- Write `/app/design.npy` (binary float64 grid)
- Verifier rebuilds device from meta; no gray pixels in final design

Study passing trial: grok-build + xai/grok-4.6 on Hub (wdm-design, reward 1.0).
```

## Example: thin always-on rule (if not redundant)

Only add if the repo lacks it. Name e.g. `intent-and-evidence.mdc`:

```markdown
---
description: Match user intent; verify claims with tool output before saying done
alwaysApply: true
---

# Intent and evidence

- Coding request → implement. Question or review → answer without unsolicited edits.
- Say done/fixed/tested only when a command, test, or verifier output supports it.
- Blocked → state blocker plainly; do not drop requirements silently.
```

Run `node .githooks/check-commented-code.mjs` on staged diffs if touching PHP/JS/Vue.

## Sync

After adding under `.cursor/skills/` or `.cursor/rules/`:

1. Copy skill to `~/.cursor/skills/` if personal
2. Update `docs/CURSOR_SKILLS_SYNC.md` manifest row
3. Commit hub per `13-notion-and-cursor-hub.mdc` when user expects sync

## Anti-patterns

- Pasting entire `#1 system` into a rule (token burn, wrong agent)
- Duplicating five existing rules in one mega-rule
- Copying Terminal-Bench `instruction.md` into Cursor (task-specific, canary)
- `alwaysApply: true` on Harbor-study content (use skill + description triggers instead)
