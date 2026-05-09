# User guest dashboard example — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:subagent-driven-development` (recommended) or `superpowers:executing-plans` to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add new Laravel 13 + Filament ~5 example app at `examples/user-guest-dashboard` with guest-only panel, Continue-as-guest login surface (2 features), and dashboard (`DemoItem` resource + stat widget).

**Architecture:** `Guest` Eloquent model + `guest` session guard; Filament panel id `guest`, path `guest`, `authGuard('guest')`. Custom `GuestLogin` extends Filament login: explainer section + Livewire action creates guest, logs in, regenerates session, redirects with notification. `DemoItem` belongs to `guest_id`; resource query scoped to `auth()->id()`. No user registration or email login in v1.

**Tech Stack:** PHP ^8.3, Laravel ^13, Filament `filament/filament` (match `examples/basic-laravel-filamentphp` lock if pinning), SQLite for local demo, PHPUnit.

---

## File map (create / modify)

| Path | Role |
|------|------|
| `examples/user-guest-dashboard/` | New app root (composer scaffold) |
| `examples/user-guest-dashboard/config/auth.php` | Add `guest` guard + `guests` provider |
| `examples/user-guest-dashboard/app/Models/Guest.php` | Authenticatable + `FilamentUser` |
| `examples/user-guest-dashboard/app/Models/DemoItem.php` | Owned by guest |
| `examples/user-guest-dashboard/database/migrations/*_create_guests_table.php` | `uuid` unique |
| `examples/user-guest-dashboard/database/migrations/*_create_demo_items_table.php` | `guest_id` FK |
| `examples/user-guest-dashboard/database/factories/GuestFactory.php` | Test data |
| `examples/user-guest-dashboard/database/factories/DemoItemFactory.php` | Test data |
| `examples/user-guest-dashboard/app/Providers/Filament/GuestPanelProvider.php` | Panel config |
| `examples/user-guest-dashboard/app/Filament/Auth/GuestLogin.php` | Continue-as-guest + explainer |
| `examples/user-guest-dashboard/app/Filament/Resources/DemoItems/DemoItemResource.php` | Resource entry |
| `examples/user-guest-dashboard/app/Filament/Resources/DemoItems/Schemas/DemoItemForm.php` | Form |
| `examples/user-guest-dashboard/app/Filament/Resources/DemoItems/Tables/DemoItemsTable.php` | Table |
| `examples/user-guest-dashboard/app/Filament/Resources/DemoItems/Pages/*.php` | List / Create / Edit |
| `examples/user-guest-dashboard/app/Filament/Widgets/DemoItemCountWidget.php` | Dashboard stat |
| `examples/user-guest-dashboard/bootstrap/providers.php` | Register `GuestPanelProvider` only |
| `examples/user-guest-dashboard/tests/Feature/ContinueAsGuestTest.php` | Guest bootstrap |
| `examples/user-guest-dashboard/tests/Feature/DemoItemIsolationTest.php` | Row isolation |
| `examples/user-guest-dashboard/README.md` | Setup + run instructions |

---

### Task 1: Scaffold Laravel app

**Files:**
- Create: entire tree under `examples/user-guest-dashboard/` via Composer

- [ ] **Step 1: Create project**

Run from repo root:

```bash
cd examples
composer create-project laravel/laravel user-guest-dashboard "^13.0"
cd user-guest-dashboard
```

Expected: `composer.json` contains `laravel/framework: ^13.0`.

- [ ] **Step 2: SQLite + env**

```bash
touch database/database.sqlite
```

Edit `.env`: set `DB_CONNECTION=sqlite`, remove or comment `DB_HOST`/`DB_DATABASE` mysql lines so SQLite file is used (match `basic-laravel-filamentphp` pattern).

- [ ] **Step 3: App key + migrate empty**

```bash
php artisan key:generate
php artisan migrate --force
```

Expected: migrate completes with no errors.

- [ ] **Step 4: Commit**

```bash
git add examples/user-guest-dashboard
git commit -m "chore: scaffold user-guest-dashboard Laravel 13 app"
```

---

### Task 2: Install Filament

**Files:**
- Modify: `examples/user-guest-dashboard/composer.json` / `composer.lock`
- Create: Filament default files from installer (panel provider, config)

- [ ] **Step 1: Require Filament**

```bash
cd examples/user-guest-dashboard
composer require filament/filament
```

- [ ] **Step 2: Install panel (non-interactive)**

```bash
php artisan filament:install --panels --no-interaction
```

Expected: `app/Providers/Filament/AdminPanelProvider.php` (or similar) exists; `bootstrap/providers.php` lists Filament provider.

- [ ] **Step 3: Build assets (if install asks)**

```bash
npm install
npm run build
```

- [ ] **Step 4: Commit**

```bash
git add examples/user-guest-dashboard
git commit -m "feat: add Filament to user-guest-dashboard"
```

---

### Task 3: Guests + demo_items migrations

**Files:**
- Create: `examples/user-guest-dashboard/database/migrations/xxxx_xx_xx_000001_create_guests_table.php`
- Create: `examples/user-guest-dashboard/database/migrations/xxxx_xx_xx_000002_create_demo_items_table.php`

- [ ] **Step 1: Add migrations**

`create_guests_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
```

`create_demo_items_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained('guests')->cascadeOnDelete();
            $table->string('title');
            $table->text('body')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_items');
    }
};
```

- [ ] **Step 2: Migrate**

```bash
cd examples/user-guest-dashboard
php artisan migrate --force
```

Expected: both tables exist.

- [ ] **Step 3: Commit**

```bash
git add examples/user-guest-dashboard/database/migrations
git commit -m "feat: add guests and demo_items tables"
```

---

### Task 4: Guest + DemoItem models and factories

**Files:**
- Create: `app/Models/Guest.php`
- Create: `app/Models/DemoItem.php`
- Create: `database/factories/GuestFactory.php`
- Create: `database/factories/DemoItemFactory.php`

- [ ] **Step 1: Guest model**

```php
<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable;

class Guest extends Model implements Authenticatable, FilamentUser
{
    /** @use HasFactory<\Database\Factories\GuestFactory> */
    use HasFactory;
    use \Illuminate\Auth\Authenticatable;
    use Authorizable;

    protected $fillable = [
        'uuid',
    ];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
        ];
    }

    public function demoItems(): HasMany
    {
        return $this->hasMany(DemoItem::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'guest';
    }
}
```

- [ ] **Step 2: DemoItem model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemoItem extends Model
{
    /** @use HasFactory<\Database\Factories\DemoItemFactory> */
    use HasFactory;

    protected $fillable = [
        'guest_id',
        'title',
        'body',
    ];

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
```

- [ ] **Step 3: Factories**

`database/factories/GuestFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Guest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Guest>
 */
class GuestFactory extends Factory
{
    protected $model = Guest::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
        ];
    }
}
```

`database/factories/DemoItemFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\DemoItem;
use App\Models\Guest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DemoItem>
 */
class DemoItemFactory extends Factory
{
    protected $model = DemoItem::class;

    public function definition(): array
    {
        return [
            'guest_id' => Guest::factory(),
            'title' => fake()->sentence(3),
            'body' => fake()->optional()->paragraph(),
        ];
    }
}
```

- [ ] **Step 4: Commit**

```bash
git add examples/user-guest-dashboard/app/Models examples/user-guest-dashboard/database/factories
git commit -m "feat: add Guest and DemoItem models with factories"
```

---

### Task 5: Auth config (guest guard)

**Files:**
- Modify: `examples/user-guest-dashboard/config/auth.php`

- [ ] **Step 1: Add guard and provider**

Inside `guards` array add:

```php
    'guest' => [
        'driver' => 'session',
        'provider' => 'guests',
    ],
```

Inside `providers` array add:

```php
    'guests' => [
        'driver' => 'eloquent',
        'model' => App\Models\Guest::class,
    ],
```

Leave default `web` + `users` unchanged (Laravel defaults still useful for future).

- [ ] **Step 2: Commit**

```bash
git add examples/user-guest-dashboard/config/auth.php
git commit -m "feat: add guest auth guard and provider"
```

---

### Task 6: GuestPanelProvider

**Files:**
- Create: `examples/user-guest-dashboard/app/Providers/Filament/GuestPanelProvider.php`
- Delete or stop registering: `AdminPanelProvider.php` from Filament installer
- Modify: `examples/user-guest-dashboard/bootstrap/providers.php`

- [ ] **Step 1: GuestPanelProvider**

Copy middleware stack from `examples/basic-laravel-filamentphp/app/Providers/Filament/AdminPanelProvider.php` and adapt:

```php
<?php

namespace App\Providers\Filament;

use App\Filament\Auth\GuestLogin;
use App\Filament\Widgets\DemoItemCountWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class GuestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('guest')
            ->path('guest')
            ->login(GuestLogin::class)
            ->authGuard('guest')
            ->colors([
                'primary' => Color::Sky,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                DemoItemCountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
```

- [ ] **Step 2: Register only GuestPanelProvider**

`bootstrap/providers.php`:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\GuestPanelProvider::class,
];
```

Remove `AdminPanelProvider` reference. Delete `app/Providers/Filament/AdminPanelProvider.php` if present.

- [ ] **Step 3: Commit**

```bash
git add examples/user-guest-dashboard/app/Providers/Filament examples/user-guest-dashboard/bootstrap/providers.php
git commit -m "feat: add Filament guest panel provider"
```

---

### Task 7: GuestLogin (Continue as guest + explainer)

**Files:**
- Create: `examples/user-guest-dashboard/app/Filament/Auth/GuestLogin.php`

- [ ] **Step 1: Implement page**

Filament v5 uses `Filament\Schemas`; match imports to installed `filament/filament` (same namespace style as `vendor/filament/filament/src/Auth/Pages/Login.php`).

```php
<?php

namespace App\Filament\Auth;

use App\Models\Guest;
use Filament\Actions\Action;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class GuestLogin extends Login
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('How this demo works')
                    ->schema([
                        Text::make('• Guest mode: anonymous demo session on this browser.'),
                        Text::make('• No account recovery — clearing cookies loses access to this guest row.'),
                        Text::make('• Registration is not implemented in v1; a real app would link guests to users for cross-device data.'),
                    ]),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('continueAsGuest')
                ->label('Continue as guest')
                ->action('continueAsGuest')
                ->color('primary'),
        ];
    }

    public function getSubheading(): ?string
    {
        return null;
    }

    public function continueAsGuest(): void
    {
        try {
            $guest = Guest::query()->create([
                'uuid' => (string) Str::uuid(),
            ]);
        } catch (\Throwable) {
            Notification::make()
                ->title('Could not start guest session')
                ->danger()
                ->send();

            return;
        }

        Filament::auth()->login($guest);
        session()->regenerate();

        Notification::make()
            ->title('Guest mode (demo)')
            ->success()
            ->send();

        $this->redirect(Filament::getUrl());
    }
}
```

**Note:** `Filament\Schemas\Components\Text::make()` first argument is the displayed string (see Filament v5 `Text` component).

- [ ] **Step 2: Smoke test**

```bash
cd examples/user-guest-dashboard
php artisan serve --port=8123
```

Open `http://127.0.0.1:8123/guest/login`, click **Continue as guest**, expect redirect to `/guest` dashboard.

- [ ] **Step 3: Commit**

```bash
git add examples/user-guest-dashboard/app/Filament/Auth/GuestLogin.php
git commit -m "feat: guest login page with continue-as-guest action"
```

---

### Task 8: DemoItem Filament resource

**Files:**
- Create: `app/Filament/Resources/DemoItems/DemoItemResource.php`
- Create: `app/Filament/Resources/DemoItems/Schemas/DemoItemForm.php`
- Create: `app/Filament/Resources/DemoItems/Tables/DemoItemsTable.php`
- Create: `app/Filament/Resources/DemoItems/Pages/ListDemoItems.php`
- Create: `app/Filament/Resources/DemoItems/Pages/CreateDemoItem.php`
- Create: `app/Filament/Resources/DemoItems/Pages/EditDemoItem.php`

- [ ] **Step 1: DemoItemForm**

```php
<?php

namespace App\Filament\Resources\DemoItems\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DemoItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->required()
                ->maxLength(255),
            Textarea::make('body')
                ->rows(4)
                ->columnSpanFull(),
        ]);
    }
}
```

- [ ] **Step 2: DemoItemsTable**

```php
<?php

namespace App\Filament\Resources\DemoItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DemoItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

- [ ] **Step 3: Pages**

`ListDemoItems.php`:

```php
<?php

namespace App\Filament\Resources\DemoItems\Pages;

use App\Filament\Resources\DemoItems\DemoItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDemoItems extends ListRecords
{
    protected static string $resource = DemoItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
```

`CreateDemoItem.php`:

```php
<?php

namespace App\Filament\Resources\DemoItems\Pages;

use App\Filament\Resources\DemoItems\DemoItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDemoItem extends CreateRecord
{
    protected static string $resource = DemoItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['guest_id'] = auth()->id();

        return $data;
    }
}
```

`EditDemoItem.php`:

```php
<?php

namespace App\Filament\Resources\DemoItems\Pages;

use App\Filament\Resources\DemoItems\DemoItemResource;
use Filament\Resources\Pages\EditRecord;

class EditDemoItem extends EditRecord
{
    protected static string $resource = DemoItemResource::class;
}
```

- [ ] **Step 4: DemoItemResource**

```php
<?php

namespace App\Filament\Resources\DemoItems;

use App\Filament\Resources\DemoItems\Pages\CreateDemoItem;
use App\Filament\Resources\DemoItems\Pages\EditDemoItem;
use App\Filament\Resources\DemoItems\Pages\ListDemoItems;
use App\Filament\Resources\DemoItems\Schemas\DemoItemForm;
use App\Filament\Resources\DemoItems\Tables\DemoItemsTable;
use App\Models\DemoItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class DemoItemResource extends Resource
{
    protected static ?string $model = DemoItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return DemoItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DemoItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDemoItems::route('/'),
            'create' => CreateDemoItem::route('/create'),
            'edit' => EditDemoItem::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('guest_id', auth()->id());
    }
}
```

- [ ] **Step 5: Verify in browser**

Create item, confirm only current guest rows show.

- [ ] **Step 6: Commit**

```bash
git add examples/user-guest-dashboard/app/Filament/Resources/DemoItems
git commit -m "feat: add DemoItem resource scoped to current guest"
```

---

### Task 9: Dashboard widget

**Files:**
- Create: `examples/user-guest-dashboard/app/Filament/Widgets/DemoItemCountWidget.php`

- [ ] **Step 1: Widget**

```php
<?php

namespace App\Filament\Widgets;

use App\Models\DemoItem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DemoItemCountWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $count = DemoItem::query()
            ->where('guest_id', auth()->id())
            ->count();

        return [
            Stat::make('Your demo items', (string) $count)
                ->description('Registration not implemented in this demo — link your own user flow in production.')
                ->url(\App\Filament\Resources\DemoItems\DemoItemResource::getUrl('index')),
        ];
    }
}
```

If `Stat::make()->url()` signature differs, use `->extraAttributes()` + description only.

- [ ] **Step 2: Commit**

```bash
git add examples/user-guest-dashboard/app/Filament/Widgets/DemoItemCountWidget.php
git commit -m "feat: add demo item count dashboard widget"
```

---

### Task 10: Feature tests (TDD-style ordering for new work)

**Files:**
- Create: `examples/user-guest-dashboard/tests/Feature/ContinueAsGuestTest.php`
- Create: `examples/user-guest-dashboard/tests/Feature/DemoItemIsolationTest.php`

- [ ] **Step 1: Continue-as-guest test**

```php
<?php

namespace Tests\Feature;

use App\Filament\Auth\GuestLogin;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContinueAsGuestTest extends TestCase
{
    use RefreshDatabase;

    public function test_continue_as_guest_creates_guest_and_authenticates(): void
    {
        $this->assertDatabaseCount('guests', 0);

        Livewire::test(GuestLogin::class)
            ->call('continueAsGuest')
            ->assertRedirect();

        $this->assertDatabaseCount('guests', 1);
        $this->assertAuthenticatedAs(Guest::query()->first(), 'guest');
    }
}
```

- [ ] **Step 2: Isolation test**

```php
<?php

namespace Tests\Feature;

use App\Filament\Resources\DemoItems\DemoItemResource;
use App\Models\DemoItem;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoItemIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_see_other_guest_demo_items_in_index(): void
    {
        $guestA = Guest::factory()->create();
        $guestB = Guest::factory()->create();

        DemoItem::factory()->create([
            'guest_id' => $guestA->id,
            'title' => 'Alpha secret',
        ]);
        DemoItem::factory()->create([
            'guest_id' => $guestB->id,
            'title' => 'Beta other',
        ]);

        $this->actingAs($guestA, 'guest');

        $response = $this->get(DemoItemResource::getUrl('index'));

        $response->assertOk();
        $response->assertSee('Alpha secret');
        $response->assertDontSee('Beta other');
    }
}
```

- [ ] **Step 3: Run tests**

```bash
cd examples/user-guest-dashboard
php artisan test tests/Feature/ContinueAsGuestTest.php tests/Feature/DemoItemIsolationTest.php
```

Expected: both pass.

- [ ] **Step 4: Commit**

```bash
git add examples/user-guest-dashboard/tests/Feature
git commit -m "test: guest bootstrap and demo item isolation"
```

---

### Task 11: README for example

**Files:**
- Create: `examples/user-guest-dashboard/README.md`

- [ ] **Step 1: Document**

Include: PHP version, `composer install`, copy `.env`, `touch database/database.sqlite`, `php artisan key:generate`, `php artisan migrate`, `npm install && npm run build`, `php artisan serve`, open `/guest/login`.

- [ ] **Step 2: Commit**

```bash
git add examples/user-guest-dashboard/README.md
git commit -m "docs: README for user-guest-dashboard example"
```

---

## Plan self-review

**Spec coverage**

| Spec item | Task(s) |
|-----------|---------|
| Guest-only panel, `guest` guard, `guests` table | Tasks 3–6 |
| Session regenerate + flash/notification | Task 7 |
| Continue-as-guest bootstrap | Tasks 6–7, 10 |
| Limits explainer on same surface | Task 7 (`Section` bullets) |
| `DemoItem` CRUD scoped by guest | Task 8 |
| Stat widget + CTA copy | Task 9 |
| DB failure shows notification, no auth | Task 7 `try/catch` |
| Tests (bootstrap + isolation) | Task 10 |

**Placeholder scan:** None intentional.

**Type consistency:** `Guest` factory + `actingAs(..., 'guest')` matches `config/auth.php` guard name `guest`. Panel id `guest` matches `canAccessPanel`.

---

**Plan complete and saved to `docs/superpowers/plans/2026-05-09-user-guest-dashboard.md`. Two execution options:**

1. **Subagent-driven (recommended)** — fresh subagent per task, review between tasks. **REQUIRED SUB-SKILL:** `superpowers:subagent-driven-development`.

2. **Inline execution** — run tasks in this session with checkpoints. **REQUIRED SUB-SKILL:** `superpowers:executing-plans`.

**Which approach?**
