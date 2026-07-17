---
name: Analytics Dashboard
description: Calm Filament admin and Breeze storefront with restrained amber accents
colors:
  primary-500: "oklch(0.769 0.188 70.08)"
  primary-600: "oklch(0.666 0.179 58.318)"
  primary-400: "oklch(0.828 0.189 84.429)"
  amber-tint-10: "oklch(0.987 0.022 95.277)"
  login-surface-light: "oklch(0.97 0.008 75)"
  login-surface-dark: "oklch(0.16 0.012 75)"
  gray-50: "oklch(0.985 0 0)"
  gray-100: "oklch(0.967 0.001 286.375)"
  gray-200: "oklch(0.92 0.004 286.32)"
  gray-500: "oklch(0.552 0.016 285.938)"
  gray-800: "oklch(0.274 0.006 286.033)"
  gray-900: "oklch(0.21 0.006 285.885)"
  gray-950: "oklch(0.141 0.005 285.823)"
  success-500: "oklch(0.723 0.219 149.579)"
  warning-500: "oklch(0.769 0.188 70.08)"
  danger-500: "oklch(0.637 0.237 25.331)"
  info-500: "oklch(0.623 0.214 259.815)"
typography:
  display:
    fontFamily: "\"Inter Variable\", ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.875rem"
    fontWeight: 600
    lineHeight: 1.25
    letterSpacing: "-0.01em"
  headline:
    fontFamily: "\"Inter Variable\", ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.25rem"
    fontWeight: 600
    lineHeight: 1.3
  title:
    fontFamily: "\"Inter Variable\", ui-sans-serif, system-ui, sans-serif"
    fontSize: "1rem"
    fontWeight: 600
    lineHeight: 1.4
  body:
    fontFamily: "\"Inter Variable\", ui-sans-serif, system-ui, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.5
  label:
    fontFamily: "\"Inter Variable\", ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.625rem"
    fontWeight: 600
    lineHeight: 1.2
    letterSpacing: "0.06em"
  body-storefront:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.5
rounded:
  sm: "0.375rem"
  md: "0.5rem"
  lg: "0.75rem"
  xl: "0.75rem"
spacing:
  xs: "0.25rem"
  sm: "0.5rem"
  md: "1rem"
  lg: "1.5rem"
  xl: "2rem"
  page: "2rem"
components:
  button-primary:
    backgroundColor: "{colors.primary-600}"
    textColor: "oklch(0.985 0 0)"
    rounded: "{rounded.md}"
    padding: "0.5rem 1rem"
  button-primary-hover:
    backgroundColor: "{colors.primary-500}"
    textColor: "oklch(0.985 0 0)"
    rounded: "{rounded.md}"
    padding: "0.5rem 1rem"
  button-secondary:
    backgroundColor: "{colors.gray-100}"
    textColor: "{colors.gray-900}"
    rounded: "{rounded.md}"
    padding: "0.5rem 1rem"
  input-default:
    backgroundColor: "oklch(0.985 0 0)"
    textColor: "{colors.gray-950}"
    rounded: "{rounded.md}"
    padding: "0.5rem 0.75rem"
  card-surface:
    backgroundColor: "oklch(0.985 0 0)"
    textColor: "{colors.gray-900}"
    rounded: "{rounded.lg}"
    padding: "1.5rem"
---

# Design System: Analytics Dashboard

## 1. Overview

**Creative North Star: "The Operations Desk"**

This system serves operators and developers who manage catalog, sales, access, and local dev tooling across long sessions. The Filament admin at `/admin` is the canonical surface: familiar Linear/Stripe density, predictable sidebar groups, and amber reserved for action and selection. The Breeze storefront and analytics dashboard share the same Laravel app but use a lighter Figtree stack with gray neutrals and selective amber on auth and brand marks.

The aesthetic is calm, capable, and operational. Surfaces stay flat at rest; depth appears only on login cards, stat tiles on hover, and modal overlays. Dark mode is first-class on both Filament and Breeze (`class` strategy, light/dark/system toggle on guest and app layouts).

**Key Characteristics:**

- Restrained amber accent on primary actions, active nav, and brand icon only
- Filament v5 defaults plus `admin-polish` render hook for login backdrop and table/nav typography
- Two registers in one repo: product admin (Inter, Filament tokens) and storefront (Figtree, Tailwind gray scale)
- Semantic colors (success, warning, danger, info) for status badges and CommerceOverview stats, never as decoration
- Motion limited to 150 to 250 ms state feedback; no decorative page-load choreography
- Navigation groups fixed: Catalog, Sales, Development, Access

## 2. Colors

Warm-tinted neutrals with a single amber primary. Filament maps `Color::Amber` to `--primary-*`; Breeze auth uses Tailwind `amber-*` on links and focus rings.

### Primary

- **Signal Amber** (oklch(0.769 0.188 70.08) / `--primary-500`): Filament primary buttons, active sidebar items, links in Playground quick links, CommerceOverview "Customers" stat accent. The only hue that reads as "go" or "selected."
- **Pressed Amber** (oklch(0.666 0.179 58.318) / `--primary-600`): Default filled button background in admin; hover steps up to 500.
- **Whisper Amber** (oklch(0.97 0.008 75)): Login simple-layout background (light). Radial wash at oklch(0.92 0.04 75 / 0.35) from top center.
- **Brand Mark Tint** (`bg-amber-500/10`, `text-amber-600`): Application brand icon wrapper on nav and guest layouts.

### Secondary

Omitted. No second accent hue. Info blue appears only as Filament semantic `info` on the Products stat, not as a brand color.

### Tertiary

Omitted.

### Neutral

- **Canvas Light** (oklch(0.985 0 0) / `gray-50`): Filament content surfaces, Breeze cards (`bg-white`).
- **Canvas Muted** (oklch(0.967 0.001 286.375) / `gray-100`): App shell background (`bg-gray-100`), theme toggle track.
- **Border Hairline** (oklch(0.92 0.004 286.32) / `gray-200`): Table dividers, card rings (`ring-gray-900/5`).
- **Secondary Text** (oklch(0.552 0.016 285.938) / `gray-500`): KPI labels, table header caps, form hints.
- **Surface Dark** (oklch(0.274 0.006 286.033) / `gray-800`): Breeze nav bar, guest card in dark mode.
- **Ink Dark** (oklch(0.141 0.005 285.823) / `gray-950`): Filament body text in dark mode, primary reading color.

### Semantic (Filament)

- **Success** (`--success-500`): Paid revenue stat, success badges (tunnel enabled, HMR active).
- **Warning** (`--warning-500`, same ramp as primary): Pending orders callout on CommerceOverview.
- **Danger** (`--danger-500`): Destructive header actions (e.g. Delete user).
- **Info** (`--info-500`): Products stat, informational badges.

### Named Rules

**The Amber Sparingly Rule.** Primary amber appears on buttons, active navigation, primary-tinted stats, and auth links only. It must not flood backgrounds, table rows, or chart fills.

**The Tinted Neutral Rule.** Neutrals carry a slight cool gray bias in Filament (`286` hue) and warm page wash on login (`75` hue). Never use pure `#000` or `#fff`.

## 3. Typography

**Display Font:** Inter Variable (with ui-sans-serif, system-ui fallbacks) on Filament admin
**Body Font:** Inter Variable on admin; Figtree (400, 500, 600 from Bunny Fonts) on Breeze storefront and auth
**Label/Mono Font:** Inter for UI labels; `font-mono` for env values in Playground and tunnel domains

**Character:** Tight, operational sans throughout admin. Storefront headings use Figtree semibold with `tracking-tight` on page titles. No display serifs anywhere in product UI.

### Hierarchy

- **Display** (600, 1.875rem / 30px, 1.25): Filament page headings (`fi-header-heading`), dashboard page h2 (`text-xl font-semibold`).
- **Headline** (600, 1.25rem / 20px, 1.3): Section headings (`text-lg font-semibold`), CommerceOverview widget title.
- **Title** (600, 1rem, 1.4): Card titles, stat values (`text-3xl font-semibold` on public KPI tiles is the one allowed metric emphasis).
- **Body** (400, 1rem, 1.5): Form copy, table cells, descriptions. Prose blocks cap at 65 to 75ch where used.
- **Label** (600, 0.625rem, uppercase 0.06em tracking): Filament sidebar group labels (`.fi-sidebar-nav-group-label`), table header cells (`.fi-ta-header-cell-label`), Breeze table `text-xs uppercase tracking-wider`.

### Named Rules

**The No Display in Data Rule.** Display-sized type is forbidden on table headers, form labels, and metric labels. PRODUCT.md anti-reference: no display fonts in tables or form labels.

**The Fixed Scale Rule.** Product UI uses fixed rem sizes, not fluid clamp headings. Sidebar and dense tables depend on predictable line heights.

## 4. Elevation

Hybrid: mostly flat tonal layering with selective shadows. Filament sections and tables rely on background contrast and borders. The `admin-polish` hook adds depth only where it aids focus.

### Shadow Vocabulary

- **Login card lift** (`0 1px 2px oklch(0.2 0.02 75 / 0.06), 0 12px 40px oklch(0.2 0.02 75 / 0.08)`): `.fi-simple-main` on admin login.
- **Stat hover** (`0 4px 16px oklch(0.2 0.02 75 / 0.08)`): CommerceOverview stats on hover, 180ms ease.
- **Breeze card** (`shadow-sm`, `shadow-md` on guest panel): Public dashboard and auth card; `ring-1 ring-gray-900/5` in light, `ring-white/10` in dark.
- **Theme toggle segment** (`shadow-sm` on active pill): Segmented control elevation inside nav.

### Named Rules

**The Flat-By-Default Rule.** Surfaces are flat at rest. Shadows appear only as a response to hover, login focus, or modal overlay. No ambient drop shadows on every card in admin lists.

**The No Glass Rule.** Login uses a soft radial wash, not backdrop blur or glassmorphism. Forbidden as default treatment.

## 5. Components

Filament owns admin primitives; Breeze components cover storefront auth and dashboard. Shapes stay medium-radius; density follows Filament defaults.

### Buttons

- **Shape:** Medium radius (0.5rem / `rounded-md` on Breeze, Filament `fi-size-md`).
- **Primary:** Amber 600 background, white text, Filament `fi-btn` on admin. Breeze `x-primary-button` is still gray-800 light / indigo-500 dark (legacy Breeze; auth links use amber instead).
- **Hover / Focus:** Primary hovers to amber 500; focus ring 2px with offset (amber on auth, indigo on some legacy Breeze controls).
- **Secondary / Ghost:** Gray filled or ring-bordered (`ring-1 ring-gray-200`) for notification enable and outline actions.
- **Destructive:** Filament danger palette (`fi-color-danger`), never amber.

### Chips

- **Style:** Filament badges (`x-filament::badge`) with semantic colors: success, warning, gray, info.
- **State:** Static; no filter-pill pattern on admin. Status text in tables uses plain cells, not chips.

### Cards / Containers

- **Corner Style:** Large radius on Breeze (`sm:rounded-lg`, guest `sm:rounded-xl`); Filament sections use default panel radius.
- **Background:** White / gray-800 (Breeze); Filament `gray-50` / dark gray-950 content areas.
- **Shadow Strategy:** See Elevation; hover lift only on stats overview widgets.
- **Border:** `border-gray-200 dark:border-white/10` on Playground quick-link tiles; Filament tables use row dividers.
- **Internal Padding:** 1.5rem (`p-6`) on public KPI and chart cards; Filament section default padding.

### Inputs / Fields

- **Style:** Filament `fi-input` with neutral border; Breeze `rounded-md shadow-sm border-gray-300`.
- **Focus:** Auth password field uses `focus:border-amber-500 focus:ring-amber-500`; default `x-text-input` still indigo (gap to align).
- **Error / Disabled:** Laravel `x-input-error` below fields; submit disabled via Alpine until email and password filled on login.

### Navigation

- **Admin:** Filament sidebar with groups Catalog, Sales, Development, Access. Active item `color: primary`; inactive `gray`. Top bar on mobile. Breadcrumbs on edit pages.
- **Storefront:** White/dark gray-800 top bar, bottom border, `x-nav-link` with indigo active border (legacy). Brand uses amber icon tile.
- **Mobile:** Filament responsive sidebar; Breeze hamburger with stacked links.

### CommerceOverview (signature widget)

- **Character:** Operational snapshot, not a hero metric billboard.
- **Layout:** Four `StatsOverviewWidget` stats in a row; heading "Commerce snapshot", description with live DB context.
- **Colors:** success (revenue), warning/gray (orders today), primary (customers), info (products).
- **Motion:** Subtle shadow transition on hover per `admin-polish`.

### Admin login (signature surface)

- **Character:** Simple layout with warm wash and lifted card.
- **Copy:** Heading "Admin sign in"; subheading lists catalog, orders, tunnels, access.
- **Background:** `admin-polish` OKLCH gradients for light and dark simple layouts.

### Public dashboard charts

- **Note:** Chart.js revenue line still uses `rgb(37, 99, 235)` (blue); status doughnut uses green, amber, rose. Documented as a known drift from amber primary (see gaps).

## 6. Do's and Don'ts

Concrete guardrails aligned with PRODUCT.md anti-references and impeccable product register.

### Do:

- **Do** use Filament `Color::Amber` as the sole admin primary via `AdminPanelProvider`.
- **Do** register `admin-polish` on `PanelsRenderHook::STYLES_AFTER` for login backdrop, nav group labels, table headers, and stat hover.
- **Do** keep navigation groups as Catalog, Sales, Development, Access with Development holding tunnel admin and playground only.
- **Do** support dark mode on admin and Breeze with `theme-init` and segmented light/dark/auto toggle.
- **Do** write empty states and widget descriptions that say what to do next (direct tone from PRODUCT.md).
- **Do** use semantic Filament colors for status in CommerceOverview, not decorative gradients.

### Don't:

- **Don't** ship hero metrics with gradient accents and decorative motion (PRODUCT.md anti-reference).
- **Don't** use side-stripe card accents (`border-left` greater than 1px as colored emphasis).
- **Don't** put display fonts or oversized display type in tables or form labels.
- **Don't** reinvent nav patterns; use Filament sidebar and standard Breeze top nav.
- **Don't** use glassmorphism, gradient text, or neon accents as default treatments.
- **Don't** flood screens with amber; if more than roughly 10% of a viewport reads as accent, pull back.
- **Don't** add orchestrated page-load animation sequences; transitions stay 150 to 250 ms and state-driven only.
