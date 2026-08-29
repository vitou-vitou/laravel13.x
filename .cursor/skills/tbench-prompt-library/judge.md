# Judge: quality vs speed

Use when user says `tbench quality`, `tbench speed`, or when two packs/trials could apply.

## Global tier (leaderboard 4.0)

From [catalog.yaml](catalog.yaml) `leaderboard_4_0`:

- **quality** → Opus 5, Fable 5, GLM 5.3 class (highest resolution on aggregate).
- **speed** → Luna, Terra class (lower cost; accept lower resolution).

Leaderboard is **across all 66 tasks**. A fast cheap model can still fail a hard row on the waffle.

## Per-task tier (preferred)

Read `tasks/{slug}/trials.yaml`:

| Field | Use |
|-------|-----|
| `reward` | Must be 1.0 for “copy this strategy” |
| `tier` | `quality` \| `speed` — author's label for that trial |
| `agent_execution_min` | speed proxy (lower = faster) |
| `hub_url` | provenance |

**Rule:** If a task has a reward-1.0 trial marked `quality`, use its `strategy.md` for hard work even if user did not say quality. Offer speed path only when user time-boxes or trial tier is `speed`.

## Waffle visual (quick read)

On [?view=waffle](https://www.tbench.ai/?view=waffle):

- Row mostly **white** across top models → task may be saturated; harness matters less than execution.
- Row **mixed dark/white** on frontier columns → task is frontier; use **quality** pack + full harness.
- Column mostly **red/orange** → agent/harness mismatch; do not copy that column's style blindly.

## Cursor mapping (honest limits)

| TB has | Cursor has |
|--------|------------|
| grok-build system #1 | `harness/default.md` + repo rules |
| instruction.md in #2 user | **Your** issue/spec as goal block |
| 63 agent steps | You run tools until done when |

You cannot run Meep/Modal from Cursor unless the repo actually has that stack. Strategy = **order of operations**, not literal `/app/` paths unless you are in that container.

## Pick one line output

```text
TB tier: quality | speed
Pack: {slug}
Trial ref: {hub_url or local trials.yaml id}
Reason: {one sentence}
```
