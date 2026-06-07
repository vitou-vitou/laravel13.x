# 02 — Local Development Setup

> Your machine is your factory. A janky factory makes janky output. Spend a day setting up. Save a year of friction.

---

## The 3 Local Dev Stacks (Pick One)

### Option A — Laravel Sail (Docker, recommended for parity)

**Pros:** Mirrors Render's Docker production exactly. Multi-project isolation. No "works on my machine."
**Cons:** Slower file I/O on Windows/Mac. Heavier on RAM.

### Option B — Laravel Herd (Native, fastest)

**Pros:** Sub-second response times. Native PHP, no Docker. Pro version adds DB, Redis, queues.
**Cons:** Less parity with prod. Requires Herd Pro for serious use ($99/yr).

### Option C — Valet (Mac only, lightweight)

**Pros:** Free, fast, native.
**Cons:** Mac-only. No bundled DB/Redis.

**Recommendation:** Sail in CI and team projects. Herd Pro for solo speed work. Both, alternating.

---

## Windows Setup (Senior-Optimized)

### Step 1 — Core Tooling

```powershell
# Install via winget
winget install -e --id Microsoft.PowerShell
winget install -e --id Git.Git
winget install -e --id GitHub.cli
winget install -e --id Docker.DockerDesktop
winget install -e --id Microsoft.WindowsTerminal
winget install -e --id JetBrains.PhpStorm
winget install -e --id OpenJS.NodeJS.LTS

# Composer
winget install -e --id ShiningLight.OpenSSL
winget install -e --id Composer.Composer
```

### Step 2 — WSL2 for Docker

```powershell
wsl --install
wsl --set-default-version 2
```

Put projects in WSL filesystem (`/home/you/projects`), NOT Windows mount. 10× faster I/O.

### Step 3 — PHP (Native, for non-Docker scripts)

```powershell
# Use PHP via Herd Pro or scoop
scoop install php
scoop install composer
```

### Step 4 — Editor: PhpStorm or VS Code

**PhpStorm essentials:**
- Laravel Idea plugin ($39/yr) — autocomplete blade, routes, eloquent
- Symfony plugin
- .env files plugin
- Database tool (built-in)

**VS Code essentials:**
- Intelephense (Premium $15 one-time)
- Laravel Extension Pack
- Tailwind CSS IntelliSense
- GitLens
- Error Lens
- DotENV

---

## Mac Setup (Senior-Optimized)

### Step 1 — Homebrew Base

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

brew install --cask docker
brew install --cask herd
brew install --cask phpstorm
brew install --cask iterm2
brew install gh node@22 mysql redis
brew install --cask tableplus
```

### Step 2 — Shell: Zsh + Starship + Oh My Zsh

```bash
sh -c "$(curl -fsSL https://raw.githubusercontent.com/ohmyzsh/ohmyzsh/master/tools/install.sh)"
brew install starship
echo 'eval "$(starship init zsh)"' >> ~/.zshrc
```

### Step 3 — Herd Pro Setup

Open Herd → Sites → Add Site → Point at `~/projects` → All Laravel projects auto-served at `projectname.test`.

---

## The Standard Laravel Sail Setup

```bash
cd ~/projects
composer create-project laravel/laravel my-app
cd my-app

# Install Sail with the services you need
php artisan sail:install --with=pgsql,redis,mailpit,minio

# Start
./vendor/bin/sail up -d

# Alias 'sail' globally (save your wrists)
echo "alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'" >> ~/.zshrc
```

Now `sail artisan migrate`, `sail npm run dev`, `sail composer require ...` work seamlessly.

### Recommended `docker-compose.yml` Tweaks

```yaml
# Add to services.laravel.test
services:
  laravel.test:
    extra_hosts:
      - 'host.docker.internal:host-gateway'
    environment:
      WWWUSER: '${WWWUSER}'
      LARAVEL_SAIL: 1
      XDEBUG_MODE: '${SAIL_XDEBUG_MODE:-off}'
      XDEBUG_CONFIG: '${SAIL_XDEBUG_CONFIG:-client_host=host.docker.internal}'
```

Enables Xdebug on demand without rebuilding.

---

## The `.env` Discipline

### Local `.env` (NEVER commit)

```env
APP_NAME="My App"
APP_ENV=local
APP_KEY=base64:GENERATED
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=app
DB_USERNAME=sail
DB_PASSWORD=password

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
CACHE_STORE=redis

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025

# Mirror production keys with FAKE values
AWS_ACCESS_KEY_ID=fake
AWS_SECRET_ACCESS_KEY=fake
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=fake
```

### Critical Rules

1. **`.env.example` is the contract.** Update it whenever you add a new env var. Never let it drift.
2. **Production `.env` lives in Render dashboard.** Never in repo. Never in code.
3. **Local DB credentials should be different from production.** No accidents.
4. **Use `php artisan key:generate` first thing.** Without `APP_KEY`, encryption fails silently in subtle ways.

---

## Database Workflow (Local)

### PostgreSQL via Sail

```bash
sail up -d
sail artisan migrate:fresh --seed
```

### Connecting GUI Client

- **TablePlus** (Mac/Win, $89, worth it)
- **DBeaver** (free, slower UI)
- **DataGrip** (JetBrains, included with All Pack)

Connection: `localhost:5432`, user `sail`, pass `password`, db `app`.

### Seeding Strategy

```php
// database/seeders/DatabaseSeeder.php
public function run(): void
{
    if (app()->environment('local')) {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        User::factory(20)->create();
        Post::factory(100)->for(User::inRandomOrder()->first())->create();
    }
}
```

`sail artisan migrate:fresh --seed` gives you a working dataset every morning. No more "let me create a test user."

---

## The Daily Local Dev Loop

```bash
# Morning
sail up -d
sail artisan migrate
sail npm run dev   # Vite HMR

# Code...

# Test
sail artisan test --parallel
sail artisan test --filter=UserTest

# Format & lint
sail composer pint
sail composer phpstan

# Commit
git add -p
git commit -m "feat(user): add bulk delete"

# Done for the day
sail stop
```

---

## Xdebug (Only When You Need It)

Don't run Xdebug always — it slows everything 30%.

```bash
SAIL_XDEBUG_MODE=develop,debug,coverage sail up -d
```

In PhpStorm:
- Settings → PHP → Debug → Port: 9003
- Run → Start Listening for PHP Debug Connections
- Set breakpoint, refresh browser, magic happens

---

## Telescope (Local Only)

```bash
sail composer require laravel/telescope --dev
sail artisan telescope:install
sail artisan migrate
```

Go to `http://localhost/telescope`. See:
- Every query
- Every job
- Every mail
- Every notification
- Every cache hit/miss
- Every request lifecycle

**Critical:** Telescope is `--dev` only. NEVER deploy it to production. It leaks everything.

---

## Mailpit Workflow

All emails sent in local dev go to Mailpit, not real inboxes.

- Web UI: `http://localhost:8025`
- View HTML, plain text, raw source
- Test 100 emails without spamming yourself

---

## The Tinker Habit

```bash
sail artisan tinker
```

```php
>>> User::factory()->create()
>>> Cache::remember('foo', 60, fn() => Post::count())
>>> dispatch(new SendWelcomeEmail($user))
>>> Storage::disk('s3')->exists('test.jpg')
```

Tinker is your live REPL. Use it to:
- Test queries before writing code
- Reproduce bugs interactively
- Manually trigger jobs/mail
- Inspect production-like data

---

## Git Hooks (Auto-Quality)

`.git/hooks/pre-commit`:

```bash
#!/bin/sh
./vendor/bin/pint --dirty
./vendor/bin/phpstan analyse --memory-limit=2G
./vendor/bin/pest --filter=Unit
```

Or use Husky:

```bash
npm install --save-dev husky lint-staged
npx husky install
```

`package.json`:
```json
{
  "lint-staged": {
    "*.php": ["./vendor/bin/pint", "./vendor/bin/phpstan analyse"]
  }
}
```

---

## Aliases That Save Your Life

```bash
# ~/.zshrc or ~/.bashrc
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
alias art='sail artisan'
alias mfs='sail artisan migrate:fresh --seed'
alias tinker='sail artisan tinker'
alias logs='sail artisan pail'
alias test='sail artisan test --parallel'
alias gst='git status'
alias gco='git checkout'
alias gcm='git checkout main && git pull'
alias gnb='git checkout -b'
alias gp='git push'
```

After 6 months these become muscle memory.

---

## The Laravel Pail Stream

Live tail of every log:

```bash
sail artisan pail
sail artisan pail --filter=error
sail artisan pail --user=42
```

Better than `tail -f storage/logs/laravel.log`. Color-coded, filterable, fast.

---

## Performance Checks Before Push

```bash
# How long does the test suite take?
time sail artisan test

# Profile a single request
sail artisan tinker
>>> $start = microtime(true);
>>> User::with('posts.comments')->take(100)->get();
>>> echo microtime(true) - $start;
```

If something looks slow locally, it's brutal in production.

---

## Mac/Linux Power Combo: `fzf` + `rg` + `bat`

```bash
brew install fzf ripgrep bat
```

Search code:
```bash
rg "User::create" --type php
```

Fuzzy-find file:
```bash
fzf
```

Better cat:
```bash
bat app/Models/User.php
```

These three save 10 minutes a day. Multiply by years.

---

## The "Reset Everything" Script

Sometimes the local dev breaks. Have a `bin/reset.sh`:

```bash
#!/bin/sh
set -e

sail down -v
docker system prune -f
sail up -d
sail composer install
sail npm install
sail artisan key:generate
sail artisan migrate:fresh --seed
sail artisan storage:link
sail artisan cache:clear
sail artisan config:clear
sail artisan view:clear

echo "Reset complete. Visit http://localhost"
```

Run when in doubt.

---

## When Setup Pays Off

A senior dev's local setup pays off in:
- **Faster feedback loops** (test in 8s, not 80s)
- **Fewer "works on my machine"** issues (Docker parity)
- **Confident commits** (pre-commit hooks catch dumb stuff)
- **Easy onboarding** (new dev clones, runs `bin/setup.sh`, works)
- **Better focus** (less time fighting tools, more shipping)

A janky setup wastes 1 hour/day. Over a year, that's 6 working weeks lost.

Spend the day. Set it up right. Reap the decade.
