# Agent workflow (laravel13.x)

Shared language for how this repo uses Matt Pocock–style engineering skills. Settled via `/grill-with-docs` smoke Round 1 (2026-09-20). Product/insurance domain language does **not** live here — keep that for a later feature grill or a separate context.

## Language

**Skill**:
A named agent procedure with a `SKILL.md` that changes behavior (gates, phases, artifacts). Invoked by slash command or explicit attach.
_Avoid_: prompt, rule, tip

**Smoke**:
The smallest real run that proves a Skill loaded and followed its first gate, judged by whether it produced that Skill’s artifact — not a full feature build.
_Avoid_: demo, dry run, hello world (unless that is the Skill’s own deliverable)

**Paper trail**:
Durable docs the Skill leaves in-repo so later sessions share vocabulary — primarily this `CONTEXT.md` and, when warranted, ADRs under `docs/adr/`.
_Avoid_: chat log, session summary, handoff (handoff is a different Skill)

## Settled decisions

- **Glossary scope**: agent-workflow only at root `CONTEXT.md` (not full product domain; not CONTEXT-MAP yet).
- **Pass criterion**: a Skill “works” when it produces its artifact (e.g. this file, a red/green test), not merely that the file loaded.
- **Setup**: whether `/setup-matt-pocock-skills` already ran for this clone is still unknown — run that Skill as the next smoke before ticketed `/implement` work.
