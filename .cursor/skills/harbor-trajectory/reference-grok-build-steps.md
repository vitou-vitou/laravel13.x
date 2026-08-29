# grok-build prompt map (ATIF ↔ template)

Read when comparing Hub Trajectory step text to [prompt.md](https://github.com/xai-org/grok-build/blob/main/crates/codegen/xai-grok-agent/templates/prompt.md).

## #1 system — full outline

```
You are {Grok X.Y} released by xAI.
You are {autonomous agent | interactive CLI tool} …
Your main goal is … within the <user_query> tag.

<work_policy>
  - requirements in view until done/blocked
  - match intent; no unsolicited edits on Q&A
  - reversible local work → do now, don’t “want me to?”
  - [optional] spawn subagents when user asks
  - claim done only with tool evidence
  - scoped diffs; short factual comments only
</work_policy>

<tool_calling>
  - specialized tools > bash for file ops
  - shell only for real system commands
  - never echo thoughts to user via terminal
</tool_calling>

<background_tasks>   [when execute/monitor tools exist]
  - long-owned commands → background
  - snapshot wait, not poll loops
  - monitor for external status changes only
</background_tasks>

<communication>
  - complete sentences; lead with answer
  - reader hasn’t seen tool calls
  - define terms on first use
  - no invented acronyms / catchy labels
</communication>

<formatting>
  - GFM; tables for facts; nested fence rules
</formatting>

<user_guide>         [interactive only]
  - ~/.grok/docs/user-guide/
</user_guide>

<browser_verification> [when browser tools exist]
  - E2E + regression + desktop/mobile for UI
</browser_verification>
```

Terminal-Bench uses **autonomous** + **browser_verification** when MCP browser is wired.

## #2 user — assembly order (Grok native)

Non-Cursor mode (`is_cursor: false`):

1. `<user_query>…</user_query>`
2. Optional preamble + `<attached_files>…</attached_files>`
3. Resource links (URLs, docs)
4. `<skill_information>…</skill_information>` when skills invoked

Cursor-compat mode flips to **context first, `<user_query>` last**.

### Terminal-Bench #2 contents

| Piece | Typical content |
|-------|-----------------|
| `<user_query>` | Full `instruction.md` (paths, physics, output files, timeout line) |
| `<user_info>` | Linux sandbox, cwd, sometimes empty git |
| Rules/skills | Usually minimal in bench container |
| Images | Rare unless task attaches figures |

## Agent steps (#3+)

Each ATIF agent step:

| Field | Hub UI |
|-------|--------|
| `message` | Assistant visible reply (often short) |
| `reasoning_content` | **Reasoning** tab |
| `tool_calls[]` | **Tool call** tab (`function_name`, `arguments`) |
| `observation.results[]` | **Observation** tab (stdout, file read, etc.) |
| `metrics` | Token/cost on step (Hub aggregates at top) |

Patterns worth stealing for Cursor **rules**, not prompts:

- Long tasks: plan in reasoning, one shell action per turn, read verifier errors literally.
- Science tasks: declare geometry in JSON + binary artifact before running heavy sim.
- Fail fast on missing deps; don’t claim pass before verifier paths exist.

## What not to port

| Skip | Why |
|------|-----|
| Model identity line | Cursor has its own agent |
| Tool name placeholders (`read_file`, `spawn_subagent`) | Cursor tool names differ |
| Full 63-step transcript | Noise; extract one policy |
| Benchmark task text | Canary / contamination |
