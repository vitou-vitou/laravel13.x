# 06 — Testing Strategy

> Tests aren't quality assurance. Tests are leverage. They let you ship Friday, refactor Monday, and sleep Tuesday.

---

## The Senior's Testing Pyramid

```
       /\
      /  \      Browser Tests (Dusk) - 5%
     /----\
    /      \    Feature Tests (HTTP) - 70%
   /--------\
  /          \  Unit Tests - 25%
 /------------\
```

**Heresy:** ignore the classical pyramid. Laravel's strength is Feature tests. Use them. 70%/25%/5% is the senior's split.

---

## Pest Over PHPUnit (2026)

PHPUnit is the engine. Pest is the seat. Same power, better ergonomics.

```bash
composer require pestphp/pest --dev --with-all-dependencies
php artisan pest:install
```

---

## Feature Tests (Your Workhorse)

```php
use App\Models\User;

it('allows authenticated users to create a post', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/posts', [
        'title' => 'Hello World',
        'body'  => 'This is my first post.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('posts', [
        'title' => 'Hello World',
        'user_id' => $user->id,
    ]);
});

it('forbids guests from creating posts', function () {
    $response = $this->post('/posts', [
        'title' => 'Sneaky',
        'body'  => 'Sneaky',
    ]);

    $response->assertRedirect('/login');
    $this->assertDatabaseCount('posts', 0);
});
```

**Why feature tests dominate:**
- Hit real HTTP layer (middleware, validation, controller, action)
- Use real database (in-memory SQLite)
- Catch routing, auth, validation, controller, model bugs at once
- Easy to read like a spec

---

## Unit Tests (For Pure Logic)

Use ONLY for:
- Value objects
- Calculators
- Pure functions
- Complex enum behavior
- Pipeline steps

```php
// tests/Unit/MoneyTest.php
it('adds money correctly', function () {
    $a = new Money(1000);
    $b = new Money(500);

    expect($a->add($b)->amount)->toBe(1500);
});

it('throws on currency mismatch', function () {
    $usd = new Money(100, 'USD');
    $eur = new Money(100, 'EUR');

    expect(fn() => $usd->add($eur))->toThrow(InvalidArgumentException::class);
});
```

Unit tests for Eloquent models? **No.** Use Feature tests. Faster to write, more realistic.

---

## Factories (The Senior Way)

```php
// database/factories/PostFactory.php
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title'   => fake()->sentence(),
            'slug'    => fake()->slug(),
            'body'    => fake()->paragraphs(3, true),
            'status'  => 'draft',
        ];
    }

    public function published(): self
    {
        return $this->state([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function forUser(User $user): self
    {
        return $this->state(['user_id' => $user->id]);
    }
}
```

Use:
```php
Post::factory()->published()->forUser($user)->create();
Post::factory()->count(50)->published()->create();
```

Factories give you DSL-like data setup. Lean on states heavily.

---

## Test Database Speed

In-memory SQLite for tests:

```xml
<!-- phpunit.xml -->
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

But: SQLite doesn't support some Postgres features (full-text search, jsonb operators). Solution:

- Default: in-memory SQLite (fast)
- CI: real Postgres (parity)

```php
// In tests using Postgres-only features
$this->markTestSkippedUnlessPostgres();
```

---

## Parallel Testing

```bash
php artisan test --parallel
php artisan test --parallel --processes=8
```

Test suite goes from 60s → 10s. Set up properly:

```php
// In TestCase if needed
use ParallelTesting;

protected function setUp(): void
{
    parent::setUp();
    ParallelTesting::setUpTestDatabase(function ($db, $token) {
        // Run migrations
    });
}
```

---

## The `RefreshDatabase` Trait

```php
uses(RefreshDatabase::class);
```

Migrations run once. Each test wraps in a transaction. Fast and clean.

Alternative `DatabaseTransactions`:
- Doesn't re-migrate (faster)
- Use when migrations are stable

---

## HTTP Testing Patterns

### Basic Assertions

```php
$response = $this->get('/dashboard');

$response->assertOk();                       // 200
$response->assertStatus(201);
$response->assertRedirect('/login');
$response->assertSee('Dashboard');
$response->assertDontSee('Admin Panel');
$response->assertJsonStructure(['id', 'name']);
$response->assertJsonFragment(['name' => 'John']);
$response->assertJsonCount(5, 'data');
$response->assertJsonPath('data.0.email', 'foo@bar.com');
```

### Form Testing

```php
$response = $this->post('/posts', [
    'title' => 'Test',
    'body'  => 'Body',
]);

$response->assertSessionHasNoErrors();
$response->assertSessionHas('flash.success');
```

### Authentication

```php
$user = User::factory()->create();

$this->actingAs($user)
     ->get('/profile')
     ->assertOk();

// API
$this->actingAs($user, 'sanctum')
     ->getJson('/api/me')
     ->assertOk();
```

### Authorization

```php
$user = User::factory()->create();
$otherUser = User::factory()->create();
$post = Post::factory()->forUser($otherUser)->create();

$this->actingAs($user)
     ->delete("/posts/{$post->id}")
     ->assertForbidden();
```

---

## Mocking External Services

### Mail

```php
Mail::fake();

$this->post('/register', [...]);

Mail::assertSent(WelcomeMail::class, function ($mail) use ($email) {
    return $mail->hasTo($email);
});
```

### Queues

```php
Queue::fake();

$this->post('/orders', [...]);

Queue::assertPushed(ProcessOrder::class);
Queue::assertPushedOn('priority', ProcessOrder::class);
```

### Events

```php
Event::fake();

UserRegistered::dispatch($user);

Event::assertDispatched(UserRegistered::class);
```

### HTTP

```php
Http::fake([
    'api.stripe.com/*' => Http::response(['id' => 'cus_123'], 200),
    'api.sendgrid.com/*' => Http::response([], 202),
]);

$service->createCustomer($user);

Http::assertSent(function ($request) {
    return str_contains($request->url(), 'stripe.com')
        && $request['email'] === 'foo@bar.com';
});
```

### Storage

```php
Storage::fake('s3');

$this->post('/upload', [
    'file' => UploadedFile::fake()->image('avatar.jpg'),
]);

Storage::disk('s3')->assertExists('avatars/avatar.jpg');
```

---

## Testing Jobs

```php
it('processes order in background', function () {
    Queue::fake();

    $order = Order::factory()->create();

    $this->post("/orders/{$order->id}/confirm");

    Queue::assertPushed(ProcessOrder::class, function ($job) use ($order) {
        return $job->order->id === $order->id;
    });
});

it('process order updates status', function () {
    $order = Order::factory()->create();

    (new ProcessOrder($order))->handle();

    expect($order->refresh()->status)->toBe('processed');
});
```

Test that the job is queued (HTTP test) AND that the job logic works (unit test).

---

## Database Assertions

```php
$this->assertDatabaseHas('users', ['email' => 'foo@bar.com']);
$this->assertDatabaseMissing('users', ['email' => 'banned@bar.com']);
$this->assertDatabaseCount('posts', 5);
$this->assertSoftDeleted('users', ['id' => $user->id]);
$this->assertModelExists($user);
$this->assertModelMissing($user);
```

---

## Pest Datasets (Senior Power Move)

```php
it('validates required fields', function (string $field) {
    $response = $this->post('/posts', [
        'title' => 'Test',
        'body'  => 'Body',
    ] + [$field => '']);

    $response->assertSessionHasErrors($field);
})->with(['title', 'body']);
```

Run the same test with multiple inputs. DRY validation tests.

---

## Architecture Tests (Pest's Killer Feature)

```php
// tests/Architecture/ArchitectureTest.php
arch('controllers do not call models directly')
    ->expect('App\Http\Controllers')
    ->not->toUse('App\Models');

arch('models are not used in jobs without queue')
    ->expect('App\Jobs')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue');

arch('all enums are backed strings')
    ->expect('App\Support\Enums')
    ->toBeStringBackedEnums();

arch('actions are final')
    ->expect('App\Actions')
    ->toBeFinal();
```

Architecture tests catch design drift. Run in CI. PR fails if someone breaks the rules.

---

## Snapshot Testing (For Output Stability)

```bash
composer require spatie/pest-plugin-snapshots --dev
```

```php
it('renders email correctly', function () {
    $user = User::factory()->create();
    $mail = new WelcomeMail($user);
    expect($mail->render())->toMatchSnapshot();
});
```

First run: snapshot saved. Future runs: must match. Catches accidental output changes.

---

## Browser Testing (Dusk, When Needed)

Only when JS interactions matter and can't be tested via HTTP.

```bash
composer require laravel/dusk --dev
php artisan dusk:install
```

```php
it('user can log in via SPA', function () {
    $user = User::factory()->create(['password' => bcrypt('secret')]);

    $this->browse(function (Browser $browser) use ($user) {
        $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'secret')
                ->press('Sign In')
                ->assertPathIs('/dashboard');
    });
});
```

Dusk tests are SLOW (5s+ each). Use for critical paths only.

---

## Coverage Goals

| Code | Coverage Target |
|------|-----------------|
| Models | 80%+ |
| Actions | 90%+ |
| Services | 80%+ |
| Controllers | 70%+ (covered by Feature tests) |
| Jobs | 80%+ |
| Helpers | 100% |
| Total project | 70%+ |

**Don't** chase 100%. Chase confidence.

```bash
php artisan test --coverage --min=70
```

---

## What NOT to Test

1. **Framework code.** Don't test that `Eloquent::create` works.
2. **3rd-party libraries.** Stripe SDK is their problem.
3. **Trivial getters/setters.** Diminishing returns.
4. **Generated code.** Just check it compiles.
5. **Migrations.** Test the resulting behavior, not the migration.

---

## TDD Discipline

For new features:

1. **Red** — Write the failing test
2. **Green** — Write the minimum code to pass
3. **Refactor** — Clean up, tests still green

```php
// 1. Red
it('publishes a post', function () {
    $post = Post::factory()->draft()->create();

    $post->publish();

    expect($post->fresh()->is_published)->toBeTrue();
});
// Run: fails (no publish() method)

// 2. Green
public function publish(): void
{
    $this->update(['is_published' => true, 'published_at' => now()]);
}
// Run: passes

// 3. Refactor
public function publish(): void
{
    $this->update([
        'status' => PostStatus::Published,
        'published_at' => now(),
    ]);
}
```

TDD slows you down for 2 weeks, speeds you up forever after.

---

## CI Test Pipeline

```yaml
- name: Tests
  run: |
    php artisan test --parallel --coverage --min=70
```

Run on every PR. Block merge if fails. No exceptions.

---

## Flaky Test Triage

Flaky test = your test depends on something it shouldn't.

| Symptom | Cause |
|---------|-------|
| Passes locally, fails CI | DB state, timezone, locale |
| Passes alone, fails in suite | Shared state between tests |
| Passes sometimes | Race condition, time-dependent |
| Passes Monday, fails Tuesday | Date logic at midnight |

Fix:
- Use `Carbon::setTestNow()` for time
- Use `RefreshDatabase` to reset state
- Avoid `sleep()` and timing assumptions

```php
Carbon::setTestNow('2026-06-07 12:00:00');
// run test
Carbon::setTestNow(); // reset
```

---

## Performance Tests

For critical endpoints:

```php
it('lists posts in under 50ms', function () {
    Post::factory()->count(100)->published()->create();

    $start = microtime(true);
    $this->get('/posts')->assertOk();
    $elapsed = (microtime(true) - $start) * 1000;

    expect($elapsed)->toBeLessThan(50);
});
```

Catches regressions when someone adds an N+1.

---

## The Test Smell Checklist

If your test:
- Has 5+ assertions → split it
- Has `sleep()` → race condition
- Mocks 5+ things → architecture wrong
- Has `if/else` → split it
- Sets up 50+ lines of data → use factories better
- Tests the framework → delete it
- Tests private methods → test the public ones
- Tests UI text → fragile, prefer behavior

---

## The Senior's Testing Mantra

> If I delete this code, will any test fail?

If no, the code is untested.

> If I refactor this internally without changing behavior, will any test fail?

If yes, you're testing the wrong thing.

> If I rename this private method, will any test fail?

If yes, you're testing implementation.

The goal: test **behavior** users observe, not the path you took to get there.

---

## The Final Discipline

Tests are not perfect. Tests are not free. Tests are your future safety net.

A senior writes tests so:
- They can refactor without fear
- They can take vacation
- They can quit without notice
- They can sleep through deploys
- They can mentor juniors who break things
- They can charge more

If you skip tests, you pay later. Always.
