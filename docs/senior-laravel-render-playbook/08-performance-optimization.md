# 08 — Performance Optimization

> Slow apps lose users. Slow apps lose money. The senior dev tunes systematically, never by gut feel.

---

## The Performance Hierarchy of Effort

Most impact per hour spent:

1. **Fix N+1 queries** (often 10× speedup, 1 hour work)
2. **Add missing indexes** (often 100× speedup, 15 min work)
3. **Cache hot paths** (often 5× speedup, 1 hour work)
4. **Queue slow work** (5-50× perceived speedup, half day)
5. **Optimize asset delivery** (frontend, 2× speedup, day)
6. **Database tuning** (Postgres config, 2× speedup, day)
7. **Horizontal scaling** (linear cost, last resort)

Senior devs walk down this list. Never skip to step 7.

---

## Measure First, Always

Without numbers, you're guessing. Tools:

### Laravel Telescope (Dev)

`http://localhost/telescope` shows:
- Query log per request (catches N+1)
- Cache hits/misses
- Job durations
- Mail sends
- Time per middleware

### Laravel Debugbar (Dev)

Bottom of page. Shows:
- Total time
- DB queries with EXPLAIN
- Models hydrated
- Memory used

### Clockwork (Alternative)

Browser extension. Less invasive than Debugbar.

### Production: Sentry Performance

```bash
composer require sentry/sentry-laravel
```

`.env`:
```env
SENTRY_TRACES_SAMPLE_RATE=0.1
SENTRY_PROFILES_SAMPLE_RATE=0.1
```

Sample 10% of requests. Get distributed traces. Spot slow endpoints.

### Production: Render Metrics

Render dashboard shows:
- CPU/RAM per service
- Request latency
- Error rate

Free with every service.

---

## The N+1 Killer

```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    Model::preventLazyLoading(! app()->isProduction());
    Model::preventSilentlyDiscardingAttributes(! app()->isProduction());
    Model::preventAccessingMissingAttributes(! app()->isProduction());
}
```

Dev/staging: throws. Production: logs (don't crash users).

Log to Sentry instead of throwing in production:
```php
Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
    Log::warning("Lazy-loaded {$relation} on " . get_class($model));
});
```

You'll find N+1s within a day. Fix them all.

---

## Indexing Strategy

### Index What You Query

```php
$table->index('email');                        // Single
$table->index(['user_id', 'created_at']);      // Composite (order matters)
$table->index('status')->where('status', '!=', 'deleted');  // Partial (Postgres)
$table->fullText(['title', 'body']);           // Full-text
```

### Composite Index Rule

**Most selective column first.** For `WHERE user_id = ? AND status = ?`:
- `user_id` is high cardinality (many distinct values) → first
- `status` is low cardinality (few values) → second

### Use `EXPLAIN ANALYZE` (Postgres)

```sql
EXPLAIN ANALYZE
SELECT * FROM posts
WHERE user_id = 5 AND status = 'published'
ORDER BY created_at DESC LIMIT 20;
```

Look for:
- `Seq Scan` on big table → missing index
- `Index Scan` → 
- Cost > 1000 → optimization opportunity
- Rows returned >> rows returned in query → over-fetching

### Drop Unused Indexes

Indexes slow writes. In Postgres:
```sql
SELECT schemaname, tablename, indexname, idx_scan
FROM pg_stat_user_indexes
WHERE idx_scan = 0;
```

Indexes with 0 scans = dead weight. Drop them.

---

## Caching Strategy

### The Cache Hierarchy

```
┌──────────────────────────┐
│ HTTP Cache (CDN, browser)│  ← 100ms
├──────────────────────────┤
│ App Cache (Redis)        │  ← 1-5ms
├──────────────────────────┤
│ Database                 │  ← 10-100ms
└──────────────────────────┘
```

Cache as close to the user as possible.

### Cache Drivers

| Driver | Use For |
|--------|---------|
| `redis` | Default; sessions, queues, cache |
| `database` | Small apps without Redis |
| `file` | Single-server, no Redis |
| `array` | Tests only |
| `dynamodb` | AWS environments |

Render: use Redis. Always.

### Cache Patterns

```php
// 1. Read-through cache
$users = Cache::remember('users:active', 300, fn() =>
    User::active()->get()
);

// 2. Tagged cache (for invalidation groups)
Cache::tags(['users', 'team:42'])->remember('users:team:42', 600, fn() =>
    User::where('team_id', 42)->get()
);

Cache::tags(['users'])->flush();  // invalidate all user caches

// 3. Lock to prevent stampede
Cache::lock('users:rebuild', 10)->block(5, function () {
    Cache::put('users:active', User::active()->get(), 300);
});

// 4. Atomic increment
Cache::increment('views:post:42');

// 5. Forever (manually invalidated)
Cache::forever('config:plan:pro', $planData);
```

### Cache Keys (Senior Convention)

```
{model}:{id}:{operation}
posts:42:view-count
users:active
team:42:members:count
```

Predictable. Easy to bust.

### Cache Invalidation

The hard problem. Strategies:

**1. TTL (Time-To-Live)** — auto-expires:
```php
Cache::remember('key', 60, fn() => /* ... */);  // 60 seconds
```

**2. Model events** — invalidate on write:
```php
class Post extends Model
{
    protected static function booted(): void
    {
        static::saved(fn($post) => Cache::forget("posts:{$post->id}"));
        static::deleted(fn($post) => Cache::forget("posts:{$post->id}"));
    }
}
```

**3. Tags** — invalidate groups:
```php
Cache::tags(['posts'])->flush();
```

**4. Versioning** — bump version, old cache abandoned:
```php
$version = Cache::get('posts:version', 1);
Cache::remember("posts:list:v{$version}", 600, fn() => /* ... */);

// To invalidate:
Cache::increment('posts:version');
```

---

## Query Optimization Recipes

### Eager Load Everything You Display

```php
// BAD
$posts = Post::paginate();
@foreach ($posts as $post)
    {{ $post->user->name }}        // N+1
    {{ $post->tags->count() }}     // N+1
@endforeach

// GOOD
$posts = Post::with('user')->withCount('tags')->paginate();
```

### Avoid `count()` for Existence

```php
// BAD
if (Post::where('user_id', $id)->count() > 0) { /* ... */ }

// GOOD
if (Post::where('user_id', $id)->exists()) { /* ... */ }
```

### Limit Columns

```php
// Slow when posts have huge body field
$posts = Post::all();

// Fast
$posts = Post::select('id', 'title', 'slug', 'created_at')->get();
```

### Use Cursor Pagination for Big Sets

```php
// Offset pagination - slow on page 1000
Post::paginate(20);  // OFFSET 19980 LIMIT 20

// Cursor pagination - O(1) regardless of page
Post::cursorPaginate(20);  // WHERE id < ? LIMIT 20
```

### Chunk for Mass Operations

```php
// BAD - loads all users in memory
User::all()->each(fn($u) => $u->recalculate());

// GOOD - chunks of 500
User::chunkById(500, fn($users) => $users->each->recalculate());
```

### Subqueries Over Multiple Queries

```php
$users = User::select([
    'id',
    'name',
    'last_login_at' => Login::select('created_at')
        ->whereColumn('user_id', 'users.id')
        ->latest()
        ->limit(1),
])->get();
```

One query. No N+1.

### Aggregate in DB

```php
$totalRevenue = Order::sum('total');
$avgPrice = Product::avg('price');
$counts = Post::groupBy('status')->selectRaw('status, count(*) as c')->get();
```

Don't load 100k rows to sum them. Sum in SQL.

---

## Asset & Frontend Optimization

### Vite for Production

```bash
npm run build
```

Outputs to `public/build/`. Hashed filenames = forever-cache-friendly.

### Asset Versioning

`@vite('resources/js/app.ts')` automatically uses hashed paths in production.

### Preload Critical Resources

`resources/views/app.blade.php`:
```blade
<link rel="preload" href="{{ Vite::asset('resources/fonts/Inter.woff2') }}" as="font" type="font/woff2" crossorigin>
@vite(['resources/css/app.css', 'resources/js/app.ts'])
```

### CDN for Static Assets

Render serves static files fast. But for global audience, push to:
- Cloudflare (free)
- Bunny CDN ($1/mo)
- CloudFront (AWS)

Point assets to CDN URL in production.

### Image Optimization

```bash
composer require spatie/laravel-medialibrary
composer require spatie/image-optimizer
```

```php
$user->addMedia($request->file('avatar'))
     ->toMediaCollection('avatars');

// Auto-generates webp + multiple sizes
```

Serve next-gen formats:
```blade
<picture>
    <source srcset="{{ $user->getFirstMedia('avatars')->getUrl('webp') }}" type="image/webp">
    <img src="{{ $user->getFirstMedia('avatars')->getUrl() }}" alt="">
</picture>
```

---

## Queue Slow Work

Anything > 100ms in a request lifecycle: queue it.

```php
// BAD - blocks request 2 seconds
public function register(RegisterRequest $request)
{
    $user = User::create($request->validated());
    Mail::to($user)->send(new WelcomeMail($user));  // 2s
    return redirect('/dashboard');
}

// GOOD - returns in 50ms, email sends async
public function register(RegisterRequest $request)
{
    $user = User::create($request->validated());
    Mail::to($user)->queue(new WelcomeMail($user));
    return redirect('/dashboard');
}
```

Use queues for:
- Emails / SMS
- PDF generation
- Image processing
- External API calls (Stripe, Mailchimp)
- Reports / exports
- Webhooks dispatched out
- Cache warming

See `11-queues-jobs-scheduling.md` for full queue mastery.

---

## OpCache (PHP Bytecode Cache)

In production Dockerfile:

```ini
; /usr/local/etc/php/conf.d/opcache.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0    ; CRITICAL: cache forever, invalidate on deploy
opcache.revalidate_freq=0
opcache.preload=/var/www/preload.php
```

Set in Render's Dockerfile. 30-50% faster requests.

### Laravel Octane (When You Outgrow FPM)

```bash
composer require laravel/octane
php artisan octane:install --server=swoole
```

Octane keeps the framework booted in memory between requests. 3-5× faster.

**Tradeoffs:**
- Memory leaks become real (long-lived processes)
- Need to be careful with statics, singletons
- Different debugging story

Use Octane when:
- App is profitable
- FPM is the bottleneck
- Team can handle the complexity

Don't use Octane:
- First year of a project
- Junior team
- When other optimizations remain

---

## Database Connection Pooling

Render Postgres: connection limit usually 97 (3 reserved for admin).

With Octane or many queue workers, you can exhaust pool.

Use PgBouncer (Render supports it on higher tiers, or external):

```env
DB_HOST=pgbouncer-host
DB_PORT=6432
```

PgBouncer multiplexes 1000+ app connections onto 20 DB connections.

---

## Lazy Collections for Memory

```php
// BAD - 10GB RAM for 10M rows
User::all()->each(/* ... */);

// GOOD - constant memory
User::lazy()->each(/* ... */);

// GOOD - constant memory, by ID for big tables
User::lazyById()->each(/* ... */);
```

For CSV exports, big reports, mass operations: always lazy.

---

## Route Caching

Production deploys:
```bash
php artisan route:cache
php artisan view:cache
php artisan config:cache
php artisan event:cache
```

Add to Render's build command. 30% request speedup.

**Don't** in development (changes won't reflect).

`php artisan optimize` does all of the above.

---

## Composer Autoload

```bash
composer install --optimize-autoloader --no-dev
```

`--optimize-autoloader` builds class map. Faster autoloading.
`--no-dev` excludes Telescope, Debugbar, etc.

In Render's build:
```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

---

## Frontend Bundle Discipline

### Code Splitting

Vue with Inertia auto-splits per page:
```ts
// app.ts
createInertiaApp({
    resolve: name => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
})
```

Each page = own chunk. Loads on demand.

### Lazy Components

```vue
<script setup>
const HeavyChart = defineAsyncComponent(() =>
    import('./Components/HeavyChart.vue')
)
</script>
```

### Bundle Analysis

```bash
npm install --save-dev rollup-plugin-visualizer
```

```ts
// vite.config.ts
import { visualizer } from 'rollup-plugin-visualizer'

export default defineConfig({
    plugins: [
        visualizer({ open: true, filename: 'dist/stats.html' }),
    ],
})
```

`npm run build` opens a treemap. See what's bloating bundle.

---

## HTTP Caching

```php
return response()->json($data)
    ->setMaxAge(3600)
    ->setSharedMaxAge(3600)
    ->setEtag(md5($jsonString))
    ->setLastModified($lastModified);
```

`Cache-Control` headers tell browsers/CDNs to cache. Save your server entirely.

Use `ETag` for conditional requests:
```php
return response()->json($data)
    ->setEtag(md5(json_encode($data)));
```

If client has same ETag, send 304 Not Modified. No body. Fast.

---

## Real Production Win Examples

| Optimization | Before | After |
|--------------|--------|-------|
| Add index on `posts.user_id` | 800ms | 12ms |
| Eager load `comments.user` | 2.1s (N+1) | 80ms |
| Cache `getActivePlans` 5min | 200ms | 1ms |
| Queue welcome email | 2.5s request | 90ms request |
| OpCache + preload | 180ms baseline | 95ms baseline |
| Octane Swoole | 95ms | 28ms |
| CDN for assets | 800KB transferred | 30KB transferred |

Each one is hours of work. All compound.

---

## The "Find the Slow Endpoint" Drill

1. Open Sentry Performance
2. Sort endpoints by P95 latency
3. Pick the slowest one
4. Open trace
5. Find biggest span
6. Open Telescope locally, reproduce
7. Identify: N+1? Missing index? Slow external? Bad cache strategy?
8. Fix
9. Deploy
10. Verify in Sentry

Senior devs do this weekly. Slow endpoints don't accumulate.

---

## Anti-Patterns to Refuse

1. **"We need microservices for performance."** Almost certainly false. Add index first.
2. **"Let's rewrite in Go."** Same. Profile first.
3. **`->all()->where(...)`** instead of `->where(...)->get()`. Loading everything to filter in PHP.
4. **Cache forever without invalidation.** Stale data is worse than slow data.
5. **Optimizing the request handler when the queue is the bottleneck.** Always profile end-to-end.
6. **Adding Redis caches that hit the DB on miss without lock.** Stampede during cache expire.
7. **Premature horizontal scaling.** 1 fast box > 4 slow boxes.

---

## Sustainable Performance Discipline

| Cadence | Action |
|---------|--------|
| Daily | Watch Render dashboard, look for spikes |
| Weekly | Check Sentry Performance — slowest endpoints |
| Monthly | Review Postgres slow query log |
| Quarterly | Vacuum + analyze big tables |
| Yearly | Load test with k6 or Artillery |

Performance is a habit, not a project.

---

## The Senior's Performance Mantra

> Measure. Find the worst thing. Fix it. Measure again.

Repeat forever. Never guess.
