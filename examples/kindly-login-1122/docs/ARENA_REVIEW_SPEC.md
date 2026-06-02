# Arena review — Spec (Prompt A)

**Date:** 2026-06-01  
**Model:** Anthropic claude-sonnet-4-6 (Arena Direct)  
**Chat:** https://arena.ai/c/019e83a3-aa87-7557-bbe4-face33a778ca

---

## Arena response (Sonnet 4.6)

### 1. Missing Security Invariants (max 5)

1. No CSRF token validation requirement stated — session auth without explicit CSRF enforcement on POST routes leaves the spec ambiguous about whether Laravel's VerifyCsrfToken middleware is mandated.
2. Rate limit scope undefined — "5 fails" doesn't specify throttle key (by IP, by email, or both). IP-only allows credential stuffing; email-only enables account enumeration via lockout timing.
3. No session fixation protection on registration — spec requires session regenerate on login but omits it for the registration+auto-login flow.
4. Password policy absent — no minimum length, complexity, or bcrypt cost factor defined.
5. No absolute session lifetime or idle timeout defined — logout invalidates session but long-lived sessions have no expiry invariant.

### 2. Ambiguous Acceptance Criteria (max 5)

1. "Generic auth.failed on email" — unclear if this covers password reset flow or login only.
2. "Secure cookies in prod" — no config key named (`SESSION_SECURE_COOKIE=true`).
3. "Dashboard protected" — single route vs middleware group not specified.
4. "Rate limit (5 fails)" — no lockout duration, HTTP status (423 vs 429), or unlock behavior.
5. "Session invalidate on logout" — ambiguous whether `regenerateToken()` is required post-logout.

### 3. P1 Story to Defer (1-week MVP)

**Arena suggested defer:** Rate limiting (5-fail lockout) — due to throttle-key QA risk.

**Project decision:** Keep rate limiting (already implemented + tested in `LoginSecurityTest`). Spec updated to document Breeze throttle key `email|IP` and lockout copy via `auth.throttle`.

---

## Merged into spec (2026-06-01)

- Throttle key documented: `Str::lower(email)|ip` (Breeze `LoginRequest`)
- Registration session regeneration noted as invariant (Breeze `RegisteredUserController` uses same session flow as login)
- `SESSION_SECURE_COOKIE` named in production checklist (plan.md)
