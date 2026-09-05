---
name: selfhost-proxy-stability
description: >-
  Troubleshoot and harden self-hosted proxies (LLM API gateways like
  LiteLLM / One API / New API, or HTTP reverse proxies like Caddy / Traefik /
  nginx) for very-stable operation. Covers the six failure classes that take
  self-hosted proxies down — AV TLS interception, port conflicts, hung
  processes, upstream flaps, stream drops, cold starts — with a
  cheap-to-expensive diagnosis ladder and a stability checklist distilled
  from battle-tested tools (LiteLLM fallbacks, One API channel health,
  Caddy auto-TLS). Use when a self-hosted proxy / gateway / 9router-class
  service is slow, flapping, 502ing, or must run unattended for months.
---

# Self-hosted proxy stability

Distilled from LiteLLM, One API/New API, and Caddy operating practice, plus
one month of live failure evidence on Windows (9router-class LLM proxy):
AV TLS interception, port conflicts 10100/10443, hung node processes,
upstream model flaps, parallel-fallback racing.

## Six failure classes (find yours first)

| Symptom | Class | First check |
|---|---|---|
| Works in curl, fails in client / weird TLS errors | **AV TLS interception** (Kaspersky/ESET) | Probe with `-k` + compare |
| Bind error / connection refused after reboot | **Port conflict** | Listen check on the port |
| Was working, now hangs forever | **Hung process** | Process tree + kill orphan |
| Intermittent 5xx, some models fine | **Upstream flap** | Direct upstream probe per model |
| Starts strong, dies mid-answer | **Stream drop** | Timeout during long chunks |
| First request after idle is slow / fails | **Cold start** | Timed probe before/after idle |

## Diagnosis ladder (cheap → expensive, stop at first hit)

1. **Timed local probe** — proves proxy up + measures latency:
   ```bash
   curl -s -m 10 -o /dev/null -w "HTTP %{http_code} in %{time_total}s\n" http://localhost:PORT/v1/models
   ```
2. **Port listen check** — is anything bound, who owns it:
   ```bash
   netstat -ano | grep -E "PORT" | grep LISTEN
   ```
3. **Process check** — hung or orphaned workers:
   ```bash
   tasklist | grep -iE "node|proxy"
   powershell -NoProfile -Command "Get-CimInstance Win32_Process -Filter \"Name='node.exe'\" | Select ProcessId,CommandLine"
   ```
4. **Direct upstream probe** — bypass proxy, hit upstream model API. Same
   failure = upstream problem, not yours.
5. **Log tail** — structured logs beat everything; no logs = fix that first.

## Hardening checklist (battle-tested behaviors)

From **LiteLLM** (USA/global, huge deploy base):
- Fallback chain: model list in priority order, auto-advance on failure
- Exponential backoff **with jitter** on retries (no thundering herd)
- Per-request hard timeout — never rely on client timeout
- Cooldown: after N failures, mark upstream down for T seconds (circuit breaker)

From **One API / New API** (China, massive deploy base):
- Channel health tracking — auto-disable channel after repeated failures
- Weight-based load balancing across healthy channels
- Per-key quota + rate limit — one abusive client can't starve the rest

From **Caddy**:
- Active health checks (`/health` probe on interval), passive too
- Graceful reload — config changes never drop in-flight streams
- Auto-TLS with ACME — never hand-roll certs

Universal:
- **Watchdog restart** — systemd / NSSM / pm2 keeps it alive after crash
- **Port pinning** — fixed port in config, never random
- **AV exclusion** — whitelist proxy dir + port in Kaspersky/ESET, or AV off for loopback (source of "works in curl, fails in app")
- **Structured logs** (JSON) — every request: upstream, latency, status, retry count
- **Hybrid fallback** (proven on 9router): fire first 2 upstreams in parallel,
  race them, fall back to remaining sequentially on both-fail

## Stability SLO (prove it, don't feel it)

Target: **99.9% over 30 days** = 43.2 min allowed downtime.

- Probe `/v1/models` (or `/health`) every 60s from a cron/task — log latency
- Alert if: 3 consecutive fails (fast burn) or >5% budget in a day (slow burn)
- Kill-switch test monthly: stop process, confirm watchdog restarts <30s

## Windows shell traps (from live evidence)

- Inline `python -c` dies silently — write script file, run `python file.py`
- `python` heredoc works but cp1252 console kills unicode — `sys.stdout.reconfigure(encoding='utf-8', errors='replace')`
- Copy locked sqlite (proxy state DBs) with `-wal -shm` to temp before query
- `HEAD^` mangles — use `HEAD~1`; long inline escaping — script file

## When composed with other skills

| Situation | Load |
|---|---|
| Defining uptime target / alert thresholds | `slo-architect` |
| Testing failover under fault | `chaos-engineering` |
| Metrics/logs/traces design | `observability-designer` |
| Public exposure of local proxy | `expose-localhost` (ngrok) |
