---
name: spec-kit-openspec-superpowers
description: >
  Enforces spec-before-code workflow for AI-driven development. Automatically selects
  Spec-Kit or OpenSpec mode, triages complexity (quick/standard/thorough), recovers
  session context, and applies quality gates (G0-G4) with inline self-review at every stage.
  Use this skill whenever the user says "/super-spec", "spec first", "规范先行",
  or starts any feature, bugfix, or refactor — especially in projects with .spec-mode,
  .specify/, or openspec/ directories. Even if the user doesn't explicitly ask for
  spec-driven workflow, activate this skill for any non-trivial code change to prevent
  skipping the design phase.
  Orchestrates: Spec-Kit (v0.7.1, Workflow Engine) / OpenSpec (OPSX v1.2.0) +
  planning-with-files (v2.30.0) + ui-ux-pro-max (v2.5.0, 67 styles, 161 palettes,
  14 stacks, 6 specialist skills) + Superpowers (v5.0.7, inline self-review,
  subagent model selection) + MemPalace (v3.3.0, 29 MCP tools, cross-session memory,
  knowledge graph).
  Every session (pgi): Claude Senior listener mode (save Cursor tokens) +
  agent-browser when UI/browser proof needed — see references/claude-senior-listener.md.
---

# Spec-First + Superpowers Orchestrator v5

Stop the AI from jumping straight to code. Every feature, bugfix, and refactor goes through a specification phase first — because unexamined code is expensive code.

## Commands

| Command | Effect |
|---------|--------|
| `/super-spec` | Full workflow (auto mode + auto complexity) |
| `/super-spec force-spec-kit` | Force Spec-Kit mode |
| `/super-spec force-openspec` | Force OpenSpec mode |
| `/super-spec reset` | Reset mode selection |
| `/super-spec upgrade` | Check all integrated projects for updates and execute upgrade |
| `claude senior` / `listener mode` | Reinforce Claude Senior + Cursor thin listener (default every session on pgi) |
| `normal cursor` | Opt out — Cursor leads again |

## Session defaults (every activation)

**Default stack — ON every session (99% workflow).** User need not attach skill.

On skill load / `/super-spec` / **any coding task** in **pgi-core-frontend**:

1. **This orchestrator is the default** — OpenSpec + Superpowers + gates G1–G4.
2. **Claude Senior listener ON** — Claude does heavy work; Cursor listens, applies, verifies. Detail: [references/claude-senior-listener.md](references/claude-senior-listener.md) · project rule `08-claude-senior-listener.mdc`.
3. **agent-browser when needed** — UI/dropdown/form/visual/G4 browser proof. Load `agent-browser` skill → `agent-browser skills get core` before first use. Skip for pure PHP/API/docs.
4. Spec gates G1–G4 still apply — listener mode does **not** skip OpenSpec.
5. Opt out of stack: user says `no super-spec` / `plain agent`.

## How It Works

### Step 1: Pick a Mode

Check for existing signals, then fall back to heuristics:

| Signal | Mode |
|--------|------|
| `.spec-mode` file exists | Use whatever it says |
| `.specify/` directory | Spec-Kit |
| `openspec/` directory | OpenSpec |
| Brand new project, < 30 files | Spec-Kit |
| Everything else | **OpenSpec** (default) |

Save the choice to `.spec-mode` so future sessions remember it.

For detailed mode workflows, read:
- Spec-Kit: [references/spec-kit-workflow.md](references/spec-kit-workflow.md)
- OpenSpec: [references/openspec-workflow.md](references/openspec-workflow.md)

### Step 2: Triage Complexity

The AI suggests a level; the user confirms or overrides.

| Level | When | What happens |
|-------|------|-------------|
| **Quick** | Single-file bugfix, typo, config | Simplified spec (`/opsx:propose` or `/opsx:ff`) → TDD → archive |
| **Standard** | Single feature, clear scope | All phases (Phase 3 only if UI) |
| **Thorough** | Multi-module, architecture decisions | All phases + Agent Teams evaluation |

### Step 3: Execute the Pipeline

**Phase 0 — Session Recovery** (automatic)
If `task_plan.md` exists from a previous session, read all planning files, query MemPalace for relevant history (if configured), run the 5-Question Reboot Test (Where am I? / Where am I going? / What's the goal? / What did I learn? / What did I do?), then resume from the last checkpoint.

**Phase 1 — Specification**
Write the spec using the selected mode. Quick tasks use `/opsx:propose`; standard/thorough use the full flow with `/opsx:explore` or `/speckit.specify`. The user must explicitly confirm the spec before moving on.
**Gate G1**: User confirmed + spec aligns with constitution + inline spec review passed + scope check done.

**Phase 2 — Persistent Planning**
Generate `task_plan.md` (numbered checklist with file structure mapping + test points), `findings.md`, and `progress.md` using `planning-with-files` + `writing-plans`.
**Gate G2**: Every task has file paths + acceptance criteria + test strategy + inline plan review passed.

**Phase 3 — UI/UX Design** (conditional)
Triggered only when UI keywords are detected. Invoke `ui-ux-pro-max --design-system --persist` to generate and persist the design system (v2.5.0: 67 styles, 161 palettes, 57 fonts, 14 tech stacks, 6 specialist skills).
**Gate G3**: Pre-delivery checklist passed + user confirmed design.

**Phase 4 — Implementation**
Execute via one of two strategies (AI recommends, user picks):
- **Subagent-Driven**: Fresh subagent per task + two-stage review (spec conformance → code quality) + model selection per task role + implementer status handling (DONE/DONE_WITH_CONCERNS/NEEDS_CONTEXT/BLOCKED)
- **Executing-Plans**: Batch execution + checkpoint reviews

TDD throughout. Errors escalate through the 3-Strike protocol → `systematic-debugging`.
**Simple code + voice (pgi):** small methods, short names, plain replies — [references/simple-code-voice.md](references/simple-code-voice.md) · `.cursor/rules/04-simple-code-voice.mdc`
**Claude Senior (pgi session default):** prefer Claude-model Task / pasted Claude plan; Cursor thin apply+verify — [references/claude-senior-listener.md](references/claude-senior-listener.md).
**agent-browser (UI):** when Phase 4/G4 touches Vue/forms/pages, smoke via agent-browser (or IDE browser MCP if CDP fails) before claiming done.
**Gate G4**: All tests pass + review passed + verification evidence written to `progress.md` + `/opsx:verify` passed (if available) + MemPalace archived (if configured) + browser evidence when UI changed + **zero edge-case confirm** after renames/path moves (see `references/quality-gates.md`).

**Phase 5 — Archive**
`finishing-a-development-branch` → update all checkboxes → archive spec artifacts → final `progress.md` entry → MemPalace diary entry (if configured).

## Quality Gates

Each gate is a hard stop — nothing moves forward until all checks pass. If a gate fails, fix the issue and re-evaluate. Full gate criteria: [references/quality-gates.md](references/quality-gates.md)

## Anti-Rush Protection

If the user asks to skip the spec phase, politely decline and redirect to `/super-spec`. The whole point of this skill is preventing premature implementation.

## Reference Files

Read these as needed — they contain detailed procedures that would bloat this file:

| File | When to read |
|------|-------------|
| [references/quality-gates.md](references/quality-gates.md) | Evaluating any gate (G0-G4) |
| [references/simple-code-voice.md](references/simple-code-voice.md) | Phase 4: short names, small methods, plain voice |
| [references/synergy-patterns.md](references/synergy-patterns.md) | Understanding cross-tool integration (6 chains) |
| [references/integration-guide.md](references/integration-guide.md) | Setup, troubleshooting, dependency list |
| [references/spec-kit-workflow.md](references/spec-kit-workflow.md) | Running the Spec-Kit flow |
| [references/openspec-workflow.md](references/openspec-workflow.md) | Running the OpenSpec flow |
| [references/mempalace-integration.md](references/mempalace-integration.md) | MemPalace memory system setup + 5 integration points |
| [references/upgrade-protocol.md](references/upgrade-protocol.md) | `/super-spec upgrade` — standardized version sync procedure |
| [references/claude-senior-listener.md](references/claude-senior-listener.md) | Every-session Claude Senior + Cursor listener + agent-browser hooks |
| [assets/constitutions/openspec-constitution.md](assets/constitutions/openspec-constitution.md) | OpenSpec constitution template |
| [assets/constitutions/spec-kit-constitution.md](assets/constitutions/spec-kit-constitution.md) | Spec-Kit constitution template |

## Cross-machine sync (same Cursor account)

| Piece | Sync method | Location |
|-------|-------------|----------|
| **spec-kit** | Personal skills + local mirror | `~/.cursor/skills/spec-kit/` |
| **openspec** | Personal skills + local mirror | `~/.cursor/skills/openspec/` |
| **superpowers** | Personal skills + local mirror | `~/.cursor/skills/superpowers/` |
| **caveman** | Cursor plugin (optional) | `~/.cursor/plugins/cache/caveman/` — see `docs/CURSOR_SKILLS_SYNC.md` |
| **This orchestrator** | Personal skills + local mirror | `~/.cursor/skills/spec-kit-openspec-superpowers/` |
| **caveman-spec-triad** | Repo + `~/.cursor/skills/` mirror | `.cursor/skills/caveman-spec-triad/` |
| **impeccable** | Personal skills + local mirror | `~/.cursor/skills/impeccable/` |
| **agent-browser** | Personal skill + CLI | `~/.claude/skills/agent-browser/` · `npm i -g agent-browser` |
| **Claude Senior rule** | Repo + user rules | `.cursor/rules/08-claude-senior-listener.mdc` · `~/.cursor/rules/claude-senior-listener.mdc` |
| **Superpowers plugin** | Cursor marketplace (optional) | Install per machine |
| **Project policy** | Git only | `docs/SESSION_STATE.md`, `.cursor/rules/session-handoff.mdc` |
| **CLI tools** | Install per machine | `specify`, `openspec`, `agent-browser` |

### Setup on a new PC

1. Sign in to the **same Cursor account**.
2. **Settings → Sync** — enable skills sync if available.
3. Confirm `~/.cursor/skills/` contains: `spec-kit/`, `openspec/`, `superpowers/`.
4. Install CLIs:
   ```bash
   uv tool install specify-cli --from git+https://github.com/github/spec-kit.git
   npm install -g @fission-ai/openspec@latest
   ```
5. Optional: install **Superpowers** and **Caveman** plugins in Cursor.
6. Clone `pgi-core-frontend`; if skills missing: `cp -r .cursor/skills/* ~/.cursor/skills/`

## Repo policies

**This repo (laravel13.x)** — read before mode auto-selection:

- [laravel13-x-policy.md](laravel13-x-policy.md) — greenfield → Spec-Kit + Superpowers; post-MVP → OpenSpec + Superpowers; never both SDD layers on one feature
- Skill `triad-router` — manual tool-choice router (Spec-Kit vs OpenSpec vs Superpowers)
- On `continue`: read `docs/SESSION_STATE.md` first

**pgi-core-frontend** (when working that repo / synced skills hub):

- [pgi-core-policy.md](pgi-core-policy.md) — OpenSpec default; PL 7-product scope; no auto-commit; Claude Senior + agent-browser session defaults
- Active change: `openspec/changes/phase-ii-quotation-slice-only/`
- Session: Claude Senior listener ON + agent-browser for UI verify — [references/claude-senior-listener.md](references/claude-senior-listener.md)

### Invocation

#### Full stack (Caveman + triad manuals — no auto SDD)

```text
/Caveman spec kit Openspec Superpower
```

```text
Use caveman spec kit openspec superpower:
```

Loads **caveman-spec-triad** skill. Does **not** run `/speckit.*` or `/opsx:*` until user asks.

#### Router only

```text
Use spec-kit-openspec-superpowers: verify my triad setup on this machine.
```

#### Single tools

```text
Use openspec: /opsx:apply <change-name>.
```

```text
Use superpowers: TDD for the next task.
```

```text
Use caveman: talk like caveman for the rest of this session.
```

### Reference links

- Spec-Kit: https://github.com/github/spec-kit
- OpenSpec: https://github.com/Fission-AI/OpenSpec
- Superpowers: https://github.com/obra/superpowers
- Caveman: https://github.com/JuliusBrussee/caveman
- Sync manifest: `docs/CURSOR_SKILLS_SYNC.md`
