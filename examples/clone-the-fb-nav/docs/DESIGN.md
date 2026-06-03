# Clone the FB Nav — Design Guardrails

> Educational UI study. Not affiliated with Meta. Do not ship Meta trademarks in production without legal review.

## Scene sentence

Desktop user in dark mode expects a fixed 56px top bar: logo + search left, five equal center tabs with one blue active state, utility circles right.

## Color strategy

| Token | Hex | Usage |
|-------|-----|--------|
| `fb-nav` | `#242526` | Bar background |
| `fb-blue` | `#1877F2` | Active tab icon + underline |
| `fb-icon-btn` | `#3A3B3C` | Search / menu / messenger / bell circles |
| `fb-icon` | `#E4E6EB` | Inactive outline icons |
| `fb-page` | `#18191A` | Page background below nav |

## Layout

- Full-width bar, inner `max-w-[1260px]` three-column grid: `auto 1fr auto`
- Center tabs: equal flex, min touch target 48px
- Active indicator: absolutely positioned 3px bar at bottom of center column item

## Components

| Component | Path |
|-----------|------|
| Top nav | `resources/views/components/fb-top-nav.blade.php` |
| Layout | `resources/views/layouts/fb.blade.php` |

## Motion

- Hover: subtle `bg-white/10` on icon buttons only
- No bounce; `transition-colors` ≤ 150ms

## Absolute bans

- Light-theme default (reference is dark)
- Nested card chrome inside the nav bar
- Client-only active tab without route sync

## AI prompt footer

Follow this DESIGN.md. Scope: top nav only. Do not add auth or API integrations. Run `php artisan test` after edits.
