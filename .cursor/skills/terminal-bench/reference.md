# Terminal-Bench — Harbor task + run reference

Read this when contributing a task or wiring Harbor flags. Keep [SKILL.md](SKILL.md) for waffle/leaderboard reading.

## Dataset

| Item | Value |
|------|--------|
| Hub | `terminal-bench/terminal-bench` |
| Latest | [hub.harborframework.com/.../latest](https://hub.harborframework.com/datasets/terminal-bench/terminal-bench/latest) |
| Leaderboard tab | `?tab=leaderboard&leaderboard=4-0-0` |
| Task count (4.0 slice) | 66 published tasks on Hub at skill authoring; **re-check Hub**, do not hardcode |
| Roadmap | [github.com/orgs/harbor-framework/projects/1](https://github.com/orgs/harbor-framework/projects/1/views/1) |
| Discord | [discord.gg/ZvcWupVXjz](https://discord.gg/ZvcWupVXjz) |

Example 4.0 task slugs (not exhaustive): `layout-config-recreation2`, `mp-checkpoint-consolidation`, `react-lead-form`, `coq-block-bound`, `risk-scorer-replay`, `atrx-vep-crispr`, `cumulative-layout-shift`, `distributed-dedup`, `uefi-bootkit`, `shadow-relay`, `wdm-design`.

## Harbor CLI map

Prefer long flags in scripts. Short flags match [tbench.ai/run](https://www.tbench.ai/run).

| Flag | Meaning |
|------|---------|
| `-d` / dataset | `terminal-bench/terminal-bench@latest` |
| `-p` | Local path (`tasks/<name>` or a dataset dir) |
| `-a` / `--agent` | `oracle`, `claude-code`, others in Harbor docs |
| `-m` / `--model` | Provider slug, e.g. `anthropic/claude-fable-5` |
| `-e` / `--env` | `modal`, `daytona`, `docker`, … |
| `-n` / `--n-concurrent` | Parallel trials |
| `-k` | Repeats (oracle sanity is often `-k 5`) |
| `--ak` | Agent kwargs, e.g. `reasoning_effort=max` |
| `--upload` | Upload when the run finishes |

GPU tasks in this release: **one H100** max. Local Docker without GPU will fail those tasks.

```toml
[environment]
gpus = 1
gpu_types = ["H100"]
```

## Task folder

Created by `harbor tasks init … -p tasks/`:

```
tasks/<task-name>/
├── README.md
├── instruction.md
├── task.toml
├── environment/
│   ├── Dockerfile
│   └── data/          # optional; never put oracle/tests here
├── solution/
│   └── solve.sh       # Oracle; mounted at /solution/
└── tests/
    ├── Dockerfile     # verifier image (separate container)
    └── test.sh
```

Harbor Task Format: [harborframework.com/docs/task-format](https://harborframework.com/docs/task-format)

### task.toml essentials

```toml
artifacts = ["/app/output.json"]

[metadata]
author_name = ""
author_email = ""
category = ""          # Science, Software, ML, Operations, Security, Hardware, Media
subcategory = ""
tags = []
expert_time_estimate_hours = 0

[verifier]
timeout_sec = 120.0
environment_mode = "separate"

[agent]
timeout_sec = 120.0

[environment]
build_timeout_sec = 600.0
cpus = 1
memory_mb = 2048
storage_mb = 10240
gpus = 0
allow_internet = true
```

`instruction.md` must end with (CI `check-instruction-suffix`):

```
You have N seconds to complete this task. Do not cheat by using online solutions or hints specific to this task.
```

`N` = `[agent].timeout_sec`. Use absolute paths (`/app/output.txt`).

### Verifier rules

- Verifier is a **separate** container. Agent never sees `tests/`.
- Bake tests into `tests/Dockerfile` (`COPY . /tests/`). Harbor does not upload `tests/` at verify time.
- Verifier may read: `artifacts = [...]`, files in the verifier image, persistent sidecars in `environment/docker-compose.yaml`.
- Pre-create parent dirs for every artifact path.
- Ground truth lives in `tests/`, never in `environment/Dockerfile`.

### Difficulty (what maintainers reject)

- Arbitrary complexity or “gotcha” filters against today’s models
- Offline-only as the hardness mechanism
- Solutions easily googled (except this repo’s oracle)
- Agent-visible answers in the environment image

Good hardness: long horizon, rich/dynamic environments, real domains, cross-domain expertise, trial-and-error loops.

## Local quality loop

```bash
export ANTHROPIC_API_KEY=...
export OPENAI_API_KEY=...   # as needed

harbor check "tasks/<task-name>" -m anthropic/claude-opus-4-8
harbor run -p "tasks/<task-name>" -a oracle
harbor tasks start-env -p "tasks/<task-name>" -e docker -a -i
```

Oracle **reward = 1.0** before PR. Full contributing guide: [CONTRIBUTING.md](https://github.com/harbor-framework/terminal-bench/blob/main/CONTRIBUTING.md)

## Reading rates

Leaderboard **resolution rate** is share of tasks (or trials — match the column tooltip on the live page) the agent finished under the verifier. Do not convert waffle squares into a published rate by hand unless you count every trial in the filtered view.

Terminal-Bench-Science 0.1 is a **different** dataset (70 science tasks, 3 trials each). Do not mix those percentages into the 4.0 waffle.
