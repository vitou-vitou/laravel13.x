# Company Task Hub (Laravel 13 + Filament)

Staff-only operations workspace that keeps three kinds of work apart: internal company tasks, daily incidents, and customer action items. Everything is managed from one Filament admin panel at `/admin`. Customers never log in.

Background reading: [`spec.md`](spec.md) (product intent), [`CONTEXT.md`](CONTEXT.md) (glossary), [`docs/adr/0001-separate-task-entities.md`](docs/adr/0001-separate-task-entities.md) (why there are three tables).

## Quick start

```bash
cd examples/company-task-hub
composer install
cp .env.example .env          # DB_CONNECTION=sqlite by default
php artisan key:generate
php artisan migrate --seed
php artisan serve             # http://127.0.0.1:8000/admin
```

`composer setup` runs install, copies `.env`, generates the key, migrates and builds assets, but it does **not** seed. Run `php artisan db:seed` afterwards if you want demo data.

Seeded logins (`database/seeders/DatabaseSeeder.php`), password `password` for both:

| Email | Name |
|---|---|
| `admin@companytaskhub.test` | Operations Admin |
| `lead@companytaskhub.test` | Tech Lead |

Run tests with `php artisan test` (PHPUnit, in-memory SQLite via `phpunit.xml`).

Build frontend assets before running the suite (`npm install && npm run build`). Otherwise `ExampleTest` (`GET /`) fails with `ViteManifestNotFoundException`, because `welcome.blade.php` calls `@vite` and there is no `public/build/manifest.json`.

## Architecture

### Three operational pillars (ADR 0001)

Each pillar has its own model, table and Filament resource. They all sit in the **Operations** navigation group.

| Pillar | Model / table | Filament resource (nav label) | Owner FK | Pillar-specific fields |
|---|---|---|---|---|
| Company General | `CompanyTask` / `company_tasks` | `CompanyTaskResource` (Company Tasks) | `assignee_id` | `department` (default `Operations`), `priority`, `due_date` |
| Resolve Issue Daily | `DailyIssueTask` / `daily_issue_tasks` | `DailyIssueTaskResource` (Daily Issues) | `resolver_id` | `incident_type` (default `bug`), `severity`, `root_cause`, `resolution_notes`, `resolved_at` |
| Customer General | `CustomerTask` / `customer_tasks` | `CustomerTaskResource` (Customer Tasks) | `handler_id` | `customer_name` (required), `customer_email` (validated as email in the form), `task_type` (default `onboarding`), `priority`, `notes`, `due_date` |

Owner FKs point to `users`, are nullable, and are set to `null` when the user is deleted.

Schema: `database/migrations/2026_09_16_000001_create_company_task_hub_tables.php`.

### Enums (`app/Enums`)

All three models cast `status` to `TaskStatus`. Each enum has `label()` and `color()` methods, and the Filament badge columns use `color()`.

| Enum | Values | Used by |
|---|---|---|
| `TaskStatus` | `pending`, `in_progress`, `completed`, `cancelled` | all three models |
| `TaskPriority` | `low`, `medium`, `high`, `urgent` | `CompanyTask`, `CustomerTask` |
| `IssueSeverity` | `minor`, `major` (default), `critical` | `DailyIssueTask` |

### Resolving an incident

Call `DailyIssueTask::markResolved()` to resolve an incident. Don't set the status by hand:

```php
$issue->markResolved(
    notes: 'Increased curl timeout from 2s to 10s',
    rootCause: 'Upstream gateway DNS latency',
    resolverId: $user->id,
);
// status => completed, resolved_at => now(); arguments you pass as null keep their existing values
```

The Filament edit form does **not** call `markResolved()`. If you set status to Completed in the UI, `resolved_at` is only filled when you also pick it in the date-time field.

### Dashboard widgets (`app/Filament/Widgets`)

- `OperationsOverviewWidget` shows three counts: pending company tasks, critical daily issues not yet `completed`, and customer tasks not yet `completed`.
- `TaskStatsOverview` and `TasksByStatusChart` belong to the **workspace** module below, not to the three pillars.

## Secondary modules in this app

These came from earlier iterations and are still wired in:

- **Workspaces / Projects / Tasks** (`app/Filament/Resources/{Workspaces,Projects,Tasks}`, `app/Support/WorkspaceContext.php`). These are multi-workspace tasks. A user belongs to workspaces through `workspace_user` (with a `role` pivot). `WorkspaceContext::current()` returns `users.current_workspace_id` if the user is a member of that workspace, and otherwise their first workspace. Project and task queries are scoped to that workspace, and creating one stamps `workspace_id`. Users with no workspace see nothing (`whereRaw('1 = 0')`). Task statuses here are the string constants `todo` / `in_progress` / `done` on `App\Models\Task`, not the `TaskStatus` enum.
- **Todo JSON API** (`routes/api.php`, `TodoController`). This is unauthenticated (`authorize()` returns `true`).

  | Method | Path | Rate limiter |
  |---|---|---|
  | GET | `/api/v1/todos`, `/api/v1/todos/{todo}` | `api` (60/min per user or IP) |
  | POST | `/api/v1/todos` | `api` + `api-writes` (30/min) |
  | PATCH | `/api/v1/todos/{todo}` | `api` + `api-writes` |
  | DELETE | `/api/v1/todos/{todo}` | `api` + `api-writes` |
  | PATCH | `/api/v1/todos/{todo}/complete`, `/incomplete` | `api` + `api-writes` |

  Index query params: `status`, `priority`, `search` (title/description LIKE), `sort` (`created_at`, `updated_at`, `due_date` or `priority`), `direction` (`asc`/`desc`), `per_page` (default 15). `due_date` must be after today. Limiters are defined in `AppServiceProvider`.
- **Signed URL demo** (`routes/web.php`). `GET /signing` returns a signed URL for `GET /action`, which returns 403 unless the signature is valid. Background notes are in [`safe-link-structure.md`](safe-link-structure.md).

## Common pitfalls

- **Creating a Daily Issue or Customer Task without `status` breaks the record.** The migration default for `status` on `daily_issue_tasks` and `customer_tasks` is `open`, but `TaskStatus` has no `open` case. A row saved without an explicit status therefore can't be cast when it's read back. The Filament forms always send `pending`, and the seeder and factories set status too, so this mostly affects tinker, raw inserts and new code paths. Always pass a `TaskStatus` value.
- **Workspace widgets show zeros after seeding.** The seeder creates no workspaces, so `TaskStatsOverview` and `TasksByStatusChart` are empty until the logged-in user creates or joins a workspace. Creating one through the Workspaces resource attaches the user as `owner` and makes it their current workspace.
- **Every user can open the panel.** `User::canAccessPanel()` returns `true`. There are no roles or policies on the pillar resources yet.
- **`GET /api/user` is not usable.** It uses `auth:sanctum`, but Laravel Sanctum is not installed (it isn't in `composer.json` or `composer.lock`).
- **`spec.md` mentions Pest and PHP 8.4.** The suite is actually PHPUnit (`tests/Feature/CompanyTaskHub*Test.php`, `WorkspaceTenancyTest.php`), and `composer.json` requires `php ^8.3`.
