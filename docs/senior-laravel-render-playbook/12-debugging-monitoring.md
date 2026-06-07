# 12 — Debugging & Monitoring

> When prod burns at 3am, you don't have time to figure out where the logs are. Set this up BEFORE the fire.

---

## The Senior's Observability Triangle

```
        Metrics (what)
          /  \
         /    \
        /      \
       /        \
   Logs ─────── Traces
   (why)        (where)
```

Three pillars:
- **Metrics:** counts, rates, gauges (Render dashboard, Prometheus)
- **Logs:** structured events (Render logs, Logtail, Datadog)
- **Traces:** distributed call graphs (Sentry Performance, Honeycomb)

Need all three.

---

## Local Debugging Toolkit

### dd() and dump()

```php
dd($user);     // dump and die
dump($user);   // dump and continue
ray($user);    // dump to Ray app (paid, beautiful)
```

Don't ship `dd()` to production. Add Pint rule:
```json
{
  "rules": {
    "no_debug_statements": true
  }
}
```

### Laravel Telescope

`http://localhost/telescope`. See every:
- Request lifecycle
- DB query (with EXPLAIN)
- Cache hit/miss
- Mail sent
- Job dispatched/processed
- Exception
- Notification
- Schedule run
- Redis command

Drill in. Find the slow query. Find the silent failure.

**NEVER deploy Telescope to production.** It logs everything = privacy leak + storage explosion.

```php
// app/Providers/TelescopeServiceProvider.php
public function register(): void
{
    Telescope::night();
    if (! app()->environment('local')) {
        return;  // disable
    }
    // ...
}
```

### Laravel Debugbar

Bottom of every page. Shows:
- Time per phase (routing, controller, view)
- DB queries with backtrace
- Models hydrated
- Memory used
- Logs

Same rules: dev only.

### Clockwork

Browser extension that shows everything Debugbar does, less intrusively.

```bash
composer require itsgoingd/clockwork --dev
```

### Ray (Paid, $30 one-time)

```php
ray($user)->blue();
ray()->measure(fn() => User::all());
ray()->showQueries();
```

Beautiful UI for debugging. Worth $30 over career.

### Laravel Pail (Live Log Tail)

```bash
sail artisan pail
sail artisan pail --filter=error
sail artisan pail --user=42
```

Real-time. Color-coded. Better than `tail -f`.

---

## Production Logging

### Structured Logs (JSON)

`config/logging.php`:
```php
'stderr' => [
    'driver' => 'monolog',
    'level' => env('LOG_LEVEL', 'info'),
    'handler' => StreamHandler::class,
    'formatter' => JsonFormatter::class,
    'with' => ['stream' => 'php://stderr'],
],
```

Output:
```json
{"timestamp":"2026-06-07T15:30:00Z","level":"error","message":"Payment failed","context":{"order_id":42,"user_id":7,"error":"insufficient_funds"}}
```

Easy to grep, parse, alert on.

### What to Log

```php
// Auth events
Log::info('user.logged_in', ['user_id' => $user->id, 'ip' => $request->ip()]);
Log::warning('user.login_failed', ['email' => $email, 'ip' => $request->ip()]);

// Business events
Log::info('order.placed', ['order_id' => $order->id, 'total' => $order->total]);
Log::info('order.cancelled', ['order_id' => $order->id, 'reason' => $reason]);

// External calls
Log::info('stripe.charged', ['order_id' => $order->id, 'charge_id' => $chargeId, 'duration_ms' => $ms]);

// Errors (Sentry catches these automatically)
Log::error('payment.failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
```

### What NOT to Log

- Passwords, tokens, secrets (ever)
- Credit card numbers
- Full SSNs (mask first 5)
- PII without need (GDPR)
- HTML/JSON request bodies (huge, leaks)
- `dd()` output

### Log Levels (Use Correctly)

| Level | When |
|-------|------|
| emergency | System unusable |
| alert | Action required immediately |
| critical | Service down |
| error | Operation failed, user impacted |
| warning | Unexpected but recoverable |
| notice | Significant event, expected |
| info | Routine operation |
| debug | Development detail (never prod) |

Production `LOG_LEVEL=warning` is common. Keeps noise down.

---

## Sentry (Error Tracking)

### Install

```bash
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=https://...
```

`.env`:
```env
SENTRY_LARAVEL_DSN=https://...@sentry.io/...
SENTRY_TRACES_SAMPLE_RATE=0.1
SENTRY_PROFILES_SAMPLE_RATE=0.1
SENTRY_ENVIRONMENT=production
```

### What You Get

- Every uncaught exception → Sentry
- Stack trace with code context
- Request data (sanitized)
- User who hit it
- Trends over time
- Slack/email alerts
- Release tracking

### Context Enrichment

```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    if (app()->bound('sentry')) {
        \Sentry\configureScope(function (\Sentry\State\Scope $scope) {
            if (auth()->check()) {
                $scope->setUser([
                    'id' => auth()->id(),
                    'email' => auth()->user()->email,
                ]);
            }
        });
    }
}
```

Sentry now shows: "Error happened to user X (id 42)."

### Custom Context

```php
\Sentry\configureScope(function ($scope) use ($order) {
    $scope->setTag('order_id', $order->id);
    $scope->setExtra('order_total', $order->total);
});
```

### Suppress Noise

```php
// config/sentry.php
'ignore_exceptions' => [
    \Illuminate\Auth\AuthenticationException::class,
    \Illuminate\Validation\ValidationException::class,
    \Illuminate\Http\Exceptions\ThrottleRequestsException::class,
    \Illuminate\Database\Eloquent\ModelNotFoundException::class,
],
```

User-error exceptions don't need alerts. Filter them.

### Sentry Performance

Sample 10% of requests for tracing:
```env
SENTRY_TRACES_SAMPLE_RATE=0.1
```

See:
- Slowest endpoints (P50, P95, P99)
- Where time is spent (DB, cache, external)
- N+1 detection (automatic)
- Memory usage

The "find the slow endpoint" workflow from chapter 8 starts here.

---

## Uptime Monitoring

### External Pings

| Service | Cost | Notes |
|---------|------|-------|
| UptimeRobot | Free for 50 monitors | Basic but reliable |
| Better Stack | $25/mo | Beautiful, alerts to Slack |
| Cronitor | $20/mo | Cron monitoring too |
| Pingdom | $15/mo | Old reliable |

Set up:
- Homepage every 1 min
- `/healthz` every 1 min
- Critical APIs every 5 min

Alerts:
- Page on 3 consecutive failures
- Slack on 1 failure
- Auto-resolve when healthy

### Render Built-in

Render → Service → Metrics. Shows:
- Uptime %
- Response time P50/P95/P99
- Error rate
- CPU/RAM

Free. Always on.

---

## Cron Monitoring

Cron jobs fail silently. Use:

### Cronitor / healthchecks.io

```php
$schedule->command('reports:daily')
    ->dailyAt('03:00')
    ->before(fn() => Http::get('https://hc-ping.com/abc-uuid/start'))
    ->after(fn() => Http::get('https://hc-ping.com/abc-uuid'))
    ->onFailure(fn() => Http::get('https://hc-ping.com/abc-uuid/fail'));
```

If cron doesn't ping in 25h → alert. Free for first 20 checks.

---

## Real-Time Log Streaming

Render aggregates `stdout`/`stderr`. But:
- 7-day retention
- Limited search

Stream to:
- **Logtail / Better Stack Logs** ($20/mo, beautiful)
- **Papertrail** ($7/mo, classic)
- **Datadog Logs** (expensive)
- **AWS CloudWatch** (cheap, ugly)

Render → Settings → Log Streams → Add Endpoint.

---

## APM (Application Performance Monitoring)

For deeper insight than Sentry Performance:

- **New Relic** (free 100GB/mo, then $$$)
- **Datadog APM** (expensive but powerful)
- **Honeycomb** (best for distributed traces)
- **OpenTelemetry + self-hosted** (free but complex)

Most Laravel apps: Sentry Performance is enough.

---

## The On-Call Playbook

### Setup Before You Need It

1. **PagerDuty or OpsGenie account** ($20/mo)
2. **Slack #alerts channel**
3. **Pager rotation** (you for week, your team thereafter)
4. **Runbook in repo** at `docs/runbook.md`

### When You're Paged

```
1. Acknowledge in PagerDuty within 5 min
2. Post in #incidents: "Investigating"
3. Open dashboards: Render, Sentry, Uptime
4. Form hypothesis
5. Mitigate (rollback, restart, scale)
6. Post update every 15 min
7. Resolve
8. Postmortem within 48h
```

### The 5-Minute Triage

| Symptom | First Check |
|---------|-------------|
| Site down | Render dashboard, recent deploy |
| Slow site | Sentry slowest endpoints, DB load |
| Errors spike | Sentry recent errors, recent deploy |
| Auth broken | Session driver, Redis health |
| Mail not sending | Queue worker, mail provider status |
| Webhook missed | Inbound queue, signature mismatch |

90% of incidents trace to recent change. Check deploys first.

---

## Postmortem Template

`docs/postmortems/2026-06-07-checkout-down.md`:

```markdown
# 2026-06-07 — Checkout Down for 18 Minutes

## Impact
- 18 min of failed checkouts (15:30 - 15:48 UTC)
- ~$2,400 estimated lost revenue
- 42 users affected

## Timeline
- 15:28 — Deploy of PR #142 (refactor charge flow)
- 15:30 — Sentry alert: 100% checkout failures
- 15:32 — Page received
- 15:35 — Identified missing migration in deploy
- 15:42 — Migration applied via Render shell
- 15:48 — All checkouts succeeding

## Root Cause
PR #142 referenced new column `payments.idempotency_key` but migration was in a separate PR not yet merged. Code rolled out, schema didn't match.

## Why It Happened
1. No CI check enforcing migration-code parity
2. No staging verification before main merge
3. Pre-deploy migration didn't catch it (migration was in different PR)

## Action Items
- [ ] Add CI check: any migration MUST be paired with code that uses it (or fail PR)
- [ ] Require staging verification on PRs touching payments
- [ ] Add canary deploy step before full rollout
- [ ] Sentry alert tuned: should alert at 50% failure rate (not 100%)

## What Went Well
- Sentry caught it within 2 min
- Rollback was fast (10 min)
- No data corruption
```

Blameless. Action-focused. Shared with team.

---

## Health Checks (Deeper)

`/healthz` should:
- Verify DB reachable
- Verify Redis reachable
- Verify queue worker alive
- Verify S3 reachable
- Return < 1s

```php
Route::get('/healthz', function () {
    $checks = [];

    try {
        DB::select('SELECT 1');
        $checks['db'] = 'ok';
    } catch (\Throwable $e) {
        $checks['db'] = 'fail: ' . $e->getMessage();
    }

    try {
        Redis::ping();
        $checks['redis'] = 'ok';
    } catch (\Throwable $e) {
        $checks['redis'] = 'fail: ' . $e->getMessage();
    }

    try {
        Storage::disk('s3')->exists('healthcheck.txt');
        $checks['s3'] = 'ok';
    } catch (\Throwable $e) {
        $checks['s3'] = 'fail: ' . $e->getMessage();
    }

    $allOk = !array_filter($checks, fn($v) => str_starts_with($v, 'fail'));

    return response()->json([
        'status' => $allOk ? 'ok' : 'degraded',
        'checks' => $checks,
        'version' => config('app.version'),
        'time' => now()->toIso8601String(),
    ], $allOk ? 200 : 503);
});
```

Render uses this for auto-restart. Monitoring tools use it for alerts.

---

## Debugging in Production (Without SSH)

### Render Shell

Render → Service → Shell. Live container.

```bash
php artisan tinker
>>> User::find(42)
>>> Cache::flush()
```

**Use for diagnosis, not changes.** Container resets on deploy.

### Tinker for Specific Issue

```php
// Find that order
$order = Order::find(42);

// Inspect state
$order->items;
$order->payments;
$order->user->subscriptions;

// Replay the failing job
(new ChargeCustomer($order))->handle(app(StripeService::class));
```

### Database Inspection

```bash
render psql my-saas-db
\dt                            # list tables
\d users                       # describe table
SELECT * FROM users WHERE id = 42;
```

### Redis Inspection

```bash
render redis-cli my-saas-redis
KEYS *                         # don't on huge sets
GET cache:foo
LLEN queues:default
ZRANGE horizon:metrics:* 0 -1
```

---

## Slow Query Log (Postgres)

```sql
-- One-time setup
ALTER SYSTEM SET log_min_duration_statement = 500;  -- log queries > 500ms
SELECT pg_reload_conf();
```

Render dashboard → Database → Logs. See slow queries.

Or query directly:
```sql
SELECT query, calls, mean_exec_time, max_exec_time
FROM pg_stat_statements
ORDER BY mean_exec_time DESC
LIMIT 20;
```

---

## Performance Regression Detection

In CI:
```yaml
- name: Benchmark critical endpoints
  run: |
    php artisan test --filter=PerformanceTest
```

```php
it('homepage responds in <100ms', function () {
    User::factory()->count(50)->create();

    $time = measure(fn() => $this->get('/'));

    expect($time)->toBeLessThan(100);
});
```

Catches PR-introduced slowdowns before merge.

---

## The Alert Pyramid

```
   Page (3am wake-up)
  ─────────────────── ← critical: revenue, security, downtime
    Slack (notify)
  ─────────────────── ← warning: degraded performance, retries
   Email (digest)
  ─────────────────── ← info: weekly trends
    Dashboard
  ─────────────────── ← see when you look
```

Most alerts = dashboard. Few = email. Fewer = Slack. Rare = page.

If you're paged > 1/week, your alerts are wrong. Tune them.

---

## Anti-Patterns to Refuse

1. **Alerting on every error.** Page fatigue → ignored alerts → real fire missed.
2. **Logging to local files only.** Vanishes on Render deploy.
3. **No log retention.** Compliance and forensics need history.
4. **`dd()` in production code.** Embarrassing, may be live.
5. **`try/catch` swallowing exceptions.** Errors invisible to Sentry.
6. **Health check that's slow.** Render restarts you, causing downtime.
7. **Sentry without releases.** Can't tell which deploy caused regression.
8. **Page-able alerts without escalation.** First responder asleep = bad.
9. **No runbook for on-call.** New oncall person fumbles in the dark.
10. **Alerts only on errors, not on absence.** Cron not running ≠ no error.

---

## The Senior's Observability Discipline

Daily:
- Glance at Render dashboard
- Glance at Sentry

Weekly:
- Top 5 slowest endpoints
- Top 5 noisiest errors
- Recent failed jobs

Monthly:
- Postmortem review (themes?)
- Alert tuning (any noisy ones?)
- Cost vs value of monitoring stack

Quarterly:
- Test the on-call rotation
- Verify backups restore
- Run a "game day" (simulated outage)

You earn sleep by setting this up well.
