# Laravel 13.x Monorepo — Project Roadmap

> Last updated: 2026-05-18

## Overview

This monorepo contains a Laravel 13 skeleton at root plus multiple runnable apps under `apps/` and reference examples under `examples/`. Each app is independently installable with its own dependencies.

---

## Root Application

**Path:** `/` (repository root)

**Status:** ✅ Active Development

**Stack:** Laravel 13.6.0, Vite 8, Tailwind v4, Laravel Passport

**Features:**
- Custom welcome page with Filament CTA
- Todo CRUD REST API (`/api/v1/todos`)
  - Resource-oriented REST design
  - JSON:API response format
  - Pagination, filtering, search
  - Rate limiting
  - FormRequest validation
  - API Resources for responses
- OAuth2 authentication via Laravel Passport
- SQLite (dev) / MySQL (prod)

**Setup:**
```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan passport:keys
npm install && npm run build
php artisan serve
```

---

## Apps Directory (`apps/`)

### Placeholder Apps (Reserved)

| App | Status | Purpose |
|-----|--------|---------|
| `app-01` to `app-10` | 📦 Reserved | Future project slots |

### Active Apps

#### `app-11-basic-filament`

**Status:** ✅ Complete

**Stack:** Laravel 13 + Filament v5

**Features:**
- Full Filament admin panel
- Resources, widgets, dashboard
- Clone of `examples/basic-laravel-filamentphp`

**Setup:**
```bash
cd apps/app-11-basic-filament
composer install
npm install
php artisan key:generate
php artisan migrate
php artisan test
```

---

#### `app-12-post-saas`

**Status:** ✅ Complete

**Stack:** Laravel 13 + Filament v5

**Features:**
- Multi-workspace SaaS pattern
- Posts datatable with Filament
- Workspace isolation

**Setup:**
```bash
cd apps/app-12-post-saas
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan test
```

---

## Examples Directory (`examples/`)

### Reference Implementations

#### `basic-laravel-filamentphp`

**Status:** ✅ Complete

**Purpose:** Reference Filament admin panel implementation

**Features:**
- Admin panel with resources
- Widgets and dashboard customization
- Best practices for Filament v5

---

#### `user-guest-dashboard`

**Status:** ✅ Complete

**Stack:** Laravel 13 + Filament v5

**Features:**
- Guest-only Filament panel (`/guest`)
- Continue-as-guest authentication flow
- Session-based guest tracking (UUID)
- DemoItem CRUD scoped per guest
- Dashboard stat widget
- No registration/email login in v1

**Architecture:**
- `Guest` Eloquent model with `guest` session guard
- Custom `GuestLogin` page extends Filament login
- Row-level isolation via `guest_id` FK

**Setup:**
```bash
cd examples/user-guest-dashboard
composer install
touch database/database.sqlite
php artisan key:generate
php artisan migrate
npm install && npm run build
php artisan serve
```

---

#### `my-sg-laravel`

**Status:** ✅ Complete

**Stack:** Laravel 13 + Breeze (Blade)

**Features:**
- Singapore-oriented Laravel app
- Bilingual: English + Simplified Chinese (`en`, `zh_CN`)
- Session-based locale switching
- SGD currency formatting demo
- Dismissible cookie consent banner
- PDPA privacy placeholder page
- Laravel development services marketing page

**Architecture:**
- `SetLocale` middleware reads `session('locale')`
- Translation JSON files (`lang/en.json`, `lang/zh_CN.json`)
- Marketing layout with locale switcher
- Cookie consent via POST + cookie storage

**Setup:**
```bash
cd examples/my-sg-laravel
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install && npm run build
php artisan serve
```

**Routes:**
- `/` — Home with SGD demo
- `/services/laravel` — Service narrative
- `/privacy` — PDPA placeholder
- `/locale/{locale}` — Switch language

---

#### `invoice-app`

**Status:** ✅ Complete

**Stack:** Laravel 13 + Breeze + DomPDF

**Features:**
- Customer management (CRUD)
- Invoice management with line items
- Nested invoice items (quantity × unit_price)
- Computed totals (model attributes)
- PDF export via DomPDF
- Auth via Breeze (Blade)
- SQLite storage

**Models:**
- `Customer` (name, email, address)
- `Invoice` (customer_id, number, issued_on, due_on, status)
- `InvoiceItem` (invoice_id, description, quantity, unit_price)

**Setup:**
```bash
cd examples/invoice-app
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install && npm run build
php artisan serve
```

**Routes:**
- `/customers` — Customer CRUD
- `/invoices` — Invoice CRUD
- `/invoices/{invoice}/pdf` — PDF download

---

#### Other Examples

| Example | Status | Purpose |
|---------|--------|---------|
| `jwt` | ✅ Complete | JWT authentication API |
| `passport` | ✅ Complete | Laravel Passport OAuth2 |
| `laravel-mailtrap` | ✅ Complete | Mailtrap email integration |
| `myapp` | ✅ Complete | Basic Laravel app |
| `myappadmin` | ✅ Complete | Admin panel example |
| `vitou-test-livewire-auth` | ✅ Complete | Livewire + Breeze auth |

---

## Documentation (`docs/`)

### Superpowers Plans

Detailed implementation plans for completed features:

| Plan | Date | Status |
|------|------|--------|
| `2026-05-09-user-guest-dashboard.md` | 2026-05-09 | ✅ Implemented |
| `2026-05-09-welcome-filament-cta.md` | 2026-05-09 | ✅ Implemented |
| `2026-05-10-my-sg-laravel.md` | 2026-05-10 | ✅ Implemented |
| `2026-05-18-invoice-app.md` | 2026-05-18 | ✅ Implemented |

### Agency Agents

Collection of specialized AI agent prompts for various domains:
- Academic, Design, Engineering, Finance
- Game Development, Marketing, Product
- Project Management, Sales, Support
- Testing, Strategy, Spatial Computing

---

## Tech Stack Summary

### Core Framework
- **PHP:** 8.3+
- **Laravel:** 13.x (latest: 13.6.0)
- **Database:** SQLite (dev), MySQL (prod)

### Frontend
- **Vite:** 8.x
- **Tailwind CSS:** v4
- **Livewire:** Latest (where used)

### Admin Panels
- **Filament:** v5 (multiple apps)
- **Laravel Breeze:** Blade stack (auth scaffolding)

### Authentication
- **Laravel Passport:** OAuth2 server
- **JWT:** Token-based auth
- **Breeze:** Session-based auth

### Additional Packages
- **DomPDF:** PDF generation (`barryvdh/laravel-dompdf`)
- **Laravel Pint:** Code style fixer
- **PHPUnit:** Testing framework

---

## Development Workflow

### Monorepo Structure

```
laravel13.x/
├── app/                    # Root app code
├── apps/                   # Runnable apps (independent)
│   ├── app-01/ to app-10/ # Reserved slots
│   ├── app-11-basic-filament/
│   └── app-12-post-saas/
├── examples/               # Reference implementations
│   ├── basic-laravel-filamentphp/
│   ├── user-guest-dashboard/
│   ├── my-sg-laravel/
│   ├── invoice-app/
│   └── [other examples]/
├── docs/                   # Documentation
│   ├── superpowers/plans/ # Implementation plans
│   └── agency-agents/     # AI agent prompts
└── [standard Laravel dirs]
```

### Each App is Standalone

- Own `composer.json` and `vendor/`
- Own `.env` and database
- Own `node_modules/` and build process
- Independent testing suite

### Root App Conventions

- API versioning: `/api/v1/`
- JSON:API response format
- FormRequest validation
- API Resources for responses
- Rate limiting on write operations
- Eloquent query scopes for filtering

---

## Testing

### Root App
```bash
php artisan test
```

### Individual Apps
```bash
cd apps/app-11-basic-filament
php artisan test
```

### Individual Examples
```bash
cd examples/invoice-app
php artisan test
```

---

## Future Roadmap

### Planned Features

#### Root App
- [ ] User authentication UI (Breeze/Filament)
- [ ] Todo API documentation (OpenAPI/Swagger)
- [ ] Rate limiting dashboard
- [ ] API analytics

#### Apps Directory
- [ ] `app-01` to `app-10` — Available for new projects
- [ ] Multi-tenant SaaS starter
- [ ] E-commerce starter
- [ ] CMS starter

#### Examples
- [ ] Real-time chat example (Reverb)
- [ ] Payment integration (Stripe/PayPal)
- [ ] File upload/storage example
- [ ] Queue/job processing example
- [ ] API rate limiting example
- [ ] Multi-database example

### Infrastructure
- [ ] Docker Compose setup
- [ ] CI/CD pipeline (GitHub Actions)
- [ ] Deployment guides (AWS, DigitalOcean, Laravel Forge)
- [ ] Performance benchmarks
- [ ] Security audit checklist

---

## Contributing

### Adding New Apps

1. Create under `apps/app-XX/`
2. Use standalone Laravel skeleton
3. Document setup in app's README.md
4. Add entry to this roadmap
5. Include tests

### Adding New Examples

1. Create under `examples/your-example/`
2. Focus on single concept/pattern
3. Include comprehensive README.md
4. Write feature tests
5. Update this roadmap

### Code Standards

- Follow PSR-12 via Laravel Pint
- Write tests for new features
- Document public APIs
- Use type hints (PHP 8.3+)
- Follow Laravel conventions

---

## Resources

### Documentation
- [Laravel 13 Docs](https://laravel.com/docs/13.x)
- [Filament v5 Docs](https://filamentphp.com/docs)
- [Tailwind v4 Docs](https://tailwindcss.com/docs)

### Learning
- [Laravel Bootcamp](https://bootcamp.laravel.com)
- [Laravel Learn](https://laravel.com/learn)
- [Laracasts](https://laracasts.com)

### Tools
- [Laravel Boost](https://laravel.com/docs/ai) — AI coding agent tools
- [Laravel Herd](https://herd.laravel.com) — Local development
- [Laravel Forge](https://forge.laravel.com) — Deployment

---

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for Laravel framework version history.

---

**Questions?** Check individual app/example README files for specific setup instructions.
