# Security Runbook: Key Rotation (Risk #1)

**Why:** `.env.example` previously committed real values for `APP_KEY` and
`REVERB_APP_SECRET`. The template is now scrubbed (placeholders), but the old
values remain in git history. Anyone with repo access can read them. Rotation
makes the leaked values worthless.

## Status

- [x] `.env.example` values replaced with placeholders (commit pending)
- [ ] **ACTION REQUIRED:** rotate live `APP_KEY` (prod)
- [ ] **ACTION REQUIRED:** rotate live `REVERB_APP_*` (prod)
- [ ] Optional: scrub git history (only if repo goes public / external access)

## Compromised values

The leaked `APP_KEY`, `REVERB_APP_ID`, `REVERB_APP_KEY`, and `REVERB_APP_SECRET`
are recorded in git history at `.env.example` before this commit. Retrieve them
for reference if needed with:

```bash
git log -p --all -- examples/dashboard-v1/.env.example | grep -E "APP_KEY=|REVERB_APP_"
```

They are not reproduced here — do not re-commit live secret values. Treat all of
them as compromised and rotate per the steps below.

## 1. Rotate APP_KEY (production — Coolify)

> WARNING: changing `APP_KEY` invalidates all existing encrypted data and
> active sessions (users get logged out). Encrypted DB columns, if any, become
> unreadable. If you have encrypted persistent data, decrypt-then-reencrypt
> instead of a blind swap.

1. Generate a new key locally (does not touch your app):
   ```bash
   php artisan key:generate --show
   ```
2. In Coolify → dashboard-v1 → Environment Variables, set `APP_KEY` to the new
   value.
3. Redeploy. Confirm `/up` returns 200 and login works.

## 2. Rotate Reverb credentials (production — Coolify)

Reverb uses a Pusher-style `APP_ID` / `APP_KEY` / `APP_SECRET` triple. The
secret must stay private; the key is sent to browsers (semi-public).

1. Generate new random values:
   ```bash
   # any sufficiently random strings, e.g.
   php -r "echo 'id='.random_int(100000,999999).PHP_EOL;"
   openssl rand -hex 12   # use for APP_KEY
   openssl rand -hex 12   # use for APP_SECRET
   ```
2. In Coolify, set `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`, and
   the matching build args `VITE_REVERB_APP_KEY` (client bundle bakes this in).
3. Redeploy **with a rebuild** — `VITE_*` are baked at build time.
4. Confirm websockets connect (browser console: Reverb/Echo connected).

## 3. Optional — scrub git history (Risk #1, option B)

Only needed if the repo will be public or shared externally. Destructive:
rewrites history, requires everyone to re-clone, and a force-push.

```bash
# Requires: pip install git-filter-repo
git filter-repo --path examples/dashboard-v1/.env.example --invert-paths --force
# then re-add the scrubbed template and force-push
```

Do NOT run this without team coordination and an explicit go-ahead.

## Prevention

- `.env` is gitignored (verified). Only `.env.example` is tracked.
- Keep `.env.example` placeholders only — never paste working credentials.
- Consider a pre-commit secret scanner (gitleaks / trufflehog) in CI.
