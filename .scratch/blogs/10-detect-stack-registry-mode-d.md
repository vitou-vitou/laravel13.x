# Audit Any Laravel Codebase in Five Minutes: How to Stop Architecture Drift

A new engineer joins your team, picks up a ticket to add an export button to an existing customer table, and submits a pull request with twenty new files. They introduced an Action class, two DTOs, a Repository interface, and an ad-hoc REST controller inside a codebase that has used Filament for two years. They did not do it with bad intentions; they did it because they did not inspect the existing architecture before writing code.

Architecture drift is one of the quietest killers of velocity in engineering teams. Developers bring patterns from their previous jobs or favourite blogs into a repository that already has established conventions. If you spend five minutes inspecting package locks, route declarations, and template directories before you write a single line of code, you can identify the authoritative stack and preserve the health of your codebase.

## Step 1: Read the Lockfile Before the Controllers

Controllers and views show you how people wrote code last month. `composer.json` and `package.json` show you the authoritative technical contracts locked into the repository.

Start your audit by checking the PHP dependencies:

```bash
# Look for UI frameworks and admin packages
composer show | grep -E "filament|livewire|inertia|nova|backpack|orchid|sanctum"
```

Look for the primary UI signals:

| Package in Lockfile | Primary UI Stack | Authoritative Conventions |
|---|---|---|
| `filament/filament` | Filament Admin Panel | Resources, Tables, Form schemas, Actions |
| `livewire/livewire` | Full-stack Livewire | Components, Form Objects, Blade reactivity |
| `inertiajs/inertia-laravel` | Inertia Monolith | Server controllers, page props, `useForm` |
| `laravel/sanctum` + external SPA | Headless API | Form Requests, API Resources, stateful cookies |
| Plain `laravel/framework` | Blade MVC | Form Requests, Eloquent, standard Blade views |

Next, inspect `package.json` to verify the client framework. If `composer.json` shows Inertia, your npm dependencies tell you whether the team writes React (`@inertiajs/react`) or Vue (`@inertiajs/vue3`). Do not write a React component in a repository where Vue is locked into production.

## Step 2: Map the Primary Presentation Layer

Once you know what dependencies exist, confirm where the user-facing screens actually live:

- `resources/views/`: Standard Blade templates or Livewire views.
- `resources/js/Pages/`: Inertia page components.
- `app/Filament/Resources/`: Filament admin panels.
- `app/Livewire/`: Full-page or nested Livewire components.
- `routes/api.php`: Headless JSON endpoints for mobile apps or detached SPAs.

If you find multiple presentation directories, look for a intentional boundary. A common production architecture is a public-facing catalog rendered in Blade paired with an internal staff portal running in Filament under `/admin`.

The rule for mixed repositories is straightforward: touch only the side your ticket belongs to. Never "modernize" a Blade screen into an Inertia page in the middle of a bug fix ticket because you prefer React. A single ticket should never introduce a new presentation paradigm without an explicit architectural decision from the team.

## Step 3: Match the Nearest Neighbor

Before writing a new feature, find a feature that does something similar in the same repository and open it side-by-side with your editor.

If you are creating a new administrative resource in Filament, inspect an existing resource in `app/Filament/Resources/`:

app/Filament/Resources/CustomerResource.php:
```php
// Existing pattern in the codebase: declarative table and form schemas
public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('email')->searchable(),
            Tables\Columns\TextColumn::make('created_at')->date(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ]);
}
```

If the existing resources use standard Filament table actions, do not invent a custom Livewire modal or a raw Blade view for your new screen. Match the naming conventions, validation placement, and authorization hooks that your colleagues already use.

## Step 4: Codify Architecture Invariants in the Repo

Teams fight the same architecture battles repeatedly because architectural rules exist only in senior engineers' heads. When rules are not written down, every pull request becomes an argument about code style.

Create a short, explicit architecture guide at the root of the repository:

docs/ARCHITECTURE.md:
```markdown
# Repository Architecture Guidelines

## Primary Stack
- Backend: Laravel 11 on PHP 8.3
- Admin Panel: Filament v3 (located in `app/Filament`)
- Public Site: Standard Blade MVC (located in `resources/views`)

## Invariants
1. Do not create custom controllers for administrative CRUD. Use Filament Resources.
2. Put all request validation in Form Requests under `app/Http/Requests`.
3. Check permissions using standard Laravel Policies under `app/Policies`.
4. Keep controllers under 30 lines. Complex multi-table writes go into `app/Actions`.
5. Do not install a second admin package or client-side SPA framework.
```

When new engineers join, point them to this document. When reviewing pull requests, cite these invariants. If someone proposes introducing a new architectural pattern, make them update `ARCHITECTURE.md` as part of that RFC before writing code.

## What Can Go Wrong

The biggest trap when auditing a codebase is mistaking a prototype or abandoned experiment for an architectural pattern. You might find a single legacy controller using raw SQL queries or an abandoned `Vue` component from three years ago in a dark corner of `resources/js/`.

Always verify whether a pattern is active by checking recent git commits:

```bash
# Check when a file was last modified and who worked on it
git log -n 5 --oneline -- app/Http/Controllers/LegacyExportController.php
```

If a pattern has not been touched in two years and only appears in one file, it is an orphan, not a convention. Follow the patterns that actively ship in recent commits.

## Summary

Before you write code in any Laravel application, pause and run a five-minute audit. Check `composer.lock` and `package.json` for primary dependencies, map the presentation directories, match the nearest neighbor's idioms, and document your invariants in an `ARCHITECTURE.md` file.

Consistency beats personal preference every time. When every developer on the team respects the established boundaries of the codebase, shipping features is fast, onboarding is painless, and code reviews remain focused on business value.

## Further Reading

- [Laravel Directory Structure Documentation](https://laravel.com/docs/structure)
- [Composer Package Management](https://getcomposer.org/doc/01-basic-usage.md)
- [Filament Panel Configuration](https://filamentphp.com/docs/panels/configuration)
- [Writing Architectural Decision Records (ADRs)](https://adr.github.io/)

How does your team document and enforce architectural consistency across Laravel repositories? Share your approach in the comments below.
