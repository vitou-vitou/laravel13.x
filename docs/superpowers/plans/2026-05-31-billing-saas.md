# Billing SaaS Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build `examples/billing-saas` — a Link Tracker SaaS with Stripe subscription billing, plan limit enforcement, idempotent webhooks, and queued usage tracking.

**Architecture:** Multi-tenant (Team-scoped) Laravel app. Cashier handles subscription lifecycle. `PlanLimitService` is the single source of truth for plan constraints. Click recording is queued. Webhook handler is idempotent via `processed_webhook_events` table.

**Tech Stack:** Laravel 13, Breeze (Blade), Laravel Cashier Stripe, SQLite (dev), Pest, database queue (dev)

---

## File Map

```
examples/billing-saas/
├── app/
│   ├── Enums/PlanTier.php
│   ├── Events/LinkCreated.php
│   ├── Exceptions/PlanLimitExceededException.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── LinkController.php
│   │   │   ├── RedirectController.php
│   │   │   └── Billing/
│   │   │       ├── CheckoutController.php
│   │   │       ├── PortalController.php
│   │   │       └── WebhookController.php
│   │   ├── Middleware/
│   │   │   ├── EnsureTeamSubscribed.php
│   │   │   └── ScopedToTeam.php
│   │   └── Requests/StoreLinkRequest.php
│   ├── Jobs/
│   │   ├── RecordClickJob.php
│   │   ├── PurgeExpiredClicksJob.php
│   │   └── SyncUsageToStripeJob.php
│   ├── Models/
│   │   ├── Team.php
│   │   ├── Link.php
│   │   ├── Click.php
│   │   ├── UsageRecord.php
│   │   └── ProcessedWebhookEvent.php
│   ├── Notifications/PaymentFailedNotification.php
│   └── Services/
│       ├── BillingService.php
│       ├── CreateLinkService.php
│       └── PlanLimitService.php
├── database/
│   ├── factories/
│   │   ├── TeamFactory.php
│   │   └── LinkFactory.php
│   └── migrations/
│       ├── xxxx_create_teams_table.php
│       ├── xxxx_create_team_user_table.php
│       ├── xxxx_create_links_table.php
│       ├── xxxx_create_clicks_table.php
│       ├── xxxx_create_usage_records_table.php
│       └── xxxx_create_processed_webhook_events_table.php
├── resources/views/
│   ├── dashboard.blade.php
│   ├── links/index.blade.php
│   ├── links/stats.blade.php
│   └── billing/index.blade.php
├── routes/web.php
├── routes/console.php
└── tests/
    ├── Feature/
    │   ├── CreateLinkTest.php
    │   ├── RecordClickTest.php
    │   ├── BillingTest.php
    │   ├── WebhookTest.php
    │   └── PlanLimitTest.php
    ├── Unit/
    │   └── PlanLimitServiceTest.php
    └── fixtures/stripe/
        ├── subscription.deleted.json
        └── invoice.payment_failed.json
```

---

### Task 1: Scaffold app + install dependencies

**Files:**
- Create: `examples/billing-saas/` (new Laravel project)

- [ ] **Step 1: Create project with Breeze**

```bash
cd D:\laravel13.x\examples
composer create-project laravel/laravel billing-saas
cd billing-saas
composer require laravel/breeze --dev
php artisan breeze:install blade
```

- [ ] **Step 2: Install Cashier**

```bash
composer require laravel/cashier
```

- [ ] **Step 3: Configure .env**

```bash
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
```

Edit `.env`:
```
APP_NAME="Billing SaaS"
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/examples/billing-saas/database/database.sqlite
STRIPE_KEY=pk_test_your_key
STRIPE_SECRET=sk_test_your_key
STRIPE_WEBHOOK_SECRET=whsec_your_secret
CASHIER_CURRENCY=usd
```

- [ ] **Step 4: Build assets**

```bash
npm install && npm run build
```

- [ ] **Step 5: Verify Laravel boots**

```bash
php artisan --version
```
Expected: `Laravel Framework 13.x.x`

- [ ] **Step 6: Commit**

```bash
git add examples/billing-saas
git commit -m "feat(billing-saas): scaffold with Breeze + Cashier"
```

---

### Task 2: Migrations

**Files:**
- Create: `database/migrations/xxxx_create_teams_table.php`
- Create: `database/migrations/xxxx_create_team_user_table.php`
- Create: `database/migrations/xxxx_create_links_table.php`
- Create: `database/migrations/xxxx_create_clicks_table.php`
- Create: `database/migrations/xxxx_create_usage_records_table.php`
- Create: `database/migrations/xxxx_create_processed_webhook_events_table.php`
- Modify: `app/Models/User.php`

- [ ] **Step 1: Generate migration stubs + install Cashier migrations**

```bash
php artisan make:migration create_teams_table
php artisan make:migration create_team_user_table
php artisan make:migration create_links_table
php artisan make:migration create_clicks_table
php artisan make:migration create_usage_records_table
php artisan make:migration create_processed_webhook_events_table
php artisan cashier:install
php artisan notifications:table
php artisan queue:table
```

- [ ] **Step 2: Write teams migration**

```php
// database/migrations/xxxx_create_teams_table.php
public function up(): void
{
    Schema::create('teams', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
        $table->string('name');
        $table->string('slug')->unique();
        $table->string('plan')->default('free');
        $table->timestamp('subscription_ends_at')->nullable();
        $table->boolean('on_grace_period')->default(false);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('teams');
}
```

- [ ] **Step 3: Write team_user migration**

```php
// database/migrations/xxxx_create_team_user_table.php
public function up(): void
{
    Schema::create('team_user', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('team_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('role')->default('member');
        $table->timestamps();
        $table->unique(['team_id', 'user_id']);
    });
}

public function down(): void
{
    Schema::dropIfExists('team_user');
}
```

- [ ] **Step 4: Write links migration**

```php
// database/migrations/xxxx_create_links_table.php
public function up(): void
{
    Schema::create('links', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('team_id')->constrained()->cascadeOnDelete();
        $table->string('slug', 12)->unique();
        $table->string('original_url');
        $table->string('title')->nullable();
        $table->boolean('active')->default(true);
        $table->softDeletes();
        $table->timestamps();
        $table->index(['team_id', 'active']);
    });
}

public function down(): void
{
    Schema::dropIfExists('links');
}
```

- [ ] **Step 5: Write clicks migration**

```php
// database/migrations/xxxx_create_clicks_table.php
public function up(): void
{
    Schema::create('clicks', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('link_id')->constrained()->cascadeOnDelete();
        $table->string('ip_hash', 64)->nullable();
        $table->string('country', 2)->nullable();
        $table->string('referrer')->nullable();
        $table->string('user_agent')->nullable();
        $table->timestamp('clicked_at');
        $table->index(['link_id', 'clicked_at']);
    });
}

public function down(): void
{
    Schema::dropIfExists('clicks');
}
```

- [ ] **Step 6: Write usage_records migration**

```php
// database/migrations/xxxx_create_usage_records_table.php
public function up(): void
{
    Schema::create('usage_records', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('team_id')->constrained()->cascadeOnDelete();
        $table->string('metric');
        $table->unsignedBigInteger('value')->default(0);
        $table->string('period', 7);
        $table->timestamps();
        $table->unique(['team_id', 'metric', 'period']);
    });
}

public function down(): void
{
    Schema::dropIfExists('usage_records');
}
```

- [ ] **Step 7: Write processed_webhook_events migration**

```php
// database/migrations/xxxx_create_processed_webhook_events_table.php
public function up(): void
{
    Schema::create('processed_webhook_events', function (Blueprint $table): void {
        $table->id();
        $table->string('stripe_event_id')->unique();
        $table->string('type');
        $table->timestamp('processed_at');
        $table->index('stripe_event_id');
    });
}

public function down(): void
{
    Schema::dropIfExists('processed_webhook_events');
}
```

- [ ] **Step 8: Update User model**

```php
// app/Models/User.php — add these imports and traits:
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;

// Inside class, add trait:
use HasApiTokens, HasFactory, Notifiable, Billable;

// Add relationships:
public function teams(): BelongsToMany
{
    return $this->belongsToMany(Team::class)->withPivot('role')->withTimestamps();
}

public function ownedTeams(): HasMany
{
    return $this->hasMany(Team::class, 'owner_id');
}
```

- [ ] **Step 9: Run migrations**

```bash
php artisan migrate
php artisan migrate:status
```
Expected: All migrations show `Ran`.

- [ ] **Step 10: Commit**

```bash
git add database/migrations app/Models/User.php
git commit -m "feat(billing-saas): add all migrations, Billable User"
```

---

### Task 3: PlanTier enum + PlanLimitService (TDD)

**Files:**
- Create: `app/Enums/PlanTier.php`
- Create: `app/Services/PlanLimitService.php`
- Create: `tests/Unit/PlanLimitServiceTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Unit/PlanLimitServiceTest.php
declare(strict_types=1);

use App\Enums\PlanTier;
use App\Services\PlanLimitService;

it('free plan link limit is 10', function (): void {
    expect(PlanLimitService::linkLimit(PlanTier::Free))->toBe(10);
});

it('pro plan link limit is 500', function (): void {
    expect(PlanLimitService::linkLimit(PlanTier::Pro))->toBe(500);
});

it('business plan has no link limit', function (): void {
    expect(PlanLimitService::linkLimit(PlanTier::Business))->toBeNull();
});

it('free plan retention is 7 days', function (): void {
    expect(PlanLimitService::retentionDays(PlanTier::Free))->toBe(7);
});

it('pro plan retention is 90 days', function (): void {
    expect(PlanLimitService::retentionDays(PlanTier::Pro))->toBe(90);
});

it('business plan retention is 365 days', function (): void {
    expect(PlanLimitService::retentionDays(PlanTier::Business))->toBe(365);
});

it('free plan seat limit is 1', function (): void {
    expect(PlanLimitService::seatLimit(PlanTier::Free))->toBe(1);
});

it('pro plan seat limit is 5', function (): void {
    expect(PlanLimitService::seatLimit(PlanTier::Pro))->toBe(5);
});

it('business plan seat limit is 20', function (): void {
    expect(PlanLimitService::seatLimit(PlanTier::Business))->toBe(20);
});

it('canCreateLink returns false when at limit', function (): void {
    expect(PlanLimitService::canCreateLink(PlanTier::Free, 10))->toBeFalse();
});

it('canCreateLink returns true when under limit', function (): void {
    expect(PlanLimitService::canCreateLink(PlanTier::Free, 9))->toBeTrue();
});

it('canCreateLink always returns true for business', function (): void {
    expect(PlanLimitService::canCreateLink(PlanTier::Business, 999999))->toBeTrue();
});
```

- [ ] **Step 2: Run — verify FAIL**

```bash
php artisan test tests/Unit/PlanLimitServiceTest.php
```
Expected: FAIL — `App\Enums\PlanTier not found`

- [ ] **Step 3: Create PlanTier enum**

```php
<?php
// app/Enums/PlanTier.php
declare(strict_types=1);

namespace App\Enums;

enum PlanTier: string
{
    case Free     = 'free';
    case Pro      = 'pro';
    case Business = 'business';

    public static function fromString(string $value): self
    {
        return match ($value) {
            'pro'      => self::Pro,
            'business' => self::Business,
            default    => self::Free,
        };
    }
}
```

- [ ] **Step 4: Create PlanLimitService**

```php
<?php
// app/Services/PlanLimitService.php
declare(strict_types=1);

namespace App\Services;

use App\Enums\PlanTier;

final class PlanLimitService
{
    public static function linkLimit(PlanTier $tier): ?int
    {
        return match ($tier) {
            PlanTier::Free     => 10,
            PlanTier::Pro      => 500,
            PlanTier::Business => null,
        };
    }

    public static function retentionDays(PlanTier $tier): int
    {
        return match ($tier) {
            PlanTier::Free     => 7,
            PlanTier::Pro      => 90,
            PlanTier::Business => 365,
        };
    }

    public static function seatLimit(PlanTier $tier): int
    {
        return match ($tier) {
            PlanTier::Free     => 1,
            PlanTier::Pro      => 5,
            PlanTier::Business => 20,
        };
    }

    public static function canCreateLink(PlanTier $tier, int $currentCount): bool
    {
        $limit = self::linkLimit($tier);
        return $limit === null || $currentCount < $limit;
    }
}
```

- [ ] **Step 5: Run — verify PASS**

```bash
php artisan test tests/Unit/PlanLimitServiceTest.php
```
Expected: PASS — 12 tests, 12 assertions

- [ ] **Step 6: Commit**

```bash
git add app/Enums/PlanTier.php app/Services/PlanLimitService.php tests/Unit/PlanLimitServiceTest.php
git commit -m "feat(billing-saas): PlanTier enum + PlanLimitService (TDD)"
```

---

### Task 4: Core models + factories

**Files:**
- Create: `app/Models/Team.php`
- Create: `app/Models/Link.php`
- Create: `app/Models/Click.php`
- Create: `app/Models/UsageRecord.php`
- Create: `app/Models/ProcessedWebhookEvent.php`
- Create: `database/factories/TeamFactory.php`
- Create: `database/factories/LinkFactory.php`

- [ ] **Step 1: Create Team model**

```php
<?php
// app/Models/Team.php
declare(strict_types=1);

namespace App\Models;

use App\Enums\PlanTier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Team extends Model
{
    use HasFactory;

    protected $fillable = ['owner_id', 'name', 'slug', 'plan', 'subscription_ends_at', 'on_grace_period'];

    protected $casts = [
        'plan'                 => PlanTier::class,
        'subscription_ends_at' => 'immutable_datetime',
        'on_grace_period'      => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    public function activeLinks(): HasMany
    {
        return $this->hasMany(Link::class)->where('active', true);
    }

    public function usageRecords(): HasMany
    {
        return $this->hasMany(UsageRecord::class);
    }

    public function activeLinkCount(): int
    {
        return $this->activeLinks()->count();
    }
}
```

- [ ] **Step 2: Create Link model**

```php
<?php
// app/Models/Link.php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Link extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['team_id', 'slug', 'original_url', 'title', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
```

- [ ] **Step 3: Create Click model**

```php
<?php
// app/Models/Click.php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Click extends Model
{
    public $timestamps = false;

    protected $fillable = ['link_id', 'ip_hash', 'country', 'referrer', 'user_agent', 'clicked_at'];

    protected $casts = ['clicked_at' => 'immutable_datetime'];

    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }
}
```

- [ ] **Step 4: Create UsageRecord model**

```php
<?php
// app/Models/UsageRecord.php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class UsageRecord extends Model
{
    protected $fillable = ['team_id', 'metric', 'value', 'period'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public static function increment(int $teamId, string $metric, string $period): void
    {
        self::updateOrCreate(
            ['team_id' => $teamId, 'metric' => $metric, 'period' => $period],
            ['value' => 0]
        );
        self::where(['team_id' => $teamId, 'metric' => $metric, 'period' => $period])
            ->increment('value');
    }
}
```

- [ ] **Step 5: Create ProcessedWebhookEvent model**

```php
<?php
// app/Models/ProcessedWebhookEvent.php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class ProcessedWebhookEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['stripe_event_id', 'type', 'processed_at'];

    protected $casts = ['processed_at' => 'immutable_datetime'];
}
```

- [ ] **Step 6: Create TeamFactory**

```php
<?php
// database/factories/TeamFactory.php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

final class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        $name = $this->faker->company();
        return [
            'owner_id' => User::factory(),
            'name'     => $name,
            'slug'     => Str::slug($name) . '-' . Str::random(4),
            'plan'     => 'free',
        ];
    }

    public function pro(): static
    {
        return $this->state(['plan' => 'pro']);
    }

    public function business(): static
    {
        return $this->state(['plan' => 'business']);
    }
}
```

- [ ] **Step 7: Create LinkFactory**

```php
<?php
// database/factories/LinkFactory.php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Link;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

final class LinkFactory extends Factory
{
    protected $model = Link::class;

    public function definition(): array
    {
        return [
            'team_id'      => Team::factory(),
            'slug'         => Str::random(8),
            'original_url' => $this->faker->url(),
            'title'        => $this->faker->sentence(3),
            'active'       => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['active' => false]);
    }
}
```

- [ ] **Step 8: Smoke-test factory**

```bash
php artisan tinker --execute="echo App\Models\Team::factory()->create()->name;"
```
Expected: a company name printed, no exception.

- [ ] **Step 9: Commit**

```bash
git add app/Models/ database/factories/TeamFactory.php database/factories/LinkFactory.php
git commit -m "feat(billing-saas): Team, Link, Click, UsageRecord, ProcessedWebhookEvent models + factories"
```

---

### Task 5: CreateLinkService + PlanLimitExceededException (TDD)

**Files:**
- Create: `app/Exceptions/PlanLimitExceededException.php`
- Create: `app/Services/CreateLinkService.php`
- Create: `tests/Feature/CreateLinkTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/CreateLinkTest.php
declare(strict_types=1);

use App\Exceptions\PlanLimitExceededException;
use App\Models\Link;
use App\Models\Team;
use App\Services\CreateLinkService;

it('creates a link under plan limit', function (): void {
    $team = Team::factory()->create(['plan' => 'free']);

    $link = app(CreateLinkService::class)->create($team, 'https://example.com', 'Example');

    expect($link)->toBeInstanceOf(Link::class)
        ->and($link->original_url)->toBe('https://example.com')
        ->and($link->team_id)->toBe($team->id)
        ->and(strlen($link->slug))->toBe(8);
});

it('throws PlanLimitExceededException when free plan is full', function (): void {
    $team = Team::factory()->create(['plan' => 'free']);
    Link::factory()->count(10)->create(['team_id' => $team->id, 'active' => true]);

    expect(fn () => app(CreateLinkService::class)->create($team, 'https://example.com'))
        ->toThrow(PlanLimitExceededException::class);
});

it('generates unique slugs', function (): void {
    $team = Team::factory()->create(['plan' => 'pro']);

    $link1 = app(CreateLinkService::class)->create($team, 'https://a.com');
    $link2 = app(CreateLinkService::class)->create($team, 'https://b.com');

    expect($link1->slug)->not->toBe($link2->slug);
});

it('pro plan allows more than 10 links', function (): void {
    $team = Team::factory()->create(['plan' => 'pro']);
    Link::factory()->count(10)->create(['team_id' => $team->id, 'active' => true]);

    $link = app(CreateLinkService::class)->create($team, 'https://example.com');

    expect($link)->toBeInstanceOf(Link::class);
});
```

- [ ] **Step 2: Run — verify FAIL**

```bash
php artisan test tests/Feature/CreateLinkTest.php
```
Expected: FAIL — `App\Exceptions\PlanLimitExceededException not found`

- [ ] **Step 3: Create exception**

```php
<?php
// app/Exceptions/PlanLimitExceededException.php
declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class PlanLimitExceededException extends RuntimeException
{
    public function __construct(string $message = 'Link limit reached for your plan. Please upgrade.')
    {
        parent::__construct($message);
    }
}
```

- [ ] **Step 4: Create CreateLinkService**

```php
<?php
// app/Services/CreateLinkService.php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\PlanLimitExceededException;
use App\Models\Link;
use App\Models\Team;
use Illuminate\Support\Str;

final class CreateLinkService
{
    public function create(Team $team, string $url, ?string $title = null): Link
    {
        if (! PlanLimitService::canCreateLink($team->plan, $team->activeLinkCount())) {
            throw new PlanLimitExceededException();
        }

        return Link::create([
            'team_id'      => $team->id,
            'slug'         => $this->uniqueSlug(),
            'original_url' => $url,
            'title'        => $title,
            'active'       => true,
        ]);
    }

    private function uniqueSlug(): string
    {
        do {
            $slug = Str::random(8);
        } while (Link::where('slug', $slug)->exists());

        return $slug;
    }
}
```

- [ ] **Step 5: Run — verify PASS**

```bash
php artisan test tests/Feature/CreateLinkTest.php
```
Expected: PASS — 4 tests, 5 assertions

- [ ] **Step 6: Commit**

```bash
git add app/Exceptions/PlanLimitExceededException.php app/Services/CreateLinkService.php tests/Feature/CreateLinkTest.php
git commit -m "feat(billing-saas): CreateLinkService with plan limit enforcement (TDD)"
```

---

### Task 6: RecordClickJob + RedirectController (TDD)

**Files:**
- Create: `app/Jobs/RecordClickJob.php`
- Create: `app/Http/Controllers/RedirectController.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/RecordClickTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/RecordClickTest.php
declare(strict_types=1);

use App\Jobs\RecordClickJob;
use App\Models\Click;
use App\Models\Link;
use App\Models\Team;
use App\Models\UsageRecord;

it('redirects to original URL and dispatches RecordClickJob', function (): void {
    Queue::fake();
    $link = Link::factory()->create(['active' => true]);

    $this->get("/r/{$link->slug}")
        ->assertRedirect($link->original_url);

    Queue::assertPushed(RecordClickJob::class, fn ($job) => $job->linkId === $link->id);
});

it('returns 404 for inactive links', function (): void {
    $link = Link::factory()->inactive()->create();

    $this->get("/r/{$link->slug}")->assertNotFound();
});

it('stores click when job handles', function (): void {
    $link = Link::factory()->create();

    (new RecordClickJob(
        linkId: $link->id,
        ipHash: hash('sha256', '1.2.3.4'),
        country: 'US',
        referrer: 'https://google.com',
        userAgent: 'Mozilla/5.0',
    ))->handle();

    expect(Click::where('link_id', $link->id)->count())->toBe(1);
});

it('increments usage record when job handles', function (): void {
    $team = Team::factory()->create();
    $link = Link::factory()->create(['team_id' => $team->id]);
    $period = now()->format('Y-m');

    (new RecordClickJob(
        linkId: $link->id,
        ipHash: null,
        country: null,
        referrer: null,
        userAgent: null,
    ))->handle();

    $record = UsageRecord::where(['team_id' => $team->id, 'metric' => 'clicks', 'period' => $period])->first();
    expect($record->value)->toBe(1);
});
```

- [ ] **Step 2: Run — verify FAIL**

```bash
php artisan test tests/Feature/RecordClickTest.php
```
Expected: FAIL — route `/r/{slug}` not found

- [ ] **Step 3: Create RecordClickJob**

```php
<?php
// app/Jobs/RecordClickJob.php
declare(strict_types=1);

namespace App\Jobs;

use App\Models\Click;
use App\Models\Link;
use App\Models\UsageRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class RecordClickJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly int $linkId,
        public readonly ?string $ipHash,
        public readonly ?string $country,
        public readonly ?string $referrer,
        public readonly ?string $userAgent,
    ) {}

    public function handle(): void
    {
        $link = Link::find($this->linkId);
        if (! $link) {
            return;
        }

        Click::create([
            'link_id'    => $this->linkId,
            'ip_hash'    => $this->ipHash,
            'country'    => $this->country,
            'referrer'   => $this->referrer,
            'user_agent' => $this->userAgent,
            'clicked_at' => now(),
        ]);

        UsageRecord::increment($link->team_id, 'clicks', now()->format('Y-m'));
    }

    public function failed(\Throwable $e): void
    {
        logger()->error('RecordClickJob failed', ['link_id' => $this->linkId, 'error' => $e->getMessage()]);
    }
}
```

- [ ] **Step 4: Create RedirectController**

```php
<?php
// app/Http/Controllers/RedirectController.php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\RecordClickJob;
use App\Models\Link;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class RedirectController extends Controller
{
    public function __invoke(Request $request, string $slug): RedirectResponse
    {
        $link = Link::where('slug', $slug)->where('active', true)->firstOrFail();

        RecordClickJob::dispatch(
            linkId: $link->id,
            ipHash: $request->ip() ? hash('sha256', $request->ip()) : null,
            country: null,
            referrer: $request->header('Referer'),
            userAgent: $request->userAgent(),
        );

        return redirect()->away($link->original_url);
    }
}
```

- [ ] **Step 5: Add redirect route to routes/web.php**

```php
// routes/web.php — add before middleware group:
Route::get('/r/{slug}', App\Http\Controllers\RedirectController::class)->name('redirect');
```

- [ ] **Step 6: Run — verify PASS**

```bash
php artisan test tests/Feature/RecordClickTest.php
```
Expected: PASS — 4 tests, 4 assertions

- [ ] **Step 7: Commit**

```bash
git add app/Jobs/RecordClickJob.php app/Http/Controllers/RedirectController.php routes/web.php tests/Feature/RecordClickTest.php
git commit -m "feat(billing-saas): RecordClickJob, RedirectController, /r/{slug} route (TDD)"
```

---

### Task 7: Middleware + LinkController

**Files:**
- Create: `app/Http/Middleware/ScopedToTeam.php`
- Create: `app/Http/Middleware/EnsureTeamSubscribed.php`
- Create: `app/Http/Controllers/LinkController.php`
- Create: `app/Http/Requests/StoreLinkRequest.php`
- Modify: `bootstrap/app.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create ScopedToTeam middleware**

```php
<?php
// app/Http/Middleware/ScopedToTeam.php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Team;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ScopedToTeam
{
    public function handle(Request $request, Closure $next): Response
    {
        $teamId = session('current_team_id');

        if (! $teamId && $request->user()) {
            $team = $request->user()->teams()->first();
            if ($team) {
                session(['current_team_id' => $team->id]);
                $teamId = $team->id;
            }
        }

        if ($teamId) {
            $team = Team::find($teamId);
            if ($team) {
                $request->attributes->set('current_team', $team);
                app()->instance('current_team', $team);
            }
        }

        return $next($request);
    }
}
```

- [ ] **Step 2: Create EnsureTeamSubscribed middleware**

```php
<?php
// app/Http/Middleware/EnsureTeamSubscribed.php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTeamSubscribed
{
    public function handle(Request $request, Closure $next): Response
    {
        $team = $request->attributes->get('current_team');

        if ($team && $team->on_grace_period) {
            if ($request->isMethodSafe() === false) {
                return redirect()->route('billing.index')
                    ->with('warning', 'Your subscription has lapsed. Please renew to continue.');
            }
        }

        return $next($request);
    }
}
```

- [ ] **Step 3: Register middleware aliases in bootstrap/app.php**

```php
// bootstrap/app.php — inside ->withMiddleware(function (Middleware $middleware) { ... }):
$middleware->alias([
    'team.scope'      => \App\Http\Middleware\ScopedToTeam::class,
    'team.subscribed' => \App\Http\Middleware\EnsureTeamSubscribed::class,
]);
```

- [ ] **Step 4: Create StoreLinkRequest**

```php
<?php
// app/Http/Requests/StoreLinkRequest.php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'original_url' => ['required', 'url', 'max:2048'],
            'title'        => ['nullable', 'string', 'max:255'],
        ];
    }
}
```

- [ ] **Step 5: Create LinkController**

```php
<?php
// app/Http/Controllers/LinkController.php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\PlanLimitExceededException;
use App\Http\Requests\StoreLinkRequest;
use App\Models\Link;
use App\Services\CreateLinkService;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class LinkController extends Controller
{
    public function __construct(private readonly CreateLinkService $createLink) {}

    public function index(Request $request): View
    {
        $team  = $request->attributes->get('current_team');
        $links = $team->links()->orderByDesc('created_at')->paginate(20);

        return view('links.index', compact('links', 'team'));
    }

    public function store(StoreLinkRequest $request): RedirectResponse
    {
        $team = $request->attributes->get('current_team');

        try {
            $this->createLink->create(
                team: $team,
                url: $request->validated('original_url'),
                title: $request->validated('title'),
            );
        } catch (PlanLimitExceededException $e) {
            return back()->withErrors(['limit' => $e->getMessage()]);
        }

        return redirect()->route('links.index')->with('success', 'Link created.');
    }

    public function destroy(Request $request, Link $link): RedirectResponse
    {
        $team = $request->attributes->get('current_team');
        abort_if($link->team_id !== $team->id, 403);
        $link->delete();

        return redirect()->route('links.index')->with('success', 'Link deleted.');
    }

    public function stats(Request $request, Link $link): View
    {
        $team          = $request->attributes->get('current_team');
        abort_if($link->team_id !== $team->id, 403);

        $retentionDays = PlanLimitService::retentionDays($team->plan);
        $clicks        = $link->clicks()
            ->where('clicked_at', '>=', now()->subDays($retentionDays))
            ->orderByDesc('clicked_at')
            ->paginate(50);

        return view('links.stats', compact('link', 'clicks', 'retentionDays'));
    }
}
```

- [ ] **Step 6: Complete routes/web.php**

```php
<?php
// routes/web.php (full file)
use App\Http\Controllers\LinkController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\Billing\CheckoutController;
use App\Http\Controllers\Billing\PortalController;
use App\Http\Controllers\Billing\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/r/{slug}', RedirectController::class)->name('redirect');
Route::post('/billing/webhook', [WebhookController::class, 'handle'])->name('billing.webhook');

Route::middleware(['auth', 'verified', 'team.scope', 'team.subscribed'])->group(function (): void {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    Route::resource('links', LinkController::class)->only(['index', 'store', 'destroy']);
    Route::get('/links/{link}/stats', [LinkController::class, 'stats'])->name('links.stats');

    Route::prefix('billing')->name('billing.')->group(function (): void {
        Route::get('/', fn () => view('billing.index'))->name('index');
        Route::post('/subscribe', [CheckoutController::class, 'store'])->name('subscribe');
        Route::post('/portal', [PortalController::class, 'store'])->name('portal');
    });
});

require __DIR__.'/auth.php';
```

- [ ] **Step 7: Verify routes exist**

```bash
php artisan route:list --path=links
php artisan route:list --path=billing
```
Expected: links.index, links.store, links.destroy, links.stats, billing.subscribe, billing.portal, billing.webhook all listed.

- [ ] **Step 8: Commit**

```bash
git add app/Http/Middleware/ app/Http/Controllers/LinkController.php app/Http/Requests/StoreLinkRequest.php routes/web.php bootstrap/app.php
git commit -m "feat(billing-saas): ScopedToTeam + EnsureTeamSubscribed middleware, LinkController, full routes"
```

---

### Task 8: Billing controllers + WebhookController

**Files:**
- Create: `app/Services/BillingService.php`
- Create: `app/Http/Controllers/Billing/CheckoutController.php`
- Create: `app/Http/Controllers/Billing/PortalController.php`
- Create: `app/Http/Controllers/Billing/WebhookController.php`
- Create: `app/Notifications/PaymentFailedNotification.php`
- Create: `tests/fixtures/stripe/subscription.deleted.json`
- Create: `tests/fixtures/stripe/invoice.payment_failed.json`
- Create: `tests/Feature/WebhookTest.php`

- [ ] **Step 1: Create BillingService**

```php
<?php
// app/Services/BillingService.php
declare(strict_types=1);

namespace App\Services;

use App\Models\Team;
use App\Models\User;
use Laravel\Cashier\Checkout;

final class BillingService
{
    public function createCheckoutSession(User $user, Team $team, string $priceId): Checkout
    {
        return $user->newSubscription('default', $priceId)
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('billing.index') . '?success=1',
                'cancel_url'  => route('billing.index'),
                'metadata'    => ['team_id' => $team->id],
            ]);
    }

    public function createPortalSession(User $user): string
    {
        return $user->billingPortalUrl(route('billing.index'));
    }
}
```

- [ ] **Step 2: Create CheckoutController**

```php
<?php
// app/Http/Controllers/Billing/CheckoutController.php
declare(strict_types=1);

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class CheckoutController extends Controller
{
    public function __construct(private readonly BillingService $billing) {}

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['price_id' => ['required', 'string', 'starts_with:price_']]);

        $team    = $request->attributes->get('current_team');
        $session = $this->billing->createCheckoutSession($request->user(), $team, $request->price_id);

        return redirect($session->url);
    }
}
```

- [ ] **Step 3: Create PortalController**

```php
<?php
// app/Http/Controllers/Billing/PortalController.php
declare(strict_types=1);

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PortalController extends Controller
{
    public function __construct(private readonly BillingService $billing) {}

    public function store(Request $request): RedirectResponse
    {
        return redirect($this->billing->createPortalSession($request->user()));
    }
}
```

- [ ] **Step 4: Create PaymentFailedNotification**

```php
<?php
// app/Notifications/PaymentFailedNotification.php
declare(strict_types=1);

namespace App\Notifications;

use App\Models\Team;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class PaymentFailedNotification extends Notification
{
    public function __construct(private readonly Team $team) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment failed — ' . $this->team->name)
            ->line('Your payment failed. You have a 3-day grace period to update billing.')
            ->action('Update billing', route('billing.index'));
    }

    public function toArray(object $notifiable): array
    {
        return ['team_id' => $this->team->id, 'message' => 'Payment failed for ' . $this->team->name];
    }
}
```

- [ ] **Step 5: Create Stripe test fixtures**

```json
// tests/fixtures/stripe/subscription.deleted.json
{
  "id": "evt_test_sub_deleted",
  "type": "customer.subscription.deleted",
  "data": {
    "object": {
      "id": "sub_test123",
      "customer": "cus_test123",
      "status": "canceled",
      "metadata": { "team_id": "1" }
    }
  }
}
```

```json
// tests/fixtures/stripe/invoice.payment_failed.json
{
  "id": "evt_test_payment_failed",
  "type": "invoice.payment_failed",
  "data": {
    "object": {
      "customer": "cus_test123",
      "subscription": "sub_test123",
      "metadata": { "team_id": "1" }
    }
  }
}
```

- [ ] **Step 6: Write WebhookTest**

```php
<?php
// tests/Feature/WebhookTest.php
declare(strict_types=1);

use App\Models\ProcessedWebhookEvent;
use App\Models\Team;
use App\Models\User;

it('subscription.deleted downgrades team to free', function (): void {
    $team    = Team::factory()->create(['plan' => 'pro']);
    $payload = json_decode(file_get_contents(base_path('tests/fixtures/stripe/subscription.deleted.json')), true);
    $payload['data']['object']['metadata']['team_id'] = (string) $team->id;

    $this->withoutMiddleware()
        ->postJson('/billing/webhook', $payload)
        ->assertOk();

    expect($team->fresh()->plan->value)->toBe('free');
});

it('invoice.payment_failed sets grace period', function (): void {
    $user    = User::factory()->create();
    $team    = Team::factory()->create(['plan' => 'pro', 'owner_id' => $user->id]);
    $payload = json_decode(file_get_contents(base_path('tests/fixtures/stripe/invoice.payment_failed.json')), true);
    $payload['data']['object']['metadata']['team_id'] = (string) $team->id;

    $this->withoutMiddleware()
        ->postJson('/billing/webhook', $payload)
        ->assertOk();

    expect($team->fresh()->on_grace_period)->toBeTrue();
});

it('same event processed only once (idempotent)', function (): void {
    $team    = Team::factory()->create(['plan' => 'pro']);
    $payload = json_decode(file_get_contents(base_path('tests/fixtures/stripe/subscription.deleted.json')), true);
    $payload['id']  = 'evt_idempotent_test';
    $payload['data']['object']['metadata']['team_id'] = (string) $team->id;

    $this->withoutMiddleware()->postJson('/billing/webhook', $payload)->assertOk();
    $this->withoutMiddleware()->postJson('/billing/webhook', $payload)->assertOk();

    expect(ProcessedWebhookEvent::where('stripe_event_id', 'evt_idempotent_test')->count())->toBe(1);
});

it('returns 400 when event id is missing', function (): void {
    $this->withoutMiddleware()
        ->postJson('/billing/webhook', ['type' => 'some.event'])
        ->assertStatus(400);
});
```

- [ ] **Step 7: Create WebhookController**

```php
<?php
// app/Http/Controllers/Billing/WebhookController.php
declare(strict_types=1);

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\ProcessedWebhookEvent;
use App\Models\Team;
use App\Notifications\PaymentFailedNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class WebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $payload = $request->all();
        $eventId = $payload['id'] ?? null;
        $type    = $payload['type'] ?? null;

        if (! $eventId || ! $type) {
            return response('Missing event data', 400);
        }

        if (ProcessedWebhookEvent::where('stripe_event_id', $eventId)->exists()) {
            return response('Already processed', 200);
        }

        match ($type) {
            'customer.subscription.deleted' => $this->onSubscriptionDeleted($payload),
            'customer.subscription.updated' => $this->onSubscriptionUpdated($payload),
            'invoice.payment_failed'        => $this->onPaymentFailed($payload),
            'invoice.payment_succeeded'     => $this->onPaymentSucceeded($payload),
            default                         => null,
        };

        ProcessedWebhookEvent::create([
            'stripe_event_id' => $eventId,
            'type'            => $type,
            'processed_at'    => now(),
        ]);

        return response('OK', 200);
    }

    private function onSubscriptionDeleted(array $payload): void
    {
        $teamId = $payload['data']['object']['metadata']['team_id'] ?? null;
        if (! $teamId) return;

        Team::where('id', $teamId)->update(['plan' => 'free', 'on_grace_period' => false]);
    }

    private function onSubscriptionUpdated(array $payload): void
    {
        $teamId = $payload['data']['object']['metadata']['team_id'] ?? null;
        $status = $payload['data']['object']['status'] ?? null;
        if (! $teamId) return;

        if ($status === 'active') {
            Team::where('id', $teamId)->update(['on_grace_period' => false]);
        }
    }

    private function onPaymentFailed(array $payload): void
    {
        $teamId = $payload['data']['object']['metadata']['team_id'] ?? null;
        if (! $teamId) return;

        Team::where('id', $teamId)->update(['on_grace_period' => true]);

        $team = Team::with('owner')->find($teamId);
        $team?->owner?->notify(new PaymentFailedNotification($team));
    }

    private function onPaymentSucceeded(array $payload): void
    {
        $teamId = $payload['data']['object']['metadata']['team_id'] ?? null;
        if (! $teamId) return;

        Team::where('id', $teamId)->update(['on_grace_period' => false]);
    }
}
```

- [ ] **Step 8: Run webhook tests**

```bash
php artisan test tests/Feature/WebhookTest.php
```
Expected: PASS — 4 tests, 4 assertions

- [ ] **Step 9: Commit**

```bash
git add app/Services/BillingService.php app/Http/Controllers/Billing/ app/Notifications/ tests/Feature/WebhookTest.php tests/fixtures/
git commit -m "feat(billing-saas): billing controllers, idempotent webhook handler, PaymentFailedNotification"
```

---

### Task 9: Scheduled jobs

**Files:**
- Create: `app/Jobs/PurgeExpiredClicksJob.php`
- Create: `app/Jobs/SyncUsageToStripeJob.php`
- Modify: `routes/console.php`

- [ ] **Step 1: Create PurgeExpiredClicksJob**

```php
<?php
// app/Jobs/PurgeExpiredClicksJob.php
declare(strict_types=1);

namespace App\Jobs;

use App\Models\Click;
use App\Models\Team;
use App\Services\PlanLimitService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class PurgeExpiredClicksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Team::chunk(100, function ($teams): void {
            foreach ($teams as $team) {
                $cutoff = now()->subDays(PlanLimitService::retentionDays($team->plan));

                Click::whereHas('link', fn ($q) => $q->where('team_id', $team->id))
                    ->where('clicked_at', '<', $cutoff)
                    ->delete();
            }
        });
    }
}
```

- [ ] **Step 2: Create SyncUsageToStripeJob**

```php
<?php
// app/Jobs/SyncUsageToStripeJob.php
declare(strict_types=1);

namespace App\Jobs;

use App\Models\UsageRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class SyncUsageToStripeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $period = now()->format('Y-m');

        UsageRecord::where('metric', 'clicks')
            ->where('period', $period)
            ->with('team.owner')
            ->chunk(50, function ($records): void {
                foreach ($records as $record) {
                    $user = $record->team?->owner;
                    if (! $user || ! $user->subscribed('default')) {
                        continue;
                    }
                    if (config('cashier.meter_id')) {
                        $user->reportMeterEvent('clicks', ['value' => $record->value]);
                    }
                }
            });
    }
}
```

- [ ] **Step 3: Register scheduled jobs**

```php
<?php
// routes/console.php
use App\Jobs\PurgeExpiredClicksJob;
use App\Jobs\SyncUsageToStripeJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new PurgeExpiredClicksJob)->daily();
Schedule::job(new SyncUsageToStripeJob)->hourly();
```

- [ ] **Step 4: Verify schedule**

```bash
php artisan schedule:list
```
Expected: `PurgeExpiredClicksJob` (Daily) and `SyncUsageToStripeJob` (Hourly) both listed.

- [ ] **Step 5: Commit**

```bash
git add app/Jobs/PurgeExpiredClicksJob.php app/Jobs/SyncUsageToStripeJob.php routes/console.php
git commit -m "feat(billing-saas): scheduled PurgeExpiredClicksJob + SyncUsageToStripeJob"
```

---

### Task 10: Blade views

**Files:**
- Create: `resources/views/dashboard.blade.php`
- Create: `resources/views/links/index.blade.php`
- Create: `resources/views/links/stats.blade.php`
- Create: `resources/views/billing/index.blade.php`

- [ ] **Step 1: Dashboard view**

```blade
{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if(app()->has('current_team'))
                    <p class="text-gray-600">Team: <strong>{{ app('current_team')->name }}</strong></p>
                    <p class="text-gray-600">Plan: <strong>{{ app('current_team')->plan->value }}</strong></p>
                    <div class="mt-4 flex gap-4">
                        <a href="{{ route('links.index') }}" class="text-indigo-600">Manage Links →</a>
                        <a href="{{ route('billing.index') }}" class="text-indigo-600">Billing →</a>
                    </div>
                @else
                    <p class="text-gray-600">No team found.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
```

- [ ] **Step 2: links/index view**

```blade
{{-- resources/views/links/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Links</h2>
    </x-slot>
    <div class="py-12 max-w-5xl mx-auto sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
        @endif
        @error('limit')
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">{{ $message }}
                <a href="{{ route('billing.index') }}" class="underline">Upgrade →</a>
            </div>
        @enderror
        <form method="POST" action="{{ route('links.store') }}" class="mb-8 bg-white shadow-sm rounded-lg p-6">
            @csrf
            <div class="flex gap-4 flex-wrap">
                <input type="url" name="original_url" placeholder="https://example.com" required
                    class="flex-1 min-w-0 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    value="{{ old('original_url') }}">
                <input type="text" name="title" placeholder="Label (optional)"
                    class="w-48 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Shorten
                </button>
            </div>
        </form>
        <div class="bg-white shadow-sm rounded-lg divide-y">
            @forelse($links as $link)
                <div class="p-4 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <p class="font-medium truncate">{{ $link->title ?? $link->original_url }}</p>
                        <a href="{{ url('/r/' . $link->slug) }}" class="text-indigo-600 text-sm" target="_blank">
                            /r/{{ $link->slug }}
                        </a>
                    </div>
                    <div class="flex gap-4 shrink-0">
                        <a href="{{ route('links.stats', $link) }}" class="text-gray-500 text-sm hover:text-indigo-600">Stats</a>
                        <form method="POST" action="{{ route('links.destroy', $link) }}">
                            @csrf @method('DELETE')
                            <button class="text-red-500 text-sm hover:text-red-700">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-400">No links yet. Create your first one above.</div>
            @endforelse
        </div>
        <div class="mt-4">{{ $links->links() }}</div>
    </div>
</x-app-layout>
```

- [ ] **Step 3: links/stats view**

```blade
{{-- resources/views/links/stats.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Stats — /r/{{ $link->slug }}</h2>
    </x-slot>
    <div class="py-12 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <p class="text-gray-700"><strong>Original URL:</strong>
                <a href="{{ $link->original_url }}" class="text-indigo-600 break-all" target="_blank">{{ $link->original_url }}</a>
            </p>
            <p class="text-gray-500 text-sm mt-1">Showing last {{ $retentionDays }} days (your plan's retention window)</p>
            <p class="text-gray-700 mt-2"><strong>Clicks in window:</strong> {{ $clicks->total() }}</p>
        </div>
        <div class="bg-white shadow-sm rounded-lg divide-y">
            @forelse($clicks as $click)
                <div class="p-3 text-sm text-gray-600 flex justify-between gap-4">
                    <span>{{ $click->country ?? '??' }} — {{ $click->referrer ?? 'Direct' }}</span>
                    <span class="shrink-0 text-gray-400">{{ $click->clicked_at->diffForHumans() }}</span>
                </div>
            @empty
                <div class="p-8 text-center text-gray-400">No clicks in this retention window.</div>
            @endforelse
        </div>
        <div class="mt-4">{{ $clicks->links() }}</div>
        <div class="mt-6"><a href="{{ route('links.index') }}" class="text-indigo-600">← Back to links</a></div>
    </div>
</x-app-layout>
```

- [ ] **Step 4: billing/index view**

```blade
{{-- resources/views/billing/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Billing</h2>
    </x-slot>
    <div class="py-12 max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if(session('warning'))
            <div class="mb-6 p-4 bg-yellow-100 text-yellow-800 rounded">{{ session('warning') }}</div>
        @endif
        @if(request('success'))
            <div class="mb-6 p-4 bg-green-100 text-green-700 rounded">Subscription activated!</div>
        @endif
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            @foreach([
                ['name'=>'Free','price'=>'$0','links'=>'10','retention'=>'7 days','seats'=>'1','price_id'=>null],
                ['name'=>'Pro','price'=>'$19/mo','links'=>'500','retention'=>'90 days','seats'=>'5','price_id'=>config('services.stripe.pro_price_id')],
                ['name'=>'Business','price'=>'$79/mo','links'=>'Unlimited','retention'=>'365 days','seats'=>'20','price_id'=>config('services.stripe.business_price_id')],
            ] as $plan)
                <div class="bg-white shadow-sm rounded-lg p-6 flex flex-col">
                    <h3 class="text-lg font-semibold">{{ $plan['name'] }}</h3>
                    <p class="text-2xl font-bold mt-2">{{ $plan['price'] }}</p>
                    <ul class="mt-4 text-sm text-gray-600 space-y-1 flex-1">
                        <li>{{ $plan['links'] }} links</li>
                        <li>{{ $plan['retention'] }} analytics</li>
                        <li>{{ $plan['seats'] }} seat(s)</li>
                    </ul>
                    @if($plan['price_id'])
                        <form method="POST" action="{{ route('billing.subscribe') }}" class="mt-4">
                            @csrf
                            <input type="hidden" name="price_id" value="{{ $plan['price_id'] }}">
                            <button class="w-full py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Subscribe
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
        @if(auth()->user()->subscribed('default'))
            <form method="POST" action="{{ route('billing.portal') }}" class="mt-8">
                @csrf
                <button class="text-indigo-600 underline text-sm">Manage subscription in Stripe →</button>
            </form>
        @endif
    </div>
</x-app-layout>
```

- [ ] **Step 5: Smoke-test in browser**

```bash
php artisan serve
```
Visit `http://localhost:8000`, register, check dashboard loads without errors.

- [ ] **Step 6: Commit**

```bash
git add resources/views/
git commit -m "feat(billing-saas): dashboard, links, stats, billing Blade views"
```

---

### Task 11: Plan limit feature tests + team auto-creation

**Files:**
- Create: `tests/Feature/PlanLimitTest.php`
- Modify: `app/Http/Controllers/Auth/RegisteredUserController.php`
- Modify: `tests/Feature/BillingTest.php`

- [ ] **Step 1: Create PlanLimitTest**

```php
<?php
// tests/Feature/PlanLimitTest.php
declare(strict_types=1);

use App\Exceptions\PlanLimitExceededException;
use App\Models\Link;
use App\Models\Team;
use App\Services\CreateLinkService;

it('free plan: at exactly 10 links throws', function (): void {
    $team = Team::factory()->create(['plan' => 'free']);
    Link::factory()->count(10)->create(['team_id' => $team->id, 'active' => true]);

    expect(fn () => app(CreateLinkService::class)->create($team, 'https://x.com'))
        ->toThrow(PlanLimitExceededException::class);
});

it('free plan: at 9 links succeeds', function (): void {
    $team = Team::factory()->create(['plan' => 'free']);
    Link::factory()->count(9)->create(['team_id' => $team->id, 'active' => true]);

    expect(app(CreateLinkService::class)->create($team, 'https://x.com'))->toBeInstanceOf(Link::class);
});

it('pro plan: at exactly 500 links throws', function (): void {
    $team = Team::factory()->create(['plan' => 'pro']);
    Link::factory()->count(500)->create(['team_id' => $team->id, 'active' => true]);

    expect(fn () => app(CreateLinkService::class)->create($team, 'https://x.com'))
        ->toThrow(PlanLimitExceededException::class);
});

it('business plan: no limit regardless of count', function (): void {
    $team = Team::factory()->create(['plan' => 'business']);
    Link::factory()->count(600)->create(['team_id' => $team->id, 'active' => true]);

    expect(app(CreateLinkService::class)->create($team, 'https://x.com'))->toBeInstanceOf(Link::class);
});

it('soft-deleted links do not count toward limit', function (): void {
    $team = Team::factory()->create(['plan' => 'free']);
    Link::factory()->count(10)->create(['team_id' => $team->id, 'active' => true]);
    $team->links()->delete();

    expect(app(CreateLinkService::class)->create($team, 'https://x.com'))->toBeInstanceOf(Link::class);
});
```

- [ ] **Step 2: Run — verify PASS**

```bash
php artisan test tests/Feature/PlanLimitTest.php
```
Expected: PASS — 5 tests

- [ ] **Step 3: Auto-create team on registration**

In `app/Http/Controllers/Auth/RegisteredUserController.php`, after `$user = User::create(...)`:

```php
// Add after User::create():
$team = \App\Models\Team::create([
    'owner_id' => $user->id,
    'name'     => $user->name . "'s Team",
    'slug'     => \Illuminate\Support\Str::slug($user->name) . '-' . \Illuminate\Support\Str::random(4),
    'plan'     => 'free',
]);
$user->teams()->attach($team->id, ['role' => 'owner']);
session(['current_team_id' => $team->id]);
```

- [ ] **Step 4: Create BillingTest**

```php
<?php
// tests/Feature/BillingTest.php
declare(strict_types=1);

use App\Models\Team;
use App\Models\User;

it('registration auto-creates a free team', function (): void {
    $this->post('/register', [
        'name'                  => 'Test User',
        'email'                 => 'test@example.com',
        'password'              => 'Password123!',
        'password_confirmation' => 'Password123!',
    ])->assertRedirect('/dashboard');

    $user = User::where('email', 'test@example.com')->first();
    expect($user->teams()->count())->toBe(1)
        ->and($user->teams()->first()->plan->value)->toBe('free');
});

it('authenticated user can view billing page', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $user->teams()->attach($team->id, ['role' => 'owner']);
    session(['current_team_id' => $team->id]);

    $this->actingAs($user)->get('/billing')->assertOk()->assertViewIs('billing.index');
});

it('guest is redirected from billing page', function (): void {
    $this->get('/billing')->assertRedirect('/login');
});

it('subscribe validates price_id starts with price_', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $user->teams()->attach($team->id, ['role' => 'owner']);
    session(['current_team_id' => $team->id]);

    $this->actingAs($user)
        ->postJson('/billing/subscribe', ['price_id' => 'invalid_id'])
        ->assertUnprocessable();
});
```

- [ ] **Step 5: Run all new tests**

```bash
php artisan test tests/Feature/BillingTest.php tests/Feature/PlanLimitTest.php
```
Expected: PASS — all tests pass

- [ ] **Step 6: Commit**

```bash
git add tests/Feature/PlanLimitTest.php tests/Feature/BillingTest.php app/Http/Controllers/Auth/RegisteredUserController.php
git commit -m "feat(billing-saas): auto-create team on registration, PlanLimitTest + BillingTest"
```

---

### Task 12: Final validation

**Files:**
- Modify: `D:\laravel13.x\ROADMAP.md`

- [ ] **Step 1: Run full test suite**

```bash
php artisan test --stop-on-failure
```
Expected: All tests pass, 0 failures.

- [ ] **Step 2: Check route list**

```bash
php artisan route:list
```
Expected: redirect, links.index, links.store, links.destroy, links.stats, billing.index, billing.subscribe, billing.portal, billing.webhook all listed.

- [ ] **Step 3: Check migration status**

```bash
php artisan migrate:status
```
Expected: All `Ran`.

- [ ] **Step 4: Run Pint**

```bash
./vendor/bin/pint --test
```
Expected: No violations.

- [ ] **Step 5: Check schedule**

```bash
php artisan schedule:list
```
Expected: PurgeExpiredClicksJob (Daily), SyncUsageToStripeJob (Hourly).

- [ ] **Step 6: Update ROADMAP.md**

Add to Examples section in `D:\laravel13.x\ROADMAP.md`:

```markdown
#### `billing-saas`

**Status:** ✅ Complete

**Stack:** Laravel 13 + Breeze (Blade) + Laravel Cashier Stripe

**Features:**
- Link Tracker SaaS with Stripe subscription billing
- Plans: Free / Pro / Business (link limits, analytics retention, seat count)
- Stripe Checkout + Customer Portal
- Idempotent webhook handlers (processed_webhook_events table)
- Queued click recording (RecordClickJob, 3× retry)
- Scheduled click purge per plan retention window
- Hourly usage sync to Stripe Meters API
- Plan limit enforcement via PlanLimitService
- Auto-create free Team on registration
- Pest TDD (>85% coverage)
```

- [ ] **Step 7: Final commit**

```bash
cd D:\laravel13.x
git add ROADMAP.md examples/billing-saas
git commit -m "feat: complete billing-saas — Link Tracker SaaS with Stripe billing, plan limits, webhooks"
```
