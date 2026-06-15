---
name: Laravel 13 Monorepo
description: Warm dark welcome surface before Filament admin; OKLCH tokens, Outfit type, restrained motion.
colors:
  welcome-bg: "oklch(0.135 0.009 70)"
  welcome-elevated: "oklch(0.19 0.009 68)"
  welcome-fg: "oklch(0.97 0.006 75)"
  welcome-body: "oklch(0.93 0.01 78)"
  welcome-muted: "oklch(0.72 0.014 78)"
  welcome-subtle: "oklch(0.58 0.012 75)"
  welcome-accent: "oklch(0.74 0.065 82)"
  welcome-accent-hover: "oklch(0.8 0.072 82)"
  welcome-border: "oklch(0.33 0.014 68)"
  welcome-footer: "oklch(0.48 0.012 72)"
  welcome-divider: "oklch(0.38 0.012 70)"
  welcome-selection: "oklch(0.74 0.065 82 / 0.22)"
typography:
  display:
    fontFamily: "'Outfit', ui-sans-serif, system-ui, sans-serif"
    fontSize: "clamp(2.25rem, 5vw, 3.5rem)"
    fontWeight: 600
    lineHeight: 1.05
    letterSpacing: "-0.038em"
  body:
    fontFamily: "'Outfit', ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 400
    lineHeight: 1.65
  label:
    fontFamily: "'Outfit', ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.8125rem"
    fontWeight: 500
    lineHeight: 1.4
    letterSpacing: "0.2em"
  sans-fallback:
    fontFamily: "'Instrument Sans', ui-sans-serif, system-ui, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.5
rounded:
  md: "0.375rem"
spacing:
  page-x-sm: "1.5rem"
  page-x-md: "2.5rem"
  page-x-lg: "4rem"
  page-y-hero: "4.25rem"
  cta-x: "1.5rem"
  cta-y: "0.75rem"
components:
  button-primary:
    backgroundColor: "{colors.welcome-accent}"
    textColor: "{colors.welcome-bg}"
    rounded: "{rounded.md}"
    padding: "12px 24px"
    typography: "{typography.label}"
  button-primary-hover:
    backgroundColor: "{colors.welcome-accent-hover}"
    textColor: "{colors.welcome-bg}"
    rounded: "{rounded.md}"
    padding: "12px 24px"
  link-accent:
    textColor: "{colors.welcome-accent}"
    typography: "{typography.body}"
  link-accent-hover:
    textColor: "{colors.welcome-accent-hover}"
    typography: "{typography.body}"
---

# Design System: Laravel 13 Monorepo

## 1. Overview

**Creative North Star: "The Filament Threshold"**

A developer opens the repo at night, laptop glow on a desk, scanning whether this skeleton is worth wiring up. The welcome surface is a single calm gate: warm charcoal ground, wheat-gold accent only where action lives, type that reads fast without shouting. Depth comes from tonal steps and a whisper of film grain, not cards or drop shadows. Motion is a short staggered fade-up, then stillness.

This system explicitly rejects SaaS landing clichés (hero metrics, icon-card grids, gradient text, glass panels, purple-on-black defaults). Filament apps under `apps/` may use their own UI; this document governs the **root welcome landing** and shared `@theme` tokens in `resources/css/app.css`.

**Key Characteristics:**

- OKLCH palette with low chroma neutrals and one warm accent (hue ~70–82)
- Dark-by-scene: dim ambient use, not "dark mode because dev tools"
- Flat elevation; borders and lightness steps define structure
- Outfit for welcome; Instrument Sans reserved as default sans stack
- Motion: state and entrance only; `cubic-bezier(0.16, 1, 0.3, 1)` ease-out; respects `motion-reduce`
- Max content width ~42rem; generous vertical rhythm on hero and footer

## 2. Colors

Warm charcoal field with parchment text and a single wheat accent used sparingly for links, CTAs, focus rings, and text selection.

### Primary

- **Wheat Signal** (oklch(0.74 0.065 82)): Primary actions (`Open admin`), inline doc links, focus rings, selection highlight. Never fills large backgrounds.
- **Wheat Signal Bright** (oklch(0.8 0.072 82)): Hover on accent surfaces and links only.

### Neutral

- **Charcoal Base** (oklch(0.135 0.009 70)): Page background (`bg-welcome-bg`). Theme color meta `#141311` is close sRGB anchor.
- **Charcoal Lift** (oklch(0.19 0.009 68)): Skip-link and elevated focus surfaces.
- **Parchment Headline** (oklch(0.97 0.006 75)): H1 and selection text color.
- **Parchment Body** (oklch(0.93 0.01 78)): Default body text on page.
- **Ash Muted** (oklch(0.72 0.014 78)): Supporting paragraphs.
- **Ash Label** (oklch(0.58 0.012 75)): Uppercase kicker / app name line.
- **Stone Border** (oklch(0.33 0.014 68)): Footer top rule.
- **Stone Divider** (oklch(0.38 0.012 70)): Footer middot separator.
- **Stone Footer** (oklch(0.48 0.012 72)): Version line text.
- **Wheat Veil** (oklch(0.74 0.065 82 / 0.22)): Text selection background.

### Named Rules

**The One Wheat Rule.** Accent wheat appears only on interactive elements and selection, never as a full-bleed hero wash. If more than ~10% of the viewport reads as accent, remove a use.

**The Tinted Neutral Rule.** No pure `#000` or `#fff`. Every neutral carries a trace of hue 70–78 at chroma 0.009–0.014.

## 3. Typography

**Display Font:** Outfit (Bunny Fonts: 300–700)
**Body Font:** Outfit (same stack on welcome)
**Label Font:** Outfit, uppercase tracking for kickers
**Sans Fallback:** Instrument Sans (defined in `@theme`, default Tailwind `font-sans` for non-welcome surfaces)

**Character:** Geometric-humanist sans with tight display tracking; editorial calm, not startup loud.

### Hierarchy

- **Display** (600, clamp 2.25rem–3.5rem / 1.05 line-height, -0.038em tracking): Page H1 only; `text-balance`.
- **Headline** (600, 1.125rem–1.25rem implied in links): Semibold inline actions.
- **Title** (500, 0.8125rem, 0.2em letter-spacing, uppercase): App name kicker above H1.
- **Body** (400, 1.125rem / 1.65, max ~34–36rem): Lead copy; `text-pretty`.
- **Label** (500, 0.75rem / 1, medium): Footer version line; tabular nums for versions.

### Named Rules

**The 42rem Column Rule.** Main copy and footer share `max-w-[42rem]` so the eye stays in one column on large screens.

**The Outfit-Only Welcome Rule.** `welcome.blade.php` uses `font-outfit` on `body`. Do not mix Instrument Sans on that page without a deliberate exception.

## 4. Elevation

This system is **flat by default**. Depth is conveyed through background steps (`welcome-bg` → `welcome-elevated`), hairline borders (`welcome-border`), and a fixed 4% opacity fractal noise overlay (`mix-blend-soft-light`), not box shadows. No card chrome on the welcome page.

### Shadow Vocabulary

None on the canonical welcome surface. If a future component needs depth, prefer a single low-alpha ambient shadow in the sidecar, never stacked Material-style layers.

### Named Rules

**The Flat Gate Rule.** Surfaces stay flush until interaction (hover brightens accent, active `translateY(1px)` on buttons/links). Do not add drop shadows to static layout blocks.

## 5. Components

Tone: tactile but quiet; controls feel pressable without glow or glass.

### Buttons

- **Shape:** Gently rounded (6px / `rounded-md`)
- **Primary:** Wheat Signal fill, Charcoal Base text, `min-h-11`, horizontal padding 24px, semibold 14px
- **Hover / Focus:** Background → Wheat Signal Bright; `focus-visible` ring 2px accent at 50% opacity, offset 2px on Charcoal Base
- **Active:** 1px downward translate (`active:translate-y-px`)

### Links (inline)

- **Style:** Accent color, underline at 40% accent opacity, offset 0.22em
- **Hover:** Brighter accent + 60% underline opacity
- **Focus:** Same ring treatment as buttons; rounded-sm on focus-visible

### Cards / Containers

Not used on welcome. Future panels should use border + tonal step, not nested cards.

### Inputs / Fields

Not present on welcome. When added, use Stone Border stroke, Charcoal Lift fill, same focus ring as buttons; no glow.

### Navigation

Welcome has no nav bar. Skip link only: sr-only until focus, then elevated surface pill top-left.

### Film grain overlay

- **Style:** Full-viewport fixed noise SVG at 4% opacity, `pointer-events-none`, soft-light blend
- **Purpose:** Analog warmth without glassmorphism

### Staggered entrance

- **Pattern:** `welcome-in` 780ms, ease `cubic-bezier(0.16, 1, 0.3, 1)`, delays 0 / 120 / 240 / 360ms on hero, lead, CTA, footer
- **A11y:** Disabled when `motion-reduce` is set

## 6. Do's and Don'ts

Strategic line from PRODUCT.md: no SaaS landing clichés, AI-tool glass/neon, template slop, or category-reflex palettes.

### Do:

- **Do** use OKLCH tokens from `@theme` in `resources/css/app.css` for all welcome colors.
- **Do** keep accent wheat on links, buttons, focus, and selection only.
- **Do** use Outfit on the welcome page and respect `motion-reduce`.
- **Do** maintain 44px minimum touch targets (`min-h-11`) on primary controls.
- **Do** use tonal borders (`welcome-border`, `welcome-divider`) instead of shadows for separation.

### Don't:

- **Don't** use pure black or white backgrounds or text.
- **Don't** ship generic SaaS landing patterns: hero metrics, three identical icon cards, gradient headlines.
- **Don't** use "AI tool" marketing aesthetics: glassmorphism, neon accents, chat-bubble chrome, over-animated heroes.
- **Don't** leave stock Laravel/Filament gray as the only identity; unstyled admin with no intent is template slop.
- **Don't** add gradient text, glass blur cards, or colored left-border stripes on callouts.
- **Don't** deploy identical icon+heading+body card grids on the welcome surface.
- **Don't** use bounce or elastic easing; use ease-out quart/expo curves only.
- **Don't** animate layout properties (width, height, margin); opacity and transform only.
- **Don't** paste generic purple/indigo SaaS palettes or observability-dark-blue reflexes over this warm hue-70 family.
- **Don't** use modal-first flows when inline or progressive disclosure would do.
