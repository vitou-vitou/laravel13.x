# Marketplace v2 — storefront design

**Speed tier:** 2 (product UI)  
**Aligned with:** [`docs/FRONTEND_REAL_WORLD_GATE.md`](../../docs/FRONTEND_REAL_WORLD_GATE.md) UI phase · [`docs/GITHUB_UI_RESOURCE_INDEX.md`](../../docs/GITHUB_UI_RESOURCE_INDEX.md) agent auto-pick · roadmap frontend tasks T034–T080 (presentation only)

## Inspiration (patterns only — no brand clone)

### Web storefront

| Source | Borrowed |
|--------|----------|
| [SaaS Landing Page](https://saaslandingpage.com/) | Clean catalog density, trust strip |
| [Land-book](https://land-book.com/) | Card grid, soft shadows |
| Dribbble “ecommerce product grid” | Image-forward cards, vendor subtitle |
| [Flowbite e-commerce](https://flowbite.com/blocks/e-commerce/) | PDP two-column, sticky buy box |
| [Flowbite admin](https://flowbite.com/blocks/application/) | Admin list panels, subnav chips |

### Mobile app UX (Play Store / App Store — patterns, not clones)

No site exposes the **full interactive** UX of every Play Store app from a listing URL (e.g. [Taobao on Play](https://play.google.com/store/search?q=taobao&c=apps) — store pages only show marketing screenshots). Use curated libraries + install-on-device when depth matters.

| Source | Best for | Notes |
|--------|----------|--------|
| [Mobbin](https://mobbin.com/) | Screens + **step-by-step flows** (onboarding, checkout, paywall) | Large library; [Taobao iOS](https://mobbin.com/search/apps?query=taobao) has homepage, social feed, etc. — partial coverage, often paid tier |
| [Page Flows](https://pageflows.com/) | **Video** recordings of real user journeys | Transitions and timing; not every app |
| [Screenlane](https://screenlane.com/) | Mobile UI by screen type / category | iOS + Android patterns |
| [Banani references](https://www.banani.co/references) | Free Mobbin-style screen browse | Smaller library |
| [UI Notes](https://www.uinotes.com/) | **Chinese apps** (Taobao-tier density, feeds, promos) | 40k+ screenshots; site in Chinese — browse visually or translate |
| [UI Pocket](https://ui-pocket.com/) | Japan-localized app UX | Useful for dense nav / localization patterns |
| [WWIT](https://wwit.design/) | Korea app UI | Clean illustrated commerce patterns |

**Workflow:** Mobbin or UI Notes for Taobao-like density → borrow layout/card/search patterns into Blade/Tailwind → document what was borrowed below. For gaps, install the app (phone or emulator) and screenshot flows yourself.

**Do not:** ship Taobao/Amazon branding, logos, or pixel-perfect clones of checkout/payment.

### Taobao-tier catalog pass (2026-06-14)

Borrowed from [Mobbin — Taobao iOS homepage / social feed](https://mobbin.com/search/apps?query=taobao) and [UI Notes](https://www.uinotes.com/) Chinese commerce feeds (visual reference only):

| Pattern | Implementation |
|---------|----------------|
| Square image tiles in 2-col feed | `aspect-square` on `x-product-card` |
| Price as primary card anchor (not overlay pill) | `.catalog-price` below title |
| Horizontal category / chip scroll on mobile | `.chip-scroll` on category filters + home categories |
| Denser mobile rhythm | `gap-2`, `p-2` card padding, `rounded-xl` feed cards |
| Compact filter bar | `.catalog-filter-panel` — smaller controls on mobile |
| Pill search bar | `rounded-full` search on catalog hero |
| Social proof hint | Vendor ★ rating on card when `rating_count > 0` |
| Feed section label | “For you” + item count above main grid |

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
| `x-sticky-cart-bar` | Mobile bottom cart summary (hidden on `/cart`) |
| `.btn-brand` / `.btn-brand-outline` | Primary / secondary actions |
| `.store-input` | Forms |

## Stock images

Unsplash placeholders via `Product::displayImageUrl()` until `image_path` set.

## Do not

- Change checkout / Stripe / webhook logic
- Clone Etsy/Amazon branding
- Add new npm packages for this pass

## Storefront polish (Phase 3)

| Pattern | Implementation |
|---------|----------------|
| Catalog filters | Category chips + `sort` + `min_price` / `max_price` (`CatalogQueryService`) |
| Mobile grid | 2-column catalog grid, denser cards, `min-h-11` touch targets on PDP/cart |
| Sticky cart | `x-sticky-cart-bar` — item count + total, mobile only |
| Home sections | Featured categories + session **recently viewed** on `/` |
| Card badges | “New” (7 days), price pill on `x-product-card` |

## Pages in scope (full UI pass)

**Storefront:** `/`, `/catalog`, `/products/{id}`, `/cart`  
**Account:** login/register (guest layout), `/dashboard`, `/profile`, `/orders/*`, checkout success/cancel  
**Vendor:** `/vendor/apply`, `/vendor/dashboard`  
**Post-purchase:** reviews, disputes  
**Admin:** dashboard, vendors, commission, disputes, payment audit  

**Out of scope:** `welcome.blade.php` (home is catalog at `/`)
