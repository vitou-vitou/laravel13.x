# UI Adoption — Design System Guardrails

> For Laravel 13 + Breeze + Blade + Tailwind 3 + Alpine (e.g. kindly-e-commerce-1122).
> Update per project when brand is chosen; do not change per AI session.

## Scene sentence
Buyer on phone in bright room, needs to see price, stock, and pay button without scrolling past decoration.

## Color strategy
**Restrained (product default):** tinted neutrals + one accent ≤10% of surface (CTA, links, sale badge).

## Theme
**Light** for storefront and checkout (trust, readability). Dark only for optional marketing landing if split layout.

## Typography
- Base: project default (e.g. Figtree via Bunny Fonts) until brand lock
- Scale: clear price > title > meta; body max ~65–75ch
- No gradient text

## Spacing & layout
- `max-w-7xl` shop shell (existing pattern)
- Product grid: consistent image ratio, aligned price baseline
- No nested card containers; one border/surface per unit

## Components (port targets)
| Component | Blade path (example) | Notes |
|-----------|----------------------|-------|
| Shop shell | `layouts/shop.blade.php` | Keep nav, flash, errors |
| Product card | `components/product-card.blade.php` | Extract before styling |
| Cart line | cart partial | Qty, remove, subtotal visible |
| Order summary | checkout partial | No marketing hero |
| Buttons | Breeze `x-primary-button` etc. | Extend, don't duplicate |

## Motion
- Minimal: hover/focus only; no layout-thrashing animation
- ease-out for transitions; no bounce on checkout

## Absolute bans (from shared laws)
- Side-stripe accent borders on cards
- Glassmorphism as default
- Identical icon+title+text card grids without hierarchy
- Modal when inline expansion works

## GitHub port order
1. `tailwind.config.js` + `resources/css/app.css`
2. Fonts / CSS variables
3. Anonymous components / partials
4. `layouts/shop.blade.php` (structure only)
5. `shop/index` → `cart` → `checkout` (never reverse order for first pass)

## AI prompt footer (paste every UI task)
Follow this DESIGN.md. Register: product. Scope: [PAGE]. Do not modify Stripe routes, webhook handlers, `CheckoutController` logic, or route names. Run tests after edits.

## External source log
| Date | Repo / kit | License | What was ported |
|------|------------|---------|-----------------|
| | | | |
