# Marketplace v2 — storefront design

**Speed tier:** 2 (product UI)  
**Aligned with:** [`docs/FRONTEND_REAL_WORLD_GATE.md`](../../docs/FRONTEND_REAL_WORLD_GATE.md) UI phase · [`docs/GITHUB_UI_RESOURCE_INDEX.md`](../../docs/GITHUB_UI_RESOURCE_INDEX.md) agent auto-pick · roadmap frontend tasks T034–T080 (presentation only)

## Inspiration (patterns only — no brand clone)

| Source | Borrowed |
|--------|----------|
| [SaaS Landing Page](https://saaslandingpage.com/) | Clean catalog density, trust strip |
| [Land-book](https://land-book.com/) | Card grid, soft shadows |
| Dribbble “ecommerce product grid” | Image-forward cards, vendor subtitle |
| [Flowbite e-commerce](https://flowbite.com/blocks/e-commerce/) | PDP two-column, sticky buy box |
| [Flowbite admin](https://flowbite.com/blocks/application/) | Admin list panels, subnav chips |

## Tokens

| Token | Value |
|-------|--------|
| Primary | `brand-600` (`emerald`) / hover `brand-700` |
| Surface | `stone-50` page, `white` cards |
| Text | `stone-900` headings, `stone-500` muted |
| Radius | `rounded-2xl` cards, `rounded-xl` buttons |
| Font | Plus Jakarta Sans (Bunny) |
| Shadow | `shadow-sm` default, `shadow-md` on card hover |

## Blade components (reuse)

| Component | Use |
|-----------|-----|
| `x-store-page` | Page shell + title |
| `x-store-panel` / `.store-panel` | White card |
| `x-flash-status` | Session success flash |
| `x-admin-subnav` | Admin area quick links |
| `x-product-card` | Catalog grid item |
| `.btn-brand` / `.btn-brand-outline` | Primary / secondary actions |
| `.store-input` | Forms |

## Stock images

Unsplash placeholders via `Product::displayImageUrl()` until `image_path` set.

## Do not

- Change checkout / Stripe / webhook logic
- Clone Etsy/Amazon branding
- Add new npm packages for this pass

## Pages in scope (full UI pass)

**Storefront:** `/`, `/catalog`, `/products/{id}`, `/cart`  
**Account:** login/register (guest layout), `/dashboard`, `/profile`, `/orders/*`, checkout success/cancel  
**Vendor:** `/vendor/apply`, `/vendor/dashboard`  
**Post-purchase:** reviews, disputes  
**Admin:** dashboard, vendors, commission, disputes, payment audit  

**Out of scope:** `welcome.blade.php` (home redirects to catalog)
