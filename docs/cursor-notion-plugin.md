# Notion Cursor plugin (laravel13.x)

**Install:** Cursor → Plugins → **Notion** ([makenotion/cursor-notion-plugin](https://github.com/makenotion/cursor-notion-plugin))

**MCP:** `https://mcp.notion.com/mcp` via `plugin-notion-workspace-notion` — **OAuth**, not `~/.cursor/mcp.json`.

Full stack context: [`MCP_SERVERS.md`](MCP_SERVERS.md)

---

## vs git docs (important)

| Source of truth | Path |
|-----------------|------|
| Session handoff, project status | `docs/SESSION_STATE.md`, `examples/*/docs/NEXT_SESSION.md` |
| Specs / OpenSpec | `openspec/`, `.specify/` |
| Notion | Tasks, meeting notes, personal boards — **when you ask** |

Do not replace git handoff files with Notion-only updates.

---

## First-time auth

1. Enable **notion** in Cursor → Settings → MCP
2. Complete OAuth when prompted (or ask agent to authenticate Notion MCP)
3. Reload MCP if tools do not appear

---

## Skills bundled with plugin

| Skill | When to use |
|-------|-------------|
| `search` / `find` | Locate specs or notes before coding |
| `knowledge-capture` | Export session insights to Notion wiki |
| `spec-to-implementation` | Break a Notion spec into tasks |
| `tasks-plan` / `tasks-build` | Work from a Notion task URL |
| `tasks-explain-diff` | Document a completed change in Notion |
| `create-task` | Quick task row |
| `tasks-setup` | Connect a task database (one-time) |

---

## Example prompts

```text
Search Notion for marketplace roadmap

Capture to Notion: decisions from today's marketplace-v2 session (bullet summary only)

Create Notion tasks from my OpenSpec money-and-trust follow-ups

Explain this PR in a Notion page for the team
```
