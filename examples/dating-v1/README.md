# Spark — Dating App Demo (Laravel 13 + Livewire 4)

Single-page dating demo with swipe, browse, match, chat and profile editing. The whole UI is one route (`GET /`) that renders the Livewire component `dating-app`. There is **no login screen**: the app logs you in as a seeded persona automatically, and a picker lets you switch persona. It's a local demo only. See [Pitfalls](#pitfalls) before exposing it anywhere.

## Quick start

```bash
cd examples/dating-v1
composer install
cp .env.example .env            # DB_CONNECTION=sqlite
php artisan key:generate
php artisan migrate --seed      # 12 personas + 2 pre-made matches with messages
npm install --ignore-scripts && npm run build   # or `npm run dev`; required, see pitfalls
php artisan serve               # http://127.0.0.1:8000
```

`composer setup` covers install, `.env`, key, migrate and the asset build, but **not** `--seed`. Run `php artisan db:seed` afterwards. With an empty `users` table the app has no one to log in as.

Tests: `php artisan test` (PHPUnit, in-memory SQLite). `DatingAppFlowTest` seeds the DB and renders `/`, so it needs built Vite assets (`public/build/manifest.json`).

Seed data (`database/seeders/DatabaseSeeder.php`): 12 users `alex@example.com` … `jordan@example.com`, all with password `password`. Alex Rivera is the first user, so he's the default persona. He's already matched with Sophia and with Elena, and both conversations have messages.

## Architecture

### Livewire single-file components (`resources/views/components/⚡*.blade.php`)

Each file holds an anonymous `new class extends Component` in a `<?php … ?>` block followed by its Blade markup. The `⚡` prefix is part of the filename, and you reference the component without it (e.g. `@livewire('dating-app')`).

| Component | Responsibility | Key actions / events |
|---|---|---|
| `dating-app` | Shell: tabs (`swipe`, `browse`, `matches`, `profile`), persona picker ("Acting as:"), match celebration modal | `mount()` auto-logs in `User::first()`; `switchPersona($id)` dispatches `personaSwitched`; listens for `matchCreated`, `openChat`, `tabChanged` |
| `swipe-deck` | Card stack | `loadQueue()` picks 15 random users you haven't swiped on yet, excluding yourself; `swipe('like'\|'pass'\|'superlike')`; `resetDeck()` deletes **all** your swipes |
| `browse-grid` | Filterable grid | Filters: `filterGender` (`all`/`woman`/`man`), `minAge`/`maxAge` (default 20–35); `quickLike($userId)` |
| `matches-chat` | Match list + conversation | `selectMatch()`, `sendMessage()`; chat pane polls with `wire:poll.2s` |
| `profile-drawer` | Edit current user's profile | `save()` updates `users.name` and upserts `profiles`; interests are entered comma-separated and stored as a JSON array |

### Matching rule

A match is created when a `like` or `superlike` meets an existing `like`/`superlike` in the other direction. The same check runs in `swipe-deck::swipe()` and `browse-grid::quickLike()`:

```php
DatingMatch::firstOrCreate(
    ['user_one_id' => min($a, $b), 'user_two_id' => max($a, $b)],   // ordered pair
    ['matched_at' => now()],
);
// then dispatches 'matchCreated' with ['user' => ..., 'match_id' => ...]
```

Always store pairs as (lower id, higher id). The unique index on `matches(user_one_id, user_two_id)` only prevents duplicates if every write follows that order. Note that the seeder writes `(alex, sophia)` / `(alex, elena)`, which is in id order because Alex is created first.

### Data model (`database/migrations/2026_03_30_000001_create_dating_tables.php`)

| Table / model | Notes |
|---|---|
| `profiles` / `Profile` | 1:1 with `users`; `photos` and `interests` are JSON arrays; defaults: `age` 25, `gender` `woman`, `interested_in` `all`, `city` `New York, NY` |
| `swipes` / `Swipe` | `type` enum `like`/`pass`/`superlike`; unique `(swiper_id, swiped_id)`; swipes are upserted |
| `matches` / `DatingMatch` | Model class is `DatingMatch` but `$table = 'matches'`; `messages()` ordered oldest first, `latestMessage()` |
| `messages` / `Message` | `match_id`, `sender_id`, `body`, `read_at` (column exists but nothing writes it yet) |

`User::allMatches()` returns a query builder over matches where the user is on either side. `User::getOtherUser($match)` returns the other participant.

## Pitfalls

- **Missing Vite manifest → 500 on `/`.** `welcome.blade.php` uses `@vite`. Without `npm run build` (or a running `npm run dev`), the page and `DatingAppFlowTest::test_welcome_page_renders_dating_app` fail with `ViteManifestNotFoundException`.
- **No authentication or authorization. Demo only.**
  - Any visitor is logged in as the first user.
  - `switchPersona()` logs in as any user id it's given.
  - `matches-chat` loads and posts to whatever `activeMatchId` it receives, without checking that the current user belongs to that match. A user outside a match can read and send messages in it.
- **Swipe `type` is only enforced by the DB.** `swipe()` doesn't validate its argument, so a value outside `like`/`pass`/`superlike` fails with a constraint `QueryException` (a CHECK constraint on SQLite).
- **`interested_in` and `distance_km` are display-only.** Neither the deck nor the grid filters on them. `browse-grid` also declares an unused `$search` property.
- **Nested app folder.** `vitou-test-livewire-auth/` inside this example is a separate Laravel app, almost identical to `examples/vitou-test-livewire-auth`. It isn't part of the dating app.
