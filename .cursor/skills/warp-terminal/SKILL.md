---
name: warp-terminal
description: >-
  Warp terminal agent behaviors distilled as portable rules for any agent
  (Cursor, Claude, Codex). Covers Warp's command safety gates, long-run
  feedback threshold, typo tolerance, workflow prompt algorithms, and the
  sqlite capture method to re-extract them from a live Warp install. Use when
  the user mentions Warp, Warp Drive, porting terminal behaviors, or wants a
  terminal-agent behavior audit.
---

# Warp terminal → portable agent behaviors

Distilled 2026-09-05 from a live Warp install on Windows — one month of data:
3,414 commands, 58 agent conversations, 10 Warp Drive workflows, full `settings.toml`.

## Behavior catalog

### 1. Command safety gates

Warp's execution profile gates every command before the agent runs it:

| Gate | Patterns | Portable rule |
|------|----------|---------------|
| Deny | `bash`, `sh`, `zsh`, `pwsh`, `eval`, `exec`, `source` | Agent never spawns subshells |
| Deny | `curl`, `wget`, `dig`, `nslookup`, `host`, `ssh`, `scp`, `rsync`, `telnet` | No network probing from agent context |
| Deny | `rm` | No destructive deletes |
| Ask user | computer use, spawning other agents | Confirm before either |
| Allow | ordinary commands, PTY writes | Run freely |

### 2. Long-run feedback

`long_running_threshold = 30` — past 30s of silent work Warp notifies.
Portable: agent posts a status line after ~30s, not silence.

### 3. Typo tolerance

Evidence from history: `git status` typed 680 times, misspelled 60 times
(`git satus`, `stauts`, `stsatus`, `tatus`) plus `npm rund` ×13. Warp's
autosuggestion still completes them.
Portable: match command **intent**, not spelling — suggest the fix, don't fail.

### 4. Agent commands share history

`include_agent_commands_in_history = true` — agent-executed commands land in
the same history stream as typed ones. Auditable after the fact.
Portable: log agent runs where user commands live.

### 5. Secret masking

Warp keeps a 20-pattern regex list (ghp_, sk-, AKIA, JWT, wk-, Stripe rk_, …).
Portable: same list gates what an agent may paste into output or commits.

### 6. Reasoning visible then folded

`show_and_collapse` — thinking shown, then collapsed.
Portable: one-line reasoning summary up front; detail folded away.

### 7. AI auto-detection chip (NLD routing)

`ai_auto_detection_enabled = true` + `nld_in_terminal_enabled = true` +
`agent_toolbar_chip_selection_setting = "default"`.
Typed text classified command-vs-natural-language; NL input renders a
clickable chip at the input's edge hinting "send to agent" — click routes
the text into an agent conversation as `initial_query`. Evidence: `git add .`
and `cursor .` appear in `agent_conversations` as conversation openers.
Portable: classify short ambiguous input; offer one-click routing to the
capable path instead of executing prose as a shell command.

## Workflow algorithms

Warp Drive `agent_mode` workflows are numbered prompts. Run as-is on any agent:

**Create PR from branch**
1. Status first — staged, unstaged, branch name.
2. Format — detect language, run its formatter, stage.
3. Commit — conventional message, why over what.
4. Compare — diff vs default branch, PR body from repo template.
5. Push, `gh pr create`, open browser. Feedback after each step; stop on error.

**Review and fix branch**
1. Branch, diff, recent commits.
2. Run project linters.
3. Judge: language conventions, clean APIs, logic bugs, security, performance.
4. Flag problems, apply fixes, restage.
5. Re-run checks until clean.

**Add tests to PR**
1. List PR changes.
2. Propose one test at a time; user approves each.
3. Run it before writing the next.

**Explain codebase**
Four fixed sections: Core Components · Interactions · Deployment · Runtime.
Opinions only when asked.

## Capture algorithm

Re-extract from any Warp install on Windows + Git Bash:

| Artifact | Path |
|----------|------|
| Settings | `%LOCALAPPDATA%\Warp\Warp\config\settings.toml` |
| Keybindings | `%LOCALAPPDATA%\Warp\Warp\config\keybindings.yaml` |
| History / workflows / conversations | `%LOCALAPPDATA%\Warp\Warp\data\warp.sqlite` (+`-wal`, `-shm`) |

Steps:

1. Copy `warp.sqlite` **and** `-wal`, `-shm` to `%TEMP%` — live DB may be
   locked; copying all three preserves the latest committed rows.
2. Write a **script file** for queries — inline `python -c` dies silently in
   this shell and the `sqlite3` CLI is not installed.
3. Query:
   - `commands` — count base verbs, typo clusters, `is_agent_executed` ratio
   - `workflows` — JSON in `data`; `type: agent_mode` rows hold prompt algorithms
   - `agent_conversations` — `summary` JSON has initial query, title, workdir
   - `ai_queries` — every prompt with workdir and completion status
4. `sys.stdout.reconfigure(encoding='utf-8', errors='replace')` — cp1252
   console dies on agent blobs.
5. Distill **one behavior per rule**. Never paste model identity or full transcripts.

## Port decision table

| Target | When |
|--------|------|
| Cursor rule (`.mdc`) | Always-on behavior: safety gates, thresholds, typo tolerance |
| Skill (`SKILL.md`) | The method: capture steps, workflow prompt library |
| `AGENTS.md` | Project-level: which commands this project's agent may run |
| Skip | Model identity, telemetry toggles, theme config |

## User interaction model

From 58 conversations:

- Inputs are terse — "hi", "done", "help me pull". Answer low-context prompts
  by stating current state first, then asking one sharp question.
- Memory asks ("pls remember no git-push till i ask") → persist as a rule and
  tell the user which file it landed in.
- Multi-IDE flow — `cursor .`, `phpstorm .` hand sessions between editors.
  End state reports what's open where.
