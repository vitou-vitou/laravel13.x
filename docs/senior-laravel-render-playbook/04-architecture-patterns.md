# 04 — Architecture Patterns

> Architecture is the cost of changing your mind 6 months from now. Senior devs design for change, not perfection.

---

## The Three Layers Senior Devs Respect

```
┌─────────────────────────────────────────┐
│  Presentation Layer                     │
│  Controllers, Requests, Resources, Views│
├─────────────────────────────────────────┤
│  Domain Layer                           │
│  Actions, Services, Models, Policies    │
├─────────────────────────────────────────┤
│  Infrastructure Layer                   │
│  DB, Cache, Queue, External APIs        │
└─────────────────────────────────────────┘
```

**Rule:** Domain depends on nothing above it. Infrastructure is replaceable. Presentation is throwaway.

---

## Pattern 1: Thin Controllers

A controller has ONE job: receive HTTP, return HTTP.

### Bad
```php
class PostController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $post = new Post();
        $post->title = $validated['title'];
        $post->slug = Str::slug($validated['title']);
        $post->body = $validated['body'];
        $post->user_id = auth()->id();
        $post->save();

        Mail::to(auth()->user())->send(new PostCreatedMail($post));

        if ($request->wantsJson()) {
            return response()->json($post);
        }

        return redirect()->route('posts.show', $post);
    }
}
```

### Good
```php
class PostController extends Controller
{
    public function store(StorePostRequest $request, CreatePost $action)
    {
        $post = $action->execute($request->user(), $request->validated());

        return $request->wantsJson()
            ? PostResource::make($post)
            : redirect()->route('posts.show', $post);
    }
}
```

The controller is 5 lines. All logic is testable in isolation.

---

## Pattern 2: Actions (Single-Purpose Classes)

```php
// app/Actions/Post/CreatePost.php
final class CreatePost
{
    public function __construct(
        private readonly SlugGenerator $slugs,
        private readonly Dispatcher $events,
    ) {}

    public function execute(User $author, array $data): Post
    {
        return DB::transaction(function () use ($author, $data) {
            $post = $author->posts()->create([
                'title' => $data['title'],
                'slug'  => $this->slugs->forTitle($data['title']),
                'body'  => $data['body'],
            ]);

            $this->events->dispatch(new PostCreated($post));

            return $post;
        });
    }
}
```

**Why actions over services?**
- One file = one verb = one test
- Constructor injection of dependencies
- Trivially mockable
- Discoverable by name

When an Action grows past ~50 lines, split it into smaller Actions or extract a Service.

---

## Pattern 3: Form Requests for Validation

```php
final class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Post::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body'  => ['required', 'string'],
            'tags'  => ['array', 'max:10'],
            'tags.*' => ['string', 'max:50'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim($this->title ?? ''),
        ]);
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Posts must have a title.',
        ];
    }
}
```

Validation lives ONE place. Reusable across controllers (API + Web).

---

## Pattern 4: API Resources for Output Shaping

```php
final class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'slug'       => $this->slug,
            'excerpt'    => Str::limit($this->body, 200),
            'published_at' => $this->published_at?->toIso8601String(),
            'author'     => UserResource::make($this->whenLoaded('user')),
            'tags'       => TagResource::collection($this->whenLoaded('tags')),
            'meta'       => [
                'comments_count' => $this->whenCounted('comments'),
                'is_owned'       => $request->user()?->id === $this->user_id,
            ],
        ];
    }
}
```

Models change. APIs are contracts. Resources are the firewall between them.

---

## Pattern 5: Services for Cross-Cutting Concerns

Services handle workflows that span multiple models or external systems.

```php
// app/Services/Billing/SubscriptionService.php
final class SubscriptionService
{
    public function __construct(
        private readonly StripeClient $stripe,
        private readonly NotificationDispatcher $notifications,
        private readonly InvoiceGenerator $invoices,
    ) {}

    public function upgradeUser(User $user, Plan $plan): Subscription
    {
        $stripeSub = $this->stripe->subscriptions->create([
            'customer' => $user->stripe_id,
            'items' => [['price' => $plan->stripe_price_id]],
        ]);

        $sub = $user->subscriptions()->create([
            'plan_id' => $plan->id,
            'stripe_id' => $stripeSub->id,
            'status' => $stripeSub->status,
        ]);

        $this->invoices->generateUpgradeInvoice($user, $plan);
        $this->notifications->send($user, new SubscriptionUpgraded($plan));

        return $sub;
    }
}
```

Inject services into Actions, Jobs, and Controllers via the container.

---

## Pattern 6: Repositories — Avoid

Don't write Repository pattern in Laravel. Eloquent IS your repository.

```php
// BAD — Eloquent already does this
interface UserRepositoryInterface
{
    public function find(int $id): ?User;
    public function create(array $data): User;
}

// GOOD — just use the model
User::find($id);
User::create($data);
```

**The exception:** when you have multiple data sources (DB + external API + cache) and need a unified interface. Even then, name it something specific (`UserFinder`, not `UserRepository`).

---

## Pattern 7: Value Objects for Domain Concepts

```php
// app/Support/ValueObjects/Money.php
final readonly class Money
{
    public function __construct(
        public int $amount,    // cents
        public string $currency = 'USD',
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Negative money');
        }
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function format(): string
    {
        return number_format($this->amount / 100, 2) . ' ' . $this->currency;
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Currency mismatch');
        }
    }
}
```

Eloquent cast it:
```php
protected $casts = [
    'price_amount' => 'integer',
];

protected function price(): Attribute
{
    return Attribute::make(
        get: fn() => new Money($this->price_amount, $this->price_currency),
    );
}
```

Value objects eliminate entire bug categories.

---

## Pattern 8: Enums for State

```php
enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Awaiting Payment',
            self::Paid => 'Paid',
            self::Shipped => 'In Transit',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return match($this) {
            self::Pending => in_array($next, [self::Paid, self::Cancelled]),
            self::Paid => in_array($next, [self::Shipped, self::Cancelled]),
            self::Shipped => in_array($next, [self::Delivered]),
            default => false,
        };
    }
}
```

Cast in Eloquent:
```php
protected $casts = [
    'status' => OrderStatus::class,
];
```

Now `$order->status` is an enum. IDE autocompletes. PHPStan validates. No magic strings.

---

## Pattern 9: Policies for Authorization

```php
final class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Post $post): bool
    {
        return $post->is_published || $post->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'editor']);
    }

    public function update(User $user, Post $post): bool
    {
        return $post->user_id === $user->id || $user->hasRole('admin');
    }

    public function delete(User $user, Post $post): bool
    {
        return $post->user_id === $user->id || $user->hasRole('admin');
    }
}
```

Use in controllers:
```php
$this->authorize('update', $post);
```

In Blade/Vue:
```php
@can('update', $post)
    <button>Edit</button>
@endcan
```

In policies, **never** call DB queries unless cached. Authorization runs per-request, per-resource.

---

## Pattern 10: Events for Decoupling

```php
// app/Events/UserRegistered.php
final class UserRegistered
{
    public function __construct(public User $user) {}
}

// app/Listeners/SendWelcomeEmail.php
final class SendWelcomeEmail implements ShouldQueue
{
    public function handle(UserRegistered $event): void
    {
        Mail::to($event->user)->send(new WelcomeMail($event->user));
    }
}

// app/Listeners/CreateTrialSubscription.php
final class CreateTrialSubscription implements ShouldQueue
{
    public function handle(UserRegistered $event): void
    {
        $event->user->subscriptions()->create([
            'plan_id' => Plan::free()->id,
            'trial_ends_at' => now()->addDays(14),
        ]);
    }
}
```

`UserRegistered` is fired ONCE. Multiple listeners react. Each listener is independent, async, retryable.

---

## Pattern 11: Pipelines for Multi-Step Operations

```php
$result = app(Pipeline::class)
    ->send($order)
    ->through([
        ValidateInventory::class,
        ChargeCustomer::class,
        ReserveShipping::class,
        SendConfirmation::class,
    ])
    ->thenReturn();
```

Each step is a class with `handle($order, Closure $next)`. Easy to add/remove steps. Easy to test each step.

---

## Pattern 12: Domain Boundaries (DDD-Lite)

For larger apps, split `app/` by domain, not by technical type:

```
app/
├── Billing/
│   ├── Actions/
│   ├── Models/
│   ├── Services/
│   └── Events/
├── Catalog/
│   ├── Actions/
│   ├── Models/
│   └── Services/
├── Identity/
└── Notifications/
```

**Rule:** Domains don't import each other directly. They communicate via Events or thin adapter services.

Use only when:
- App has 50k+ LOC
- Multiple teams own different domains
- Clear bounded contexts exist

For < 20k LOC apps, the default Laravel structure is fine.

---

## Pattern 13: Repository for External APIs

When integrating with Stripe, Twilio, SendGrid, etc:

```php
// app/Integrations/Stripe/StripeClient.php (real impl)
final class StripeClient implements StripeContract { /* ... */ }

// app/Integrations/Stripe/StripeFake.php (test fake)
final class StripeFake implements StripeContract { /* ... */ }
```

Bind in `AppServiceProvider`:
```php
$this->app->bind(StripeContract::class, function () {
    return app()->environment('testing')
        ? new StripeFake()
        : new StripeClient(config('services.stripe.secret'));
});
```

Tests run zero network calls. Production uses real Stripe. Clean.

---

## The Sacred Boundary: Models vs Logic

**Models** know about:
- Their table
- Their relationships
- Their casts and attributes
- Their scopes (`scopePublished`, `scopeForUser`)

**Models** do NOT know about:
- HTTP requests
- Sending emails
- External APIs
- Cache invalidation
- Authorization

If you find yourself writing `Mail::send` inside a model method, extract it to an Action.

---

## The Container: Bind, Don't `new`

Always inject dependencies:

```php
// BAD
public function execute(User $user): void
{
    $stripe = new StripeClient(config('services.stripe.secret'));
    $stripe->charge($user, 1000);
}

// GOOD
public function __construct(private readonly StripeContract $stripe) {}

public function execute(User $user): void
{
    $this->stripe->charge($user, 1000);
}
```

Why:
- Testable (swap with fake)
- Reusable (same dependency tree)
- Type-safe (PHPStan validates)
- Discoverable (you see what's needed)

---

## When to Refactor

| Smell | Refactor |
|-------|----------|
| Controller > 30 lines | Extract Action |
| Action > 80 lines | Split into smaller Actions |
| Model > 300 lines | Extract Services or split model |
| Method > 20 lines | Extract private methods |
| Class has 5+ public methods | Probably 2+ classes |
| Same logic in 2+ places | Extract Action/Service |
| `if (request()->is('api/*'))` in domain code | Move to controller |
| Mock chains 3+ deep in tests | Architecture is wrong |

---

## The "Will Junior Understand It?" Test

After writing a new pattern, ask: "Will a junior dev understand this in 6 months?"

If no, you're being too clever. Simplify.

Senior code is **boring on purpose**. Boring = predictable = maintainable = profitable.

---

## The Architecture Hierarchy of Pain

Easiest to change → hardest to change:

1. Controller method body (5 min)
2. Action implementation (15 min)
3. Service class (1 hour)
4. Model fields/casts (1 hour + migration)
5. Database schema (half day + data migration)
6. Module boundaries (week)
7. Framework choice (months)
8. Language choice (year)

Spend your design effort on the hard-to-change. Iterate freely on the easy-to-change.
