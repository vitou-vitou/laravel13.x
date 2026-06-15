# Product

## Register

product

## Users

PHP/Laravel developers (solo or small team) who scaffold, clone, and extend multiple apps from one repo. They work at a desk, often in a dim room, switching between terminal, IDE, and browser. Context: evaluating whether a skeleton is worth wiring up, bootstrapping Filament admin, or copying patterns from reference apps (`apps/app-11-basic-filament`, `apps/app-12-post-saas`, `examples/`).

## Product Purpose

A Laravel 13 monorepo for shipping several independent apps without reinventing the stack each time. The root skeleton provides Vite, Tailwind v4, and a calm welcome gate; `apps/` holds runnable Filament and SaaS-style implementations. Success means: clone or bootstrap an app quickly, trust the examples, and keep UI craft consistent (welcome + admin) without generic template aesthetics.

## Brand Personality

**Calm, capable, warm.** Expert confidence without startup hype. Copy is direct and sparse. The welcome surface feels like a workshop threshold, not a marketing funnel. Filament surfaces should feel like tools that respect focus, not dashboards shouting for attention.

**Emotional goals:** reassurance (this repo is serious), clarity (next step is obvious), quiet momentum (ready to build).

## Anti-references

- Generic SaaS landing pages: hero metrics, three identical icon cards, gradient headlines, purple-on-black defaults
- "AI tool" marketing: glassmorphism, neon accents, chat-bubble chrome, over-animated heroes
- Laravel/Filament template slop: default welcome with no intent, unstyled admin left as stock Filament gray
- Developer-tool clichés: observability-dark-blue everywhere, terminal cosplay without function
- Visual noise: colored left-border alert stripes, nested card-in-card layouts, modal-first flows for simple tasks
- Copy bloat: restated headings, filler intros, em dashes in UI strings

## Design Principles

1. **Threshold, not billboard.** The root welcome orients and exits; it does not sell. One column, one accent, one path forward (docs or admin).
2. **Practice what you ship.** Patterns in `examples/` and `apps/` should be copy-worthy; if it is not good enough to clone, it does not belong in the monorepo.
3. **Tool-first in apps.** Admin and dashboard work serves tasks (CRUD, settings, tables), not brand theater. Density and legibility beat decoration.
4. **Restraint is identity.** One warm accent on neutrals; rarity makes the accent meaningful. Avoid category-reflex palettes (purple SaaS, teal healthcare, navy finance).
5. **Accessible by default.** Visible focus, skip links, `motion-reduce` respect, 44px touch targets, contrast-safe OKLCH neutrals (see DESIGN.md for tokens).

## Accessibility & Inclusion

- Target **WCAG 2.1 AA** for text contrast and focus visibility on custom surfaces (welcome); defer to Filament/Flux defaults inside apps unless customizing.
- Honor **`prefers-reduced-motion`** on all custom animations (already on welcome).
- Do not rely on color alone for state; pair with weight, underline, or labels.
- Keyboard paths for primary actions (skip link, CTA, doc links) must remain visible on `:focus-visible`.
- No pure `#000` / `#fff`; tinted neutrals aid comfort in long sessions (aligned with DESIGN.md).
