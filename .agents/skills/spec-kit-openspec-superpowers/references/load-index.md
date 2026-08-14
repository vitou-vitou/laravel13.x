# Load index (triad command)

**Triggers:** `load index` · `load project index` · `load <domain> index` · `index first` · `later give a task`

**Purpose:** Orient the agent before a task. **Do not implement.** Wait for the next user message.

## When

| Phrase | Scope |
|--------|--------|
| `load index` / `load project index` | Whole repo |
| `load Dramabox index` / `load <Name> index` | One domain/plugin/module |
| `load index, later give a task` | Orient only; stop after brief map |

## Procedure (read-only)

1. Confirm stack line + `AFK: on/off` (do **not** auto-implement).
2. Detect branch + short `git status -sb` (context only).
3. Map **entrypoint** (`main`, `app/`, `routes`, etc.).
4. Map **layers** (UI / workers / core / plugins / DB / tests).
5. List **domain artifacts**: plugin/module file, OpenSpec change folder, proof scripts, `progress.md`.
6. One-line **flow** for that domain (URL → scrape → resolve → download).
7. Note **open gaps** from last `progress.md` (VIP, stubs, etc.).
8. End with: `Index loaded. Send the task when ready.`

## Output shape (keep short)

```text
**Index loaded.** Branch: <name>

| Piece | Location |
|-------|----------|
| Plugin/module | path |
| Proof | path |
| Spec | openspec/changes/... |

**Flow:** …
**Open gap:** …
Send the task when ready.
```

## Rules

- No code edits, no commits, no installs unless user asks.
- Prefer existing `openspec/changes/*/progress.md` over re-deriving.
- Domain index = deep on that slice; project index = wide and shallow.
