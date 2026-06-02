# UI Adoption & Design Workflow — Product Context

## register
product

## Product purpose
Enable Laravel developers (especially on Breeze + Blade + Tailwind e-commerce MVPs) to adopt external design consistently without breaking commerce flows, tests, or payment integration.

## Users
- Primary: Solo builder using Cursor + Spec-Kit/Superpowers on `examples/*` apps
- Secondary: Future contributors cloning patterns from this monorepo

## Strategic principles
1. **Commerce truth over chrome** — Cart, checkout, and order state are authoritative; UI never rewrites them in a design pass.
2. **One design system per app** — Single Tailwind config + Blade component set; one UI kit max.
3. **Thin PRs** — One page or component family per change; tests must stay green.
4. **Reference, don't clone** — GitHub/Dribbble inform tokens and patterns, not whole-repo merges.
5. **Post-MVP polish** — Speed 1 ship first; Speed 2/3 via scoped tasks (OpenSpec), not session roulette.

## Anti-references (never intentional)
- Full Dribbble clone on checkout
- React component paste into Blade
- New fonts/colors every AI session without `DESIGN.md`
- Merging foreign `routes/` or payment services for “design”
- card-in-card-in-card product grids on transactional pages
- hero-metric SaaS landing on cart page

## Success criteria
- `artisan test` passes after UI PR
- Shop, cart, checkout readable on mobile; empty/error states present
- Visual consistency across storefront pages
- LICENSE documented if external UI source used

## Out of scope
- Re-scaffolding Breeze/Sanctum/Stripe
- Replacing Blade with SPA for storefront (unless explicit new project)
