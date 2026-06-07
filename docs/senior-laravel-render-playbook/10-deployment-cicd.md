# 10 — Deployment & CI/CD

> Manual deploys are a smell. Automated, reversible, observable deploys are the senior baseline.

---

## The Senior's Deployment Doctrine

1. **Every commit on `main` is deployable.** If not, your tests are wrong.
2. **Every deploy is reversible.** One click rollback. Always.
3. **Every deploy is observable.** You know within 60s if it broke prod.
4. **Every deploy is fast.** Under 5 minutes from push to live.
5. **Every deploy is safe.** Migration before code switch. Workers drained gracefully.

---

## The Full Pipeline

```
Developer commit
      │
      ▼
git push origin feature/foo
      │
      ▼
GitHub Actions CI runs
  ├── Lint (Pint)
  ├── Static analysis (Larastan)
  ├── Tests (Pest parallel)
  ├── Frontend build check
  └── Security audit
      │
      ▼ all green
Code review (PR)
      │
      ▼ approved
Merge to main
      │
      ▼
Render webhook triggered
      │
      ▼
Render build
  ├── Pull code
  ├── Build Docker image
  ├── Run pre-deploy command (migrations)
  └── Health check new instance
      │
      ▼ healthy
Switch traffic to new instance
      │
      ▼
Old instance drained
      │
      ▼
Sentry release marked
      │
      ▼
Slack notification
      │
      ▼
Done. Live in 4-7 minutes.
```

---

## CI: GitHub Actions Workflow

`.github/workflows/ci.yml`:

```yaml
name: CI

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

env:
  PHP_VERSION: '8.4'
  NODE_VERSION: '22'

jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ env.PHP_VERSION }}
          coverage: none
      - uses: ramsey/composer-install@v3

      - name: Pint (code style)
        run: ./vendor/bin/pint --test

      - name: Larastan (static analysis)
        run: ./vendor/bin/phpstan analyse --memory-limit=2G --no-progress

      - name: Composer audit
        run: composer audit --no-dev || true   # warn don't fail

  test:
    runs-on: ubuntu-latest
    needs: lint
    services:
      pgsql:
        image: postgres:16
        env:
          POSTGRES_PASSWORD: password
          POSTGRES_DB: testing
        ports: ['5432:5432']
        options: --health-cmd pg_isready --health-interval 10s --health-timeout 5s --health-retries 5
      redis:
        image: redis:7-alpine
        ports: ['6379:6379']
    steps:
      - uses: actions/checkout@v4

      - uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ env.PHP_VERSION }}
          extensions: pdo_pgsql, redis, gd, zip, bcmath, intl
          coverage: pcov
          tools: composer:v2

      - uses: actions/setup-node@v4
        with:
          node-version: ${{ env.NODE_VERSION }}
          cache: npm

      - uses: ramsey/composer-install@v3

      - name: Install JS deps
        run: npm ci

      - name: Prepare env
        run: |
          cp .env.example .env
          php artisan key:generate

      - name: Build assets
        run: npm run build

      - name: Run tests
        env:
          DB_CONNECTION: pgsql
          DB_HOST: localhost
          DB_USERNAME: postgres
          DB_PASSWORD: password
          DB_DATABASE: testing
          REDIS_HOST: localhost
        run: ./vendor/bin/pest --parallel --coverage --min=70

      - name: Upload coverage
        if: github.ref == 'refs/heads/main'
        uses: codecov/codecov-action@v4
        with:
          token: ${{ secrets.CODECOV_TOKEN }}

  security:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: NPM audit
        run: npm audit --audit-level=high || true
      - uses: shivammathur/setup-php@v2
        with: { php-version: '8.4' }
      - uses: ramsey/composer-install@v3
      - name: Composer audit
        run: composer audit --format=json
```

---

## Branch Strategy

```
main           ← production (auto-deploys)
└── feature/X  ← short-lived (1-3 days max)
```

**No `develop` branch.** Trunk-based development.
**No long-lived feature branches.** Merge daily.
**Feature flags hide unfinished work**, not branches.

```php
// Use Laravel Pennant
use Laravel\Pennant\Feature;

Feature::define('new-checkout', fn(User $user) =>
    $user->is_beta_tester
);

// In code
if (Feature::active('new-checkout')) {
    return new V2Checkout();
}
return new V1Checkout();
```

Ship to main with feature off. Enable gradually. No "big bang" releases.

---

## render.yaml Auto-Deploy

```yaml
services:
  - type: web
    name: my-saas
    autoDeploy: true        # push to main = deploy
    branch: main
    healthCheckPath: /healthz
```

For staging:
```yaml
  - type: web
    name: my-saas-staging
    autoDeploy: true
    branch: develop          # or 'staging'
    plan: starter
```

Two services. Two branches. Two environments. Both auto-deploy.

---

## Pre-Deploy Migrations

Render Service → Settings → Pre-Deploy Command:

```bash
php artisan migrate --force --isolated
```

Runs in a fresh container BEFORE the new web instance starts.

If migration fails:
- Old version stays live
- New version doesn't start
- You get notified
- You fix the migration
- Push again

Safe by default.

---

## Zero-Downtime Deploy

Render does this automatically for web services:

1. Build new image
2. Start new instance
3. Health check it
4. Wait for it to be healthy
5. Route traffic to new instance
6. Drain old instance (graceful shutdown)

You see zero downtime in metrics. Users see zero errors.

**Workers** are different: old worker finishes its current job, then exits. New worker picks up. Brief gap but no job loss (Redis queue holds jobs).

---

## Migration Strategy for Big Changes

### Adding Column

Phase 1 (now):
```php
// Migration
$table->string('phone')->nullable()->after('email');
```
Deploy. Old code ignores it. New code can write.

Phase 2 (later):
```php
// Make required (after backfill)
$table->string('phone')->nullable(false)->change();
```

### Dropping Column

Phase 1: Deploy code that doesn't reference column.
Phase 2: Drop column.

NEVER drop column in same deploy as code change. The window where new code is rolling out but old code still references old schema = errors.

### Renaming Column

Phase 1: Add new column, dual-write.
Phase 2: Backfill new column from old.
Phase 3: Read from new column. Stop writing old.
Phase 4: Drop old column.

4 deploys. Boring. Safe.

### Big Data Migrations

If migration takes > 30 seconds, don't run as `migrate`. Make a Job:

```php
// Migration
public function up(): void
{
    Schema::table('users', function ($t) {
        $t->string('username')->nullable();
    });

    BackfillUsernames::dispatch();
}
```

Job runs async. Migration finishes in 1s. Render doesn't time out.

---

## Rollback Strategy

### One-Click Rollback (Render Dashboard)

Render Service → Events → Previous deploy → Rollback.

Fast. Visual. No git knowledge needed.

But: migrations don't roll back. If a migration broke prod, rolling back code may not fix it.

### Safer: Always Forward

Roll forward with a fix commit:
```bash
git revert <bad-commit>
git push origin main
```

Forward roll is auditable. Rollback is panic.

### When Rollback Is Right

- New code crashes on every request
- No DB migration in the bad deploy
- Speed > correctness for the next 5 minutes

Otherwise, fix forward.

---

## Database Backup Before Migration

Critical migrations: snapshot first.

```bash
# Manually via Render CLI
render psql my-saas-db
\copy users TO 'users_backup.csv' CSV HEADER
```

Or trigger a manual backup in dashboard before the deploy.

Or:
```php
// Schedule a backup right before known big migration
Artisan::call('backup:run', ['--only-db' => true]);
```

---

## Deploy Notifications

### Slack Webhook from GitHub Actions

```yaml
  notify:
    runs-on: ubuntu-latest
    needs: [test, security]
    if: github.ref == 'refs/heads/main'
    steps:
      - name: Slack notify
        uses: 8398a7/action-slack@v3
        with:
          status: ${{ job.status }}
          fields: repo,commit,author,action
          webhook_url: ${{ secrets.SLACK_WEBHOOK }}
```

### Sentry Release Marking

```yaml
      - name: Sentry release
        uses: getsentry/action-release@v1
        env:
          SENTRY_AUTH_TOKEN: ${{ secrets.SENTRY_AUTH_TOKEN }}
          SENTRY_ORG: my-org
          SENTRY_PROJECT: my-saas
        with:
          environment: production
          version: ${{ github.sha }}
```

Sentry now associates errors with specific deploys. Diagnose regression in seconds.

---

## Smoke Tests After Deploy

```yaml
  smoke:
    runs-on: ubuntu-latest
    needs: [deploy]
    steps:
      - name: Wait for deploy
        run: sleep 60
      - name: Hit healthcheck
        run: |
          curl --fail https://app.example.com/healthz
      - name: Critical endpoints
        run: |
          curl --fail https://app.example.com/api/status
          curl --fail https://app.example.com/login
```

Catches "deploy succeeded but app is broken" within minutes.

---

## The Senior's Pre-Deploy Checklist

Before merging to main:

- [ ] CI green
- [ ] Code reviewed
- [ ] Migration safe (additive, no destructive ops)
- [ ] Feature flag in place if risky
- [ ] Tested manually if it's UI
- [ ] Rollback plan written if it's risky
- [ ] Off-hours if it's big (avoid Friday 4pm)

---

## Staging Environment

```yaml
  - type: web
    name: my-saas-staging
    branch: develop
    plan: starter
    envVars:
      - key: APP_ENV
        value: staging
      - key: APP_DEBUG
        value: false        # don't leak in staging either
```

Treat staging as prod:
- Real Postgres (not SQLite)
- Real Redis
- Real S3 (separate bucket)
- Sanitized data (no real PII)

Why: features that "work in staging" but "break in prod" mean staging wasn't real enough.

---

## Preview Environments per PR

```yaml
services:
  - type: web
    name: my-saas
    previewsEnabled: true
    previews:
      generation: automatic
      expireAfterDays: 7
```

Every PR opens its own:
- `https://my-saas-pr-42.onrender.com`
- Own DB, own Redis
- QA tests it like prod

Closes automatically when PR merges or expires.

**Cost:** ~$15/PR/week. For active teams, budget $100-300/mo. Worth every penny vs prod incidents.

---

## Secrets Management Across Environments

| Secret | Local | Staging | Prod |
|--------|-------|---------|------|
| APP_KEY | Generated in `.env` | Render-generated | Render-generated |
| DB password | Sail default | Render auto | Render auto |
| Stripe key | TEST mode | TEST mode | LIVE mode |
| Sentry DSN | None | Staging project | Prod project |
| Mail | Mailpit | Postmark sandbox | Postmark live |

**Critical:** test secrets in test mode. NEVER live keys in staging.

---

## Database Promotion (Staging → Prod)

For DB migrations that need careful prod data:

1. Snapshot prod DB
2. Restore to staging
3. Apply migration on staging
4. Verify
5. Apply same migration on prod

```bash
# Render CLI
render psql my-saas-db < migration.sql
```

For Eloquent migrations, just `php artisan migrate --force` on prod (after staging tested).

---

## Worker Deployment Strategy

Render auto-deploys workers when code changes. Each worker:

1. Finishes current job
2. Receives SIGTERM
3. Stops accepting new jobs
4. Exits gracefully (with `--max-time=N` or `--rest=N`)
5. New worker starts with new code

In `app/Console/Kernel.php`:
```php
$schedule->command('horizon:terminate')
    ->everyThirtyMinutes()
    ->onOneServer();
```

Forces workers to restart with new code (in case auto-deploy doesn't fully restart).

Use Horizon's `php artisan horizon:terminate` after deploy. Workers gracefully restart with latest code.

---

## Deploy Frequency Targets

| Maturity | Deploys/Week |
|----------|--------------|
| Startup (1-3 devs) | 5-15 |
| Scale-up (5-15 devs) | 20-100 |
| Mature (50+ devs) | 100-1000 |
| FAANG | 1000+ |

If you deploy < 1/week, your batches are too big and your blast radius is too big. Smaller, more frequent.

---

## Deploy on Friday?

Conventional wisdom: never.
Senior wisdom: **only when the deploy is safe**.

Safe = small, reversible, feature-flagged, tested in staging, has rollback ready, on-call has bandwidth.

A 100-line bug fix on Friday is fine. A 5000-line feature flag enable on Friday is not.

---

## Deploy Discipline Score

Rate your own deploy process:

- [ ] (1 pt) Every commit on main is auto-deployed
- [ ] (1 pt) CI runs lint + static analysis + tests
- [ ] (1 pt) PR previews are first-class
- [ ] (1 pt) Migrations run pre-deploy
- [ ] (1 pt) Rollback is one click
- [ ] (1 pt) Sentry receives release info
- [ ] (1 pt) Slack notifies on deploy
- [ ] (1 pt) Smoke tests run post-deploy
- [ ] (1 pt) Feature flags hide unfinished work
- [ ] (1 pt) Deploys happen 5+ times/week

Score:
- 0-3: You're a junior. Fix this first.
- 4-6: Mid. You're catching up.
- 7-9: Senior. You sleep well.
- 10: Principal. Teach others.

---

## The Discipline of Boring Deploys

A senior's deploy is **boring**:
- No drama
- No "let's hope it works"
- No 4-hour rollback war room
- No "remember to run X manually"

Boring deploys = healthy team = users get value faster.

If your deploys aren't boring, that's the next thing to fix.
