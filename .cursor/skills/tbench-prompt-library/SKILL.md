---
name: tbench-prompt-library
description: >-
  Routes Cursor work through distilled Terminal-Bench trial patterns (harness +
  per-task strategy packs). Matches user tasks to tbench.ai waffle tasks, picks
  quality vs speed tier, loads combo with terminal-bench and harbor-trajectory.
  Use for self-improvement prompts, fix issue, tbench, waffle, Hub trial URLs,
  or task slugs like wdm-design, photonic-waveguide-routing, coq-block-bound.
---

# Terminal-Bench prompt library (combo)

Store **behaviors and strategies** distilled from public Hub trajectories — not full benchmark dumps.

Combo stack (load what you need):

| Layer | Skill | Role |
|-------|-------|------|
| 1 | **this skill** | Match task → pack → apply harness |
| 2 | `terminal-bench` | Waffle / leaderboard / run context |
| 3 | `harbor-trajectory` | Read Hub Trajectory #1/#2/#3 when importing |

Library root: `.cursor/skills/tbench-prompt-library/`

## When to run (before improvising “fix issue …”)

1. User asks to fix, implement, or debug something **non-trivial** (not a one-line typo).
2. Parse intent for **domain tags** (see [catalog.yaml](catalog.yaml)).
3. If a pack matches → apply [harness/default.md](harness/default.md) + task `strategy.md`.
4. If user says **`tbench quality`** or **`tbench speed`** → run [judge.md](judge.md) pick.
5. If no pack matches → **harness only** (still better than raw chat).

Confirm in one line: `TB pack: {slug|harness-only} · tier: {quality|speed|default}`

## Router (match user work → TB task)

Read [catalog.yaml](catalog.yaml). Score each task:

- +3 tag exact match in user message or open files path
- +2 category match (science, frontend, security, ml, …)
- +1 keyword from `meta.yaml` `keywords`

Pick highest score if ≥ 2; else harness-only.

| User situation | Likely pack |
|----------------|-------------|
| Meep / FDTD / photonics / WDM / silicon | `wdm-design` |
| Waveguide routing / photonic layout | `photonic-waveguide-routing` (stub — import trial) |
| Coq / proof / block bound | `coq-block-bound` (stub) |
| Next.js perf / CLS | `nextjs-performance`, `cumulative-layout-shift` (stub) |
| Generic Laravel bugfix | harness-only + existing repo rules |

## Apply order (what Cursor “native” means)

Cursor does not auto-read YAML. **You** (the agent) load files when this skill triggers:

```
1. harness/default.md          ← always (frontier agent behaviors)
2. tasks/{slug}/strategy.md  ← if matched
3. tasks/{slug}/trials.yaml  ← which Hub run to cite; tier hints
```

Wrap the **user’s real ask** in a clear goal block at the top of your thinking — same shape as TB `<user_query>`, but **their** words, not `instruction.md`.

Do **not** paste TB task instructions into unrelated repo work.

## Quality vs speed judge

Leaderboard 4.0 (reference — refresh from [tbench.ai](https://www.tbench.ai/?view=leaderboard)):

| Tier | When | Prefer |
|------|------|--------|
| **quality** | Hard / frontier / science / user says quality | High resolution models (Opus 5, Fable 5, GLM 5.3) |
| **speed** | Quick fix / spike / user says speed | Lower cost (Luna, Terra) if pack has no slow trial |
| **default** | Unspecified | quality for ambiguous; speed only if user time-boxes |

Per-**task** override beats global leaderboard: see `trials.yaml` `tier` on each stored trial (e.g. grok wdm-design = quality, 36m).

Detail: [judge.md](judge.md)

## Grow the library

New public Hub trial:

1. Open Trajectory → note reward, model, agent, timing.
2. Follow [import-trial.md](import-trial.md).
3. Add row to `catalog.yaml` + `tasks/{slug}/`.

Never commit: full `trajectory.json`, oracle solutions, or canary benchmark text.

## Waffle workflow (user)

1. [tbench.ai/?view=waffle](https://www.tbench.ai/?view=waffle) — pick task row + model column.
2. Click white cell → Hub trial → Trajectory tab.
3. Import distilled pack (not copy-paste 63 steps into chat).

## Canary

Do not paste Terminal-Bench canary strings or full task instructions into issues, PRs, or public docs.
