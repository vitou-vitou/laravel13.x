# 15 — Quick Reference

> The cheatsheet. Print it. Bookmark it. Tape it to your monitor.

---

## Artisan Cheatsheet

### Project
```bash
composer create-project laravel/laravel my-app
php artisan key:generate
php artisan storage:link
php artisan optimize           # cache config, routes, views, events
php artisan optimize:clear     # clear all caches
```

### Migration
```bash
php artisan make:migration create_posts_table
php artisan migrate
php artisan migrate --force                  # production
php artisan migrate --pretend                # show SQL, don't run
php artisan migrate --isolated               # multi-instance safe
php artisan migrate:rollback
php artisan migrate:fresh --seed
php artisan migrate:status
```

### Make
```bash
php artisan make:model Post -mfsc            # migration + factory + seeder + controller
php artisan make:controller PostController --resource --model=Post
php artisan make:request StorePostRequest
php artisan make:resource PostResource
php artisan make:policy PostPolicy --model=Post
php artisan make:job ChargeCustomer
php artisan make:event PostPublished
php artisan make:listener NotifySubscribers --event=PostPublished
php artisan make:notification PostPublishedNotification
php artisan make:middleware EnsureAdmin
php artisan make:command BackupDatabase
php artisan make:test PostTest --pest
php artisan make:cast MoneyCast
php artisan make:enum OrderStatus
php artisan make:rule UniqueSlug
php artisan make:provider PaymentServiceProvider
```

### Queues
```bash
php artisan queue:work
php artisan queue:work --queue=high,default
php artisan queue:listen                    # auto-restart on changes (dev)
php artisan queue:retry all
php artisan queue:retry 5                   # by ID
php artisan queue:flush                     # delete all failed
php artisan queue:failed                    # list failed
php artisan queue:forget 5                  # delete one failed
php artisan queue:prune-failed --hours=720
php artisan horizon
php artisan horizon:terminate                # restart workers after deploy
php artisan horizon:status
```

### Scheduling
```bash
php artisan schedule:run                    # called by cron every minute
php artisan schedule:work                   # local dev (foreground)
php artisan schedule:list
php artisan schedule:test                   # run one task manually
php artisan schedule:clear-cache
```

### Cache
```bash
php artisan cache:clear
php artisan cache:forget key
php artisan config:cache
php artisan config:clear
php artisan route:cache
php artisan route:clear
php artisan view:cache
php artisan view:clear
php artisan event:cache
php artisan event:clear
```

### Database
```bash
php artisan db                              # interactive psql
php artisan db:seed
php artisan db:seed --class=PostSeeder
php artisan db:wipe
php artisan db:show
php artisan db:table users
php artisan model:show User
```

### Maintenance
```bash
php artisan down
php artisan down --secret=abc123            # bypass with /abc123
php artisan up
```

### Debug
```bash
php artisan tinker
php artisan pail                            # live log tail
php artisan pail --filter=error
php artisan pail --user=42
php artisan route:list
php artisan route:list --columns=method,uri
php artisan about                           # show full app info
```

### Testing
```bash
php artisan test
php artisan test --parallel
php artisan test --filter=PostTest
php artisan test --coverage --min=70
./vendor/bin/pest tests/Feature/PostTest.php
./vendor/bin/pest --watch
```

### Code Quality
```bash
./vendor/bin/pint                           # format all
./vendor/bin/pint --dirty                   # format git-dirty files
./vendor/bin/pint --test                    # check, don't fix
./vendor/bin/phpstan analyse
./vendor/bin/phpstan analyse --memory-limit=2G
composer audit
npm audit
```

---

## Eloquent Cheatsheet

```php
// Read
$user = User::find(1);
$user = User::findOrFail(1);
$user = User::where('email', $email)->first();
$users = User::all();                       // careful with big tables
$users = User::pluck('name', 'id');
$count = User::count();

// Create
$user = User::create(['name' => 'Foo']);
$user = User::firstOrCreate(['email' => $e], ['name' => $n]);
$user = User::updateOrCreate(['email' => $e], ['name' => $n]);

// Update
$user->update(['name' => 'Bar']);
User::where('active', false)->update(['archived_at' => now()]);

// Delete
$user->delete();
User::destroy([1, 2, 3]);
User::where('inactive', true)->delete();

// Soft delete
$user->delete();                            // soft if SoftDeletes trait
$user->restore();
$user->forceDelete();                       // permanent
User::withTrashed()->find(1);
User::onlyTrashed()->get();

// Relations
$user->posts;                               // many
$user->posts()->create([...]);
$user->posts()->where('published', true)->get();
$user->roles()->sync([1, 2, 3]);
$user->roles()->attach($roleId);
$user->roles()->detach($roleId);

// Eager loading (FIX N+1)
User::with('posts')->get();
User::with('posts.comments.user')->get();
User::with(['posts' => fn($q) => $q->published()])->get();
User::withCount('posts')->get();
User::withSum('orders', 'total')->get();
User::withMax('orders', 'created_at')->get();

// Lazy loading prevention (DEV)
Model::preventLazyLoading(! app()->isProduction());

// Aggregates
User::count();
User::sum('credits');
User::avg('age');
User::max('created_at');

// Pagination
User::paginate(15);
User::simplePaginate(15);
User::cursorPaginate(15);                   // for big tables

// Chunking
User::chunk(100, fn($users) => /* ... */);
User::chunkById(100, fn($users) => /* ... */);
User::lazy()->each(/* ... */);

// Raw
User::whereRaw('LOWER(email) = ?', [$email])->first();
DB::select('SELECT * FROM users WHERE id = ?', [$id]);
DB::statement('TRUNCATE TABLE users');

// Locking
DB::transaction(function () use ($id) {
    $user = User::lockForUpdate()->find($id);
    $user->update(['credits' => $user->credits + 10]);
});

// Exists / Missing
User::where('email', $email)->exists();
User::where('email', $email)->doesntExist();
```

---

## Validation Rules Cheatsheet

```php
// Basics
'name' => 'required|string|max:255'
'email' => 'required|email|unique:users,email'
'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)]
'age' => 'integer|between:18,99'
'avatar' => 'image|mimes:jpg,png,webp|max:2048'

// Conditional
'card' => 'required_if:payment_method,card'
'role' => Rule::requiredIf(fn() => $request->user()->isAdmin())

// Custom
'slug' => ['required', new UniqueSlug]
'phone' => ['required', 'regex:/^\+?[0-9]{10,15}$/']

// Nested
'tags' => 'array|max:10'
'tags.*' => 'string|max:50'
'addresses.*.city' => 'required|string'

// Enums
'status' => ['required', new Enum(OrderStatus::class)]

// Passwords
'password' => ['required', Password::defaults(), 'confirmed']

// Files
'document' => 'file|mimes:pdf|max:5120'

// Dates
'born' => 'date|before:18 years ago'
'starts_at' => 'date|after:today'

// In list
'role' => ['required', Rule::in(['admin', 'editor', 'user'])]
'role' => 'required|in:admin,editor,user'

// Exists
'category_id' => 'required|exists:categories,id'
```

---

## Blade Cheatsheet

```blade
{{-- Output (escaped) --}}
{{ $user->name }}

{{-- Raw output (DANGEROUS) --}}
{!! $user->bio !!}

{{-- Conditionals --}}
@if($user->isAdmin())
@elseif($user->isEditor())
@else
@endif

@unless($user->isAdmin())
@endunless

@isset($user)
@endisset

@empty($posts)
    No posts
@endempty

{{-- Loops --}}
@foreach($users as $user)
    {{ $loop->iteration }} / {{ $loop->count }}
    {{ $user->name }}
@endforeach

@forelse($posts as $post)
    {{ $post->title }}
@empty
    No posts yet
@endforelse

@for($i = 0; $i < 5; $i++)
@endfor

{{-- Components --}}
<x-button type="primary">Click me</x-button>
<x-slot:trailing>...</x-slot>

{{-- Includes --}}
@include('partials.header')
@includeWhen($user->isAdmin(), 'partials.admin')

{{-- Auth --}}
@auth
    Hi, {{ auth()->user()->name }}
@endauth

@guest
    <a href="/login">Login</a>
@endguest

{{-- Auth + Role --}}
@can('update', $post)
    <button>Edit</button>
@endcan

@role('admin')
@endrole

{{-- Layouts --}}
@extends('layouts.app')
@section('title', 'Posts')
@section('content')
    @parent
@endsection

{{-- Stacks (CSS/JS injection) --}}
@push('scripts')
    <script>...</script>
@endpush
@stack('scripts')

{{-- CSRF --}}
@csrf
@method('PUT')

{{-- Translations --}}
{{ __('Welcome, :name', ['name' => $user->name]) }}

{{-- Pagination --}}
{{ $posts->links() }}
```

---

## Inertia + Vue Cheatsheet

```php
// Controller
return Inertia::render('Posts/Index', [
    'posts' => PostResource::collection(Post::published()->paginate()),
    'filters' => $request->only(['search', 'status']),
]);

// Lazy prop
return Inertia::render('Dashboard', [
    'analytics' => Inertia::lazy(fn() => $this->expensiveQuery()),
]);

// Share global
Inertia::share('auth.user', fn() =>
    auth()->user() ? UserResource::make(auth()->user()) : null
);
```

```vue
<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3'

const form = useForm({
    title: '',
    body: '',
})

const submit = () => form.post('/posts', {
    onSuccess: () => router.reload(),
})
</script>

<template>
    <Head title="Create Post" />

    <form @submit.prevent="submit">
        <input v-model="form.title">
        <p v-if="form.errors.title">{{ form.errors.title }}</p>

        <button :disabled="form.processing">Submit</button>
    </form>

    <Link href="/posts">Back</Link>
</template>
```

---

## Render CLI Cheatsheet

```bash
# Install
brew install render
render login

# Services
render services list
render services list --type=web

# Deploy
render deploys create my-saas

# Logs
render logs my-saas -f
render logs my-saas --since 1h

# Shell
render ssh my-saas
render run my-saas -- php artisan tinker

# Database
render psql my-saas-db
render psql my-saas-db -c "SELECT count(*) FROM users"

# Redis
render redis-cli my-saas-redis

# Restart
render deploys create my-saas
```

---

## Git Cheatsheet (Senior)

```bash
# Status
git status
git diff
git diff --cached                          # staged changes
git log --oneline -20
git log --graph --oneline --all -20

# Stage
git add -p                                 # interactive (best)
git add file.php
git restore --staged file.php              # unstage

# Commit
git commit -m "feat(posts): add bulk delete"
git commit --amend                         # change last commit (before push)
git commit --fixup HEAD~2                  # mark for rebase squash

# Branch
git branch
git checkout -b feature/foo
git switch -c feature/foo
git branch -d feature/foo                  # delete merged

# Rebase
git rebase main
git rebase -i HEAD~5                       # interactive
git rebase --abort
git rebase --continue

# Stash
git stash
git stash pop
git stash list
git stash show -p

# Remote
git push -u origin feature/foo
git push --force-with-lease                # safer than --force
git pull --rebase
git fetch --all --prune

# Worktree (parallel branches)
git worktree add ../foo-branch foo-branch
git worktree list
git worktree remove ../foo-branch

# Inspect
git blame file.php
git log -p file.php                        # history of file
git log --follow file.php                  # across renames
git bisect start
git bisect bad
git bisect good v1.0
```

---

## Docker Cheatsheet

```bash
# Sail
sail up -d
sail down
sail down -v                               # also remove volumes
sail logs -f                               # tail logs
sail artisan migrate
sail composer install
sail npm install
sail shell                                 # bash in container

# Docker
docker ps
docker ps -a                                # all (including stopped)
docker logs -f <container>
docker exec -it <container> bash
docker compose up -d
docker compose down
docker system prune -a                     # nuke everything unused
docker volume ls
docker volume prune
```

---

## SQL Cheatsheet (Postgres)

```sql
-- Inspect
\dt                                        -- list tables
\d users                                   -- describe table
\du                                        -- list users
\l                                         -- list databases
\timing                                    -- show query times

-- Explain
EXPLAIN ANALYZE SELECT * FROM users WHERE email = 'x';

-- Slow queries (with pg_stat_statements)
SELECT query, calls, mean_exec_time
FROM pg_stat_statements
ORDER BY mean_exec_time DESC LIMIT 20;

-- Index usage
SELECT * FROM pg_stat_user_indexes;

-- Table sizes
SELECT relname AS table, pg_size_pretty(pg_total_relation_size(relid)) AS size
FROM pg_catalog.pg_statio_user_tables
ORDER BY pg_total_relation_size(relid) DESC LIMIT 10;

-- Locks
SELECT * FROM pg_locks WHERE NOT granted;

-- Active queries
SELECT pid, state, query, query_start
FROM pg_stat_activity
WHERE state != 'idle' ORDER BY query_start;

-- Kill a query
SELECT pg_cancel_backend(pid);
SELECT pg_terminate_backend(pid);

-- Vacuum
VACUUM ANALYZE users;
VACUUM FULL users;                         -- locks table, do off-hours

-- Reindex
REINDEX TABLE users;
REINDEX TABLE CONCURRENTLY users;          -- online
```

---

## Common Composer Packages (Senior Defaults)

### Production
```
spatie/laravel-permission         # roles & permissions
spatie/laravel-medialibrary       # file uploads + thumbnails
spatie/laravel-activitylog        # audit log
spatie/laravel-backup             # auto backup to S3
spatie/laravel-query-builder      # API query patterns
spatie/laravel-data               # typed DTOs
laravel/horizon                   # queue dashboard
laravel/sanctum                   # API auth
laravel/scout                     # search
sentry/sentry-laravel             # error tracking
predis/predis                     # Redis client (or use phpredis ext)
league/flysystem-aws-s3-v3        # S3 storage
intervention/image                # image manipulation
maatwebsite/excel                 # Excel import/export
barryvdh/laravel-dompdf           # PDF generation
```

### Dev only
```
laravel/sail                      # Docker dev environment
laravel/telescope                 # request debugging
laravel/breeze                    # auth scaffold
laravel/pint                      # code formatter
larastan/larastan                 # static analysis
pestphp/pest                      # testing
pestphp/pest-plugin-laravel
nunomaduro/collision              # CLI errors
barryvdh/laravel-debugbar         # in-browser debugging
spatie/laravel-ignition           # better error pages
brianium/paratest                 # parallel testing
```

---

## NPM Packages (Senior Defaults)

### Vue Inertia stack
```json
{
  "@inertiajs/vue3": "^2.0",
  "vue": "^3.5",
  "vite": "^6",
  "tailwindcss": "^4",
  "@vitejs/plugin-vue": "^5",
  "typescript": "^5",
  "pinia": "^3",
  "@headlessui/vue": "^1.7",
  "lucide-vue-next": "^0.4",
  "@vueuse/core": "^11",
  "axios": "^1.7",
  "vee-validate": "^4",
  "@vee-validate/zod": "^4",
  "zod": "^3",
  "vue-sonner": "^1"
}
```

---

## HTTP Status Cheatsheet

| Code | Meaning | Use For |
|------|---------|---------|
| 200 | OK | Successful GET/PUT |
| 201 | Created | Successful POST creating a resource |
| 204 | No Content | Successful DELETE |
| 301 | Moved Permanently | URL changed forever |
| 302 | Found | Temporary redirect |
| 304 | Not Modified | Cache hit (ETag/If-Modified-Since) |
| 400 | Bad Request | Generic client error |
| 401 | Unauthorized | Not authenticated |
| 403 | Forbidden | Authenticated, no permission |
| 404 | Not Found | Resource doesn't exist |
| 409 | Conflict | State conflict (already exists, version mismatch) |
| 422 | Unprocessable Entity | Validation failed (Laravel default) |
| 429 | Too Many Requests | Rate limited |
| 500 | Internal Server Error | Server bug |
| 502 | Bad Gateway | Upstream service down |
| 503 | Service Unavailable | Degraded, retry later |

---

## The Senior's Daily Commands

Morning:
```bash
git pull --rebase
sail up -d
sail artisan migrate
git log --oneline -10                      # what's new
gh pr list                                 # open PRs
```

End of day:
```bash
git add -p
git commit -m "WIP: feature foo"           # don't lose work
git push origin feature/foo
sail stop
```

Before merging to main:
```bash
sail artisan test --parallel
./vendor/bin/pint
./vendor/bin/phpstan analyse
composer audit
gh pr create
```

---

## The Senior's "I'm Stuck" Algorithm

1. Re-read the error message. (50% solved)
2. `git diff` your changes. (75% solved)
3. Check Telescope / Pail logs. (90% solved)
4. Reproduce in tinker.
5. Read the relevant Laravel source code.
6. Google the exact error message in quotes.
7. Ask in Discord / Stack Overflow with a minimal reproduction.
8. Sleep on it.
9. Walk away for 15 min.
10. Ask a duck (rubber duck debugging).

Don't skip steps. The first three solve 90% of issues.

---

## Print This Page

These commands and patterns are your operating system. Memorize the top 80%. Reference the rest forever.

You're now a senior Laravel dev. Go ship something.
