---
name: system-study-packet
description: >-
  Generates a paired codebase study pack: (1) 8-principle learning packet and
  (2) system decomposition doc in Markdown. Explores routes, services, models,
  and tests from the real repo. Use when the user asks for a study packet,
  8-principle study, system map, decomposition doc, learn this app/codebase,
  or /system-study-packet.
---

# System Study Packet (8-Principle + Decomposition)

Produces **two Markdown files** for one software system:

| Output | Purpose |
|--------|---------|
| `{slug}-8-principle-study.md` | Learn + remember (quizzes, flashcards, spacing) |
| `{slug}-decomposition.md` | Read + navigate (layers, flows, status matrix) |

Default output folder: `{project-root}/docs/study-packets/`

## When to use

- User wants to **understand** or **document** an app they built or inherited.
- User says: study packet, decomposition, system map, 8 principles, learn this codebase.
- After MVP/feature work — refresh docs from **actual code**, not specs alone.

## Before writing (one round)

Confirm or infer:

1. **Scope** — project path (e.g. `examples/kindly-e-commerce-1122`) or monorepo subfolder.
2. **Slug** — short filename prefix (e.g. `kindly-ecommerce`).
3. **Learner level** — beginner | student (default) | professional.
4. **Format** — Markdown only unless user asks for docx/html.

If user says "continue" on a known project, read `docs/NEXT_SESSION.md`, `docs/SESSION_STATE.md`, routes, and test count first.

## Workflow

```
Task Progress:
- [ ] 1. Explore codebase (read-only)
- [ ] 2. Write decomposition MD
- [ ] 3. Write 8-principle study MD (aligned with decomposition)
- [ ] 4. Verify facts against code; link both files to each other
```

### Step 1 — Explore (required)

Gather facts from the repo; do not invent architecture.

| Source | What to extract |
|--------|-----------------|
| `routes/web.php`, `routes/api.php` | Endpoints, middleware |
| `app/Http/Controllers` | Entry points |
| `app/Services`, `app/Actions` | Business logic |
| `app/Models` | Entities, relationships |
| `database/migrations` | Schema truth |
| `tests/Feature` | Behavior contracts, test count |
| `docs/NEXT_SESSION.md`, `.specify/specs` | Done vs planned |

Note: payment/auth boundaries, state machines, external integrations (Stripe, webhooks, queues).

### Step 2 — Decomposition MD

Follow [decomposition-template.md](decomposition-template.md).

Must include:

- Context boundaries (in/out of scope)
- Subsystem breakdown
- Layer diagram (presentation → infrastructure)
- Data model + state machine (if applicable)
- Request-flow traces for 3–5 critical paths
- Route table (method, path, middleware)
- Test map
- **Status matrix: Done / Doing / Not started** per subsystem
- File index + suggested reading order

Save as: `docs/study-packets/{slug}-decomposition.md`

### Step 3 — 8-Principle study MD

Follow [packet-structure.md](packet-structure.md) (all 8 principles, both steps).

Rules:

- Plain English; define terms on first use.
- Principle headings must be visible (`Principle 1 — Map of the system`, etc.).
- Quiz + flashcard answers must come **only** from content taught in the packet.
- Align map/flows with decomposition doc (same names for subsystems).
- Close decomposition with link to study packet; study packet links back to decomposition.

Save as: `docs/study-packets/{slug}-8-principle-study.md`

### Step 4 — Verify

- [ ] Route names and status values match code
- [ ] Test count matches `php artisan test` (or project test command) if run
- [ ] No claim that a feature exists unless file/route/test proves it
- [ ] Status matrix matches handoff docs where they exist

## Output naming

| Input | Slug example |
|-------|----------------|
| `examples/kindly-e-commerce-1122` | `kindly-ecommerce` |
| `examples/booking-v1` | `booking-v1` |

## Invocation examples

User: "Create study packet for kindly e-commerce"  
→ Read skill + explore `examples/kindly-e-commerce-1122` → write both MD files under `docs/study-packets/`.

User: "Update decomposition only for booking-v1"  
→ Regenerate `{slug}-decomposition.md` only; note date in footer.

User: "/system-study-packet this repo"  
→ Use workspace root as scope; slug from folder name.

## Cross-machine use (Cursor)

This skill lives in **personal** skills:

`~/.cursor/skills/system-study-packet/`

- Available in **all projects** on machines where you are signed into the same Cursor account.
- Enable **Settings Sync** in Cursor if skills/rules should follow your account across PCs.
- Optional: copy the same folder to `{repo}/.cursor/skills/system-study-packet/` and commit for team/git backup (project scope).

Do **not** put skills in `~/.cursor/skills-cursor/` (reserved for Cursor built-ins).

## Reference files

- [packet-structure.md](packet-structure.md) — 8-principle section blueprint
- [decomposition-template.md](decomposition-template.md) — decomposition outline
- [examples.md](examples.md) — kindly-ecommerce reference paths

## Anti-patterns

- Do not generate only a bullet list — both full documents are required unless user asks for one.
- Do not teach generic Laravel; teach **this** system's flows.
- Do not mark features Done in the matrix without code or tests.
