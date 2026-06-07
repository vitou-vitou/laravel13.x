# Proposal: Docker Production Hardening — dashboard-v1

**Status:** Awaiting approval
**Owner:** Engineering
**Reviewers:** CEO / CTO
**Date:** 2026-06-07
**Decision required by:** before public/customer launch

---

## 1. Summary

The dashboard-v1 container stack is functionally production-ready: it builds,
boots, passes health checks, and runs on Coolify. Before exposing it to
customers we must close five operational gaps. None are architectural; all are
hygiene. Estimated total effort: **~2 engineering days**.

Recommendation: **approve staging now**, fund the hardening sprint, gate public
launch on items #1 and #2.

---

## 2. Risks, options, cost, rollback

### Risk #1 — Real secrets committed to git  🔴 BLOCKS PROD

**Problem.** `.env.example` contains live-looking values: `APP_KEY`,
`REVERB_APP_SECRET`, and a demo login password. They are in git history. If any
are reused in production, an attacker can forge sessions/encrypted payloads.

**Options.**
| Option | Effort | Residual risk |
|--------|--------|---------------|
| A. Replace values with placeholders + rotate keys; leave history | 1 hr | Secrets still in old history (mitigated if rotated) |
| B. Option A **plus** scrub history (`git filter-repo`) + force-push | 0.5 day | Lowest; requires team re-clone |

**Recommended:** A now (rotate makes leaked values worthless), B if repo will go
public or external parties have access.

**Cost:** 1 hr (A) / 0.5 day (B).
**Rollback:** key rotation is forward-only; keep old keys 24 h in a secrets
manager for emergency revert, then delete.

---

### Risk #2 — No database backup strategy  🔴 BLOCKS PROD

**Problem.** Postgres data lives only in the `pgdata` Docker volume. Host loss,
disk failure, or `docker compose down -v` = total, unrecoverable customer data
loss.

**Options.**
| Option | Effort | Notes |
|--------|--------|-------|
| A. Scheduled `pg_dump` to offsite object storage (S3/B2) | 0.5 day | Simple, restore-tested |
| B. Managed Postgres (move DB off-container) | 1–2 days | Best durability, monthly cost |

**Recommended:** A for launch; revisit B as customer count grows.

**Cost:** 0.5 day.
**Rollback:** additive (backup job only); no impact on running stack.

---

### Risk #3 — Containers run as root  🟠 PRE-PUBLIC

**Problem.** No `USER` directive; supervisor/nginx master run as root. Container
escape → host compromise. Fails common compliance checks.

**Options.**
| Option | Effort | Notes |
|--------|--------|-------|
| A. Non-root `USER`, bind nginx to 8080, drop capabilities | 0.5 day | Full fix |
| B. Keep web root (nginx drops workers), non-root for worker/reverb/scheduler | 2 hr | Partial |

**Recommended:** A.

**Cost:** 0.5 day.
**Rollback:** revert Dockerfile + compose port map; rebuild.

---

### Risk #4 — Unpinned base images, no vulnerability scan  🟠 PRE-PUBLIC

**Problem.** Base images use floating tags (`php:8.4-fpm-alpine`, etc.). Builds
are non-reproducible and CVEs ship unnoticed.

**Options.**
| Option | Effort | Notes |
|--------|--------|-------|
| A. Pin runtime base by digest + add Trivy/Docker Scout gate in CI | 0.5 day | Recommended |
| B. Scan only, no pinning | 2 hr | Catches CVEs, not drift |

**Recommended:** A.

**Cost:** 0.5 day.
**Rollback:** unpin tag; remove CI step.

---

### Risk #5 — Migrations run on web boot  🟡 BEFORE SCALING

**Problem.** The `web` role runs `migrate` at startup. Safe at one replica.
Scaling web to >1 = concurrent migrations = possible corruption.

**Options.**
| Option | Effort | Notes |
|--------|--------|-------|
| A. Dedicated release/migrate job (Coolify pre-deploy), drop from web boot | 0.5 day | Standard pattern |
| B. Leave as-is, cap web at 1 replica | 0 | Blocks horizontal scaling |

**Recommended:** A when horizontal scaling is on the roadmap; B acceptable today.

**Cost:** 0.5 day.
**Rollback:** restore migrate line in entrypoint.

---

## 3. Plan & sequencing

| Phase | Items | Effort | Gate |
|-------|-------|--------|------|
| Now | Approve staging use | — | — |
| Sprint 1 (pre-launch) | #1, #2 | ~1 day | Required for public |
| Sprint 1 (pre-public) | #3, #4 | ~1 day | Required for external/compliance |
| Later (pre-scale) | #5 | 0.5 day | Before web replicas > 1 |

**Total: ~2.5 engineering days.**

---

## 4. Decision

- [ ] Approve staging use now
- [ ] Approve hardening sprint (#1–#4)
- [ ] Defer #5 to scaling milestone

Signature / date: ______________________
