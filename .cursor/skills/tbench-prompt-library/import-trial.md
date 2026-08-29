# Import a Hub trial into the library

Do this after a **reward 1.0** (or instructive fail) trial on Harbor Hub.

## 1. Capture metadata

From trial page header:

- job id, trial id, public URL
- dataset/task slug (`terminal-bench/wdm-design`)
- agent + model
- reward, agent execution time, verifier time

## 2. Read Trajectory (do not dump raw)

| Step | Extract | Write to |
|------|---------|----------|
| #1 system | Only `<work_policy>`, `<tool_calling>`, `<communication>` bullets | Extend `harness/default.md` if new |
| #2 user | Skip — TB task text stays on Hub | — |
| First 5 agent steps | First tools, artifact paths, planning order | `tasks/{slug}/strategy.md` |
| Last 5 agent steps | Verifier prep, final artifacts | same |
| Fail trials | Where loop broke | `strategy.md` § Pitfalls |

Max ~80 lines per task strategy. No full trajectory paste.

## 3. Files to create/update

```
tasks/{slug}/
  meta.yaml       # tags, keywords, category
  strategy.md     # distilled playbook
  trials.yaml     # hub links + tier
catalog.yaml      # register task
```

### trials.yaml shape

```yaml
trials:
  - id: f9210dd1-a93c-4bad-8cec-2c147bcf6801
    job: 2c89a14f-d14a-4ea8-ad8a-d08d90c67a5d
    hub_url: https://hub.harborframework.com/jobs/.../trials/...
    model: xai/grok-4.6
    agent: grok-build@1.0.5
    reward: 1.0
    tier: quality
    agent_execution_min: 37
    steps: 63
    note: one line — what worked
```

## 4. Stub → live

Set `status: live` in catalog.yaml; replace `tasks/_stub/strategy.md` pointer with real `tasks/{slug}/strategy.md`.

## 5. Validate

- No canary GUID blocks in committed files
- No copy of `instruction.md` or oracle `solve.sh`
- Skill description in `catalog.yaml` tags matches `meta.yaml`

## Local trajectory (optional)

If you ran `harbor run` locally:

```bash
harbor view jobs
# trial agent/trajectory.json — same ATIF fields as Hub
python -m harbor.utils.trajectory_validator path/to/trajectory.json
```

Same extract rules; do not commit raw JSON unless repo policy allows (default: no).
