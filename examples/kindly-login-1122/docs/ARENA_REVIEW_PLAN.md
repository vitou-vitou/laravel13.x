# Arena review — Plan (Prompt B)

**Date:** 2026-06-01  
**Model:** Anthropic claude-sonnet-4-6 (Arena Direct)  
**Chat:** https://arena.ai/c/019e83a3-aa87-7557-bbe4-face33a778ca

---

## Arena response (Sonnet 4.6)

### 1. Security holes (max 5)

1. **SESSION_SECURE_COOKIE is opt-in via env** — misconfigured production `.env` ships insecure cookies; document `config/session.php` + `SESSION_SECURE_COOKIE=true`.
2. **Sessions table with no cleanup** — database session driver accumulates stale rows; schedule `session:gc` or equivalent in production.
3. **Post-MVP OAuth scope** — document that current Breeze routes are session-only; OAuth is OpenSpec later.
4. **Arena docs go stale** — re-review trigger when auth routes/middleware/session config change.
5. **No CSP / X-Frame-Options** — post-MVP hardening; note in plan backlog.

### 2. Test gaps (max 5)

1. **No test that `session()->regenerate()` runs** — fixation protection unverified on login/register.
2. **Branding test doesn't assert cookie flags** — optional `Secure`/`HttpOnly`/`SameSite` on session cookie in feature test.
3. **Rate limit key** — composite `email|IP` is implemented; document in spec (not IP-only).
4. **Logout invalidation** — test that post-logout session cannot access `/dashboard`.
5. **Session driver in tests** — confirm `SESSION_DRIVER=database` in phpunit.xml for parity (optional).

### 3. One simplification

**Drop standalone `KindlyLoginBrandingTest`** — fold `assertSee('Kindly Login')` into an existing auth feature test.

**Project decision:** Keep `KindlyLoginBrandingTest` for explicit SC-001 branding criterion; low cost, clear signal.

---

## Merged into plan (2026-06-01)

- Production: `SESSION_SECURE_COOKIE`, session GC note in `plan.md`
- Tests added: `SessionSecurityTest` (regenerate + logout invalidation)
- Registration: `session()->regenerate()` after `Auth::login()` in `RegisteredUserController`
