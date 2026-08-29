---
name: harbor-trajectory
description: >-
  Reads Harbor Hub Trajectory tabs (ATIF steps #1 system, #2 user, agent turns),
  maps grok-build and other agent prompts, and ports patterns into Cursor rules
  or skills. Use when the user mentions trajectory, ATIF, trajectory.json,
  Harbor Hub trial steps, grok-build prompt, or studying agent runs.
---

# Harbor trajectory prompts

Harbor logs agent runs as **ATIF** (Agent Trajectory Interchange Format). The Hub **Trajectory** tab is a human view of `trajectory.json`.

Spec: [harborframework.com/docs/agents/trajectory-format](https://www.harborframework.com/docs/agents/trajectory-format) · RFC: [github.com/harbor-framework/harbor/blob/main/rfcs/0001-trajectory-format.md](https://github.com/harbor-framework/harbor/blob/main/rfcs/0001-trajectory-format.md)

Related: skill `terminal-bench` for waffle / runs · skill `tbench-prompt-library` to store distilled packs and route quality/speed.

## Step types (what #1, #2, … mean)

| Hub label | ATIF `source` | `message` holds |
|-----------|---------------|-----------------|
| **#1 system** | `system` | Full system prompt: identity, policies, tool rules, output format |
| **#2 user** | `user` | First user turn: env + task + `<user_query>` (and often `<attached_files>`) |
| **#3+ agent** | `agent` | Assistant text + optional `reasoning_content` + `tool_calls` + `observation` |
| later **user** | `user` | Follow-ups (rare in Terminal-Bench single-shot tasks) |

Each **agent** step is one model turn: what it said, what tools it called, shell/API output in **observation**, token **metrics**.

Hub sub-tabs on an agent step: **Tool call** · **Observation** · **Reasoning** map to those JSON fields.

## Typical grok-build trial (e.g. wdm-design)

Terminal-Bench runs **grok-build** in **non-interactive** mode (`is_non_interactive` in the template).

### #1 system — sections (in order)

Source template: [xai-org/grok-build `prompt.md`](https://github.com/xai-org/grok-build/blob/main/crates/codegen/xai-grok-agent/templates/prompt.md)

| Block | Role |
|-------|------|
| Identity | “You are Grok … autonomous agent … no human operator” |
| `<work_policy>` | Scope, intent matching, verify-before-claim, no drive-by edits |
| `<tool_calling>` | Prefer file tools over bash; no `echo` to user |
| `<background_tasks>` | Long commands in background; bounded wait; monitor for status |
| `<communication>` | Plain language, lead with answer, define terms, no invented jargon |
| `<formatting>` | GFM markdown; fence nesting rules |
| `<browser_verification>` | End-to-end browser check when UI changes (when tools exist) |

Interactive CLI adds `<user_guide>` (Grok TUI docs under `~/.grok/docs/user-guide/`).

### #2 user — layers (Grok order: query first, then context)

Parser: [prompt_parser.rs](https://github.com/xai-org/grok-build/blob/main/crates/codegen/xai-grok-shell/src/session/prompt_parser.rs)

| Layer | Tags / content |
|-------|----------------|
| Task query | `<user_query>` — for Terminal-Bench this is **`instruction.md`** (the task spec) |
| Environment | `<user_info>` — OS, shell, workspace path |
| Attachments | `<attached_files>` — file snippets, images |
| Skills / rules | `<skill_information>`, project instruction files (when present) |
| Dynamic tools | MCP / tool catalog (when present) |

On your screenshot: **#1** = full system template; **#2** starts with `<user_info>OS Version: linux</user_info>` then the task body.

Steps **#3 onward**: agent runs Meep/FDTP optimization shell commands until `/app/design.npy` + `/app/meta.json` exist; verifier runs after agent execution ends.

## Where to read trajectories

| Source | How |
|--------|-----|
| **Harbor Hub** | `hub.harborframework.com/jobs/{job}/trials/{trial}` → Trajectory tab |
| **Local job** | `{trial}/agent/trajectory.json` after `harbor run` |
| **Viewer** | `harbor view jobs` → same UI locally |
| **Export** | Hub public trials; or copy from uploaded job |

Validate JSON: `python -m harbor.utils.trajectory_validator trajectory.json`

## Study workflow (trajectory → Cursor)

1. Open a **passing** and a **failing** trial on the same task (e.g. wdm-design).
2. Read **#1 system** once — note policy blocks worth copying (usually `work_policy`, not identity fluff).
3. Read **#2 user** — separate **task** (`instruction.md`) from **harness** (user_info, attachments).
4. Skim agent steps for **first tool call**, **retry loops**, **verifier-facing artifacts** (paths in `/app/`).
5. Extract **one** pattern per deliverable (rule or skill section). Do not paste the whole 63-step prompt.

Decision table: [cursor-porting.md](cursor-porting.md)

Grok-build section map: [reference-grok-build-steps.md](reference-grok-build-steps.md)

## Rule vs skill (quick)

| Put in **Cursor rule** (`.cursor/rules/*.mdc`) | Put in **skill** (`.cursor/skills/*/SKILL.md`) |
|-----------------------------------------------|-----------------------------------------------|
| Always-on behavior (verify before done, scope) | How to read ATIF / Hub UI |
| Short bullets, &lt;50 lines | Workflow, links, examples |
| Project-specific conventions | Cross-project agent-study method |
| One concern per rule | “When user pastes trajectory screenshot” playbooks |

Do **not** copy “You are Grok released by xAI” into Cursor rules. Copy **behaviors**.

## Optional seeding (Harbor tasks)

Tasks may ship `trajectory.json` beside `instruction.md` to give agents prior context (multi-step). Oracle/nop agents skip it. See Harbor `load-trajectory` docs.

## After you distill a trial

Do not stop at reading Trajectory — write a pack:

1. Follow skill `tbench-prompt-library` → [import-trial.md](../tbench-prompt-library/import-trial.md)
2. Register in [catalog.yaml](../tbench-prompt-library/catalog.yaml)

## Canary

Terminal-Bench trajectories may contain benchmark canary strings. Do not paste full trajectories into training data or public docs.
