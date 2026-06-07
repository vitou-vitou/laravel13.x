# 05 — Database & Eloquent Mastery

> 80% of production bugs and 95% of performance issues are database-shaped. Master this file or stay junior forever.

---

## The Database Choice: Postgres > MySQL (for 2026)

| Feature | PostgreSQL 16 | MySQL 8 |
|---------|---------------|---------|
| JSON queries | Native, fast | OK, slower |
| Full-text search | Built-in, decent | Built-in, decent |
| CTEs | Excellent | Decent |
| Window functions | Excellent | Decent |
| Constraints | Strict by default | Relaxed |
| Stored procedures | Plpgsql + Python + JS | SQL only |
| GIS | PostGIS | None native |
| Render support | First class | First class |
| Backup tooling | pg_dump, WAL | mysqldump |
| Concurrency | MVCC, no row locks needed | Locks more |

**Pick Postgres unless legacy demands MySQL.**

---

## Migration Discipline

### Naming Pattern

```
2026_06_07_120000_create_posts_table.php
2026_06_08_130000_add_published_at_to_posts_table.php
2026_06_09_140000_create_post_tag_pivot_table.php
2026_06_10_150000_index_posts_user_id_published_at.php
```

One concern per migration. Easy to revert.

### The Senior Migration Template

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('body');
            $table->string('status')->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

**Rules:**
1. Foreign keys always have an action (`cascadeOnDelete`, `nullOnDelete`, `restrictOnDelete`).
2. Index every foreign key.
3. Index every column you'll filter or sort by.
4. Use `softDeletes()` for user-facing data (you'll thank yourself).
5. Use `timestamps()` always.
6. Use `string` for short text, `text` for long, `longText` for HTML/markdown.

### Adding Columns Safely

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('phone')->nullable()->after('email');
        $table->index('phone');
    });
}
```

**Never** `dropColumn` without a 2-deploy plan. Old code still references it.

### Two-Phase Column Rename

Phase 1 (deploy):
```php
$table->renameColumn('user_id', 'author_id');
```
Both old and new code work because Eloquent maps it.

Phase 2 (next deploy after all old code is gone):
- Remove the old code paths
- Done

### Backfill Migrations

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('display_name')->nullable();
    });

    // Backfill in chunks
    User::query()->whereNull('display_name')->chunkById(500, function ($users) {
        foreach ($users as $user) {
            $user->update(['display_name' => $user->name]);
        }
    });
}
```

For tables > 100k rows, do the backfill in a Job, not the migration. Migrations should be fast.

---

## Eloquent Mastery

### Mass Assignment

```php
final class Post extends Model
{
    protected $fillable = ['title', 'slug', 'body', 'status'];
    // OR (recommended in 2026)
    protected $guarded = [];  // and use FormRequests to whitelist
}
```

If you use `$guarded = []`, your **only** defense is FormRequest validation. Use it religiously.

### Casts (Senior Standard)

```php
protected $casts = [
    'is_published' => 'boolean',
    'published_at' => 'datetime',
    'metadata'     => 'array',
    'status'       => OrderStatus::class,
    'settings'     => AsArrayObject::class,
    'tags'         => AsCollection::class,
    'price'        => MoneyCast::class,
];
```

Always cast. Never let raw DB strings leak into your domain.

### Relationship Patterns

```php
final class Post extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function latestComment(): HasOne
    {
        return $this->hasOne(Comment::class)->latestOfMany();
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}
```

### Scopes

```php
public function scopePublished(Builder $query): Builder
{
    return $query->where('status', 'published')
                 ->whereNotNull('published_at')
                 ->where('published_at', '<=', now());
}

public function scopeForUser(Builder $query, User $user): Builder
{
    return $query->where('user_id', $user->id);
}
```

Use:
```php
Post::published()->forUser($user)->latest()->paginate();
```

### Global Scopes (Use Sparingly)

```php
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if ($tenantId = session('tenant_id')) {
            $builder->where('tenant_id', $tenantId);
        }
    }
}
```

Global scopes hide queries. Use only for cross-cutting tenancy. Document loudly.

---

## The N+1 Demon

The #1 source of slow Laravel apps. Always.

### Spotting It

```php
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->user->name; // <-- 1 query per post
}
```

100 posts = 101 queries.

### Fixing It

```php
$posts = Post::with('user')->get();
foreach ($posts as $post) {
    echo $post->user->name;  // 0 extra queries
}
```

2 queries total.

### Detecting in Dev

`app/Providers/AppServiceProvider.php`:
```php
public function boot(): void
{
    Model::preventLazyLoading(app()->isLocal());
    Model::preventAccessingMissingAttributes(app()->isLocal());
}
```

Now lazy loading throws an exception locally. You can never ship N+1 again.

### Counting Related

```php
Post::withCount('comments')->get();
// Each post now has $post->comments_count
```

### Nested Eager Loading

```php
Post::with([
    'user',
    'comments.user',
    'tags',
])->get();
```

### Conditional Eager Loading

```php
Post::with(['comments' => function ($q) {
    $q->where('is_approved', true)->latest();
}])->get();
```

### Lazy Eager Load

When you already have the model:
```php
$post->load('user', 'comments');
```

---

## Query Performance

### Indexes Are Cheap, Slow Queries Are Expensive

Index any column you:
- WHERE on
- ORDER BY on
- JOIN on

```php
$table->index('email');
$table->index(['user_id', 'created_at']);  // composite
$table->fullText(['title', 'body']);       // Postgres FTS
```

### `EXPLAIN` Every Slow Query

```php
DB::enableQueryLog();
$posts = Post::published()->with('user')->paginate();
dd(DB::getQueryLog());
```

Or use:
```php
Post::published()->toRawSql();
```

Then in psql:
```sql
EXPLAIN ANALYZE SELECT * FROM posts WHERE status = 'published';
```

If you see `Seq Scan` on a table > 10k rows, you're missing an index.

### Chunking Big Datasets

```php
// BAD - loads everything in memory
User::all()->each(fn($u) => $u->sendNewsletter());

// GOOD - chunks
User::chunkById(500, function ($users) {
    foreach ($users as $user) {
        $user->sendNewsletter();
    }
});

// BETTER - lazy collection
User::lazy(500)->each(fn($u) => $u->sendNewsletter());

// BEST for cron jobs - dispatch to queue
User::lazy(500)->each(fn($u) => SendNewsletter::dispatch($u));
```

### Aggregate in Database, Not PHP

```php
// BAD
$total = 0;
foreach (Order::all() as $order) {
    $total += $order->total;
}

// GOOD
$total = Order::sum('total');
```

### Avoid `count()` on Big Tables

Postgres `COUNT(*)` is slow on large tables. Cache it.

```php
$count = Cache::remember('orders:count', 60, fn() => Order::count());
```

Or use Postgres `pg_class` for approximate counts:
```php
$count = DB::scalar('SELECT reltuples::bigint FROM pg_class WHERE relname = ?', ['orders']);
```

---

## Transactions

### Wrap Multi-Step Writes

```php
DB::transaction(function () use ($user, $data) {
    $user->update($data);
    $user->subscriptions()->create([...]);
    $user->roles()->sync($data['roles']);
});
```

If any step throws, all rollback.

### Deadlock Retries

```php
DB::transaction(function () {
    // ...
}, 3);  // retry 3 times on deadlock
```

### Don't Mix HTTP Calls Into Transactions

```php
// BAD - external API in transaction
DB::transaction(function () use ($user) {
    $user->update(['stripe_id' => $stripe->createCustomer()->id]);
});
```

External API takes 500ms-2s. DB connections stay open. Pool exhausts under load.

```php
// GOOD - external call outside, only DB writes inside
$stripeId = $stripe->createCustomer()->id;

DB::transaction(function () use ($user, $stripeId) {
    $user->update(['stripe_id' => $stripeId]);
});
```

---

## Soft Deletes Strategy

```php
use SoftDeletes;
```

User-facing data → soft delete.
Audit/log data → never delete.
Cache/temp data → hard delete.

```php
// Include deleted
Post::withTrashed()->find($id);

// Only deleted
Post::onlyTrashed()->get();

// Restore
$post->restore();

// Permanent delete
$post->forceDelete();
```

**Gotcha:** Soft-deleted rows still consume disk and skew aggregations. Periodically prune:
```php
$schedule->command('prune:trashed-posts')->monthly();
```

---

## JSON Columns (Postgres Strength)

```php
$table->json('preferences');
```

Query inside JSON:
```php
User::where('preferences->notifications->email', true)->get();

User::whereJsonContains('skills', 'PHP')->get();

User::whereJsonLength('badges', '>', 3)->get();
```

Index JSON fields you query often:
```sql
CREATE INDEX users_email_notifications_idx
ON users ((preferences->'notifications'->>'email'));
```

---

## Soft Validation: Database Constraints

Validate at code AND database level:

```php
$table->string('email')->unique();
$table->integer('age')->unsigned();
$table->enum('status', ['active', 'inactive']);
```

Postgres CHECK constraints:
```php
DB::statement('ALTER TABLE posts ADD CONSTRAINT body_length CHECK (length(body) > 10)');
```

Defense in depth.

---

## Query Builder When Eloquent Is Slow

```php
// Faster than Eloquent for raw selects
$users = DB::table('users')
    ->select('id', 'name', 'email')
    ->where('active', true)
    ->limit(1000)
    ->get();
```

When you don't need model events, casts, or relationships, Query Builder is 2-3× faster.

---

## Read Replicas

Render Postgres supports read replicas. In `config/database.php`:

```php
'pgsql' => [
    'read' => [
        'host' => [env('DB_READ_HOST_1'), env('DB_READ_HOST_2')],
    ],
    'write' => [
        'host' => [env('DB_HOST')],
    ],
    'sticky' => true,
    // ...
],
```

Heavy SELECTs → replica. Writes → primary. `sticky` = same request uses primary after a write (avoid replication lag bugs).

---

## Connection Pooling

Laravel persists connections per worker. With many workers:
- Web workers: connection per request
- Queue workers: long-lived
- Total: workers × DB connections

Postgres default max: 100 connections.

If you hit the limit:
1. Reduce queue workers
2. Use PgBouncer (Render supports it on higher plans)
3. Move to managed Postgres with pooler

---

## Backups

Render auto-backs up your DB daily. But:
1. Test restore quarterly. Untested backup ≠ backup.
2. Export critical data to S3 monthly. Render outage shouldn't kill you.

```bash
# In a scheduled job
php artisan backup:run --only-db
```

Use `spatie/laravel-backup`:
```bash
composer require spatie/laravel-backup
```

Set up S3 destination. Daily backups, 30-day retention. Cost: ~$1/mo.

---

## Migration Rollback Discipline

Test rollbacks LOCALLY before deploying migration:

```bash
sail artisan migrate
sail artisan migrate:rollback
sail artisan migrate
```

If rollback fails, your `down()` method is wrong. Fix before deploy.

---

## Production Migration Strategy

```bash
# In Render's release command
php artisan migrate --force --isolated
```

`--isolated` = only one instance runs the migration. Prevents race conditions when scaling.

For destructive migrations (drop column, alter type):
1. Deploy code that doesn't use the column
2. Wait 1 week
3. Deploy the destructive migration
4. Never break running production code

---

## The Senior's DB Cheatsheet

| Need | Use |
|------|-----|
| Find by ID | `User::find($id)` |
| Find or fail | `User::findOrFail($id)` |
| First match | `User::where(...)->first()` |
| Multiple matches | `User::where(...)->get()` |
| Paginate | `User::paginate(15)` |
| Cursor paginate (big data) | `User::cursorPaginate(15)` |
| Eager load | `User::with('posts')` |
| Lazy load | `$user->load('posts')` |
| Count | `User::count()` |
| Aggregate | `User::sum('credits')` |
| Update many | `User::where(...)->update([...])` |
| Insert many | `User::insert([...])` |
| Upsert | `User::upsert([...], ['email'], ['name'])` |
| Atomic increment | `User::where(...)->increment('views')` |
| Raw select | `User::selectRaw('count(*) as total')` |
| Subquery | `User::whereIn('id', Order::select('user_id'))` |
| Exists check | `User::where(...)->exists()` |
| Lock | `User::where(...)->lockForUpdate()->first()` |

---

## Anti-Patterns to Refuse

1. **`Model::all()` on tables > 1000 rows.** Use chunks or pagination.
2. **`$query->get()->count()`.** Use `$query->count()`.
3. **N+1 in production.** Never. Use `preventLazyLoading()`.
4. **Raw SQL with user input.** Use parameter binding.
5. **`SELECT *` everywhere.** Specify columns when you can.
6. **Long-running queries in HTTP requests.** Queue them.
7. **Polling for changes.** Use events + queues.
8. **`saveQuietly` to bypass events.** Architecture smell.
9. **Models with 20+ relationships.** Probably 2+ models.
10. **`$model->fresh()` after every write.** Lazy. Trust your code.

Master this file. Become unkillable.
