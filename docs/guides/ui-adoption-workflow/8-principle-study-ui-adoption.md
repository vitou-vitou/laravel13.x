# Professional UI Workflow & GitHub Design Adoption

**A Structured Study Packet**  
Built with an 8-principle learning method

---

## How to use this packet

This packet teaches how to get a **professional, consistent UI** on Laravel projects (especially e-commerce) without skill roulette, Dribbble cloning, or merging whole foreign repos. It covers speed tiers, adopting design from GitHub, what top OSS projects do, and what to avoid.

**Step 1 — Understanding** (Principles 1–4): build a correct mental model.  
**Step 2 — Automaticity** (Principles 5–8): quizzes and spacing so you apply it under pressure.

### The 8 principles

| # | Principle | What you do |
|---|-----------|-------------|
| 1 | Map of the system | See how parts connect |
| 2 | Clear explanations | Learn core ideas in plain language |
| 3 | Different media | Summary, diagram, analogy, table |
| 4 | Short lessons | Bite-sized micro-lessons |
| 5 | Test yourself | Quiz + flashcards + answer key |
| 6 | Wait to review | Spaced repetition schedule |
| 7 | Mix it up | Interleaved quiz |
| 8 | Don't stop | Overlearning plan |

### Table of contents

- [Step 1 — Understanding](#step-1--understanding)
- [Step 2 — Automaticity](#step-2--automaticity)
- [Appendix — Glossary](#appendix--glossary)

---

# Step 1 — Understanding

Your goal: know *what* to copy from GitHub or AI skills, *when*, and *why* MVP UIs should stay boring before they stay flashy.

---

## Principle 1 — Map of the system

### UI workflow stages (in order)

| Stage | What happens | Typical output |
|-------|----------------|----------------|
| 1. Ship | Breeze + Tailwind defaults, clear routes | Working shop/cart/checkout |
| 2. Lock context | `PRODUCT.md` + `DESIGN.md` | Frozen constraints for AI |
| 3. Port design system | Config, fonts, components | `tailwind.config`, Blade partials |
| 4. Page-by-page polish | One surface per PR/session | Shop → cart → checkout |
| 5. Audit | a11y, mobile, tests, browser | Green tests + smoke |

### Major “inputs” (where look comes from)

| Source | You usually extract | Risk if misused |
|--------|---------------------|-----------------|
| GitHub repo (same stack) | Tailwind config, components, layout | License, version mismatch |
| GitHub repo (React/Vue) | Tokens + layout ideas only | Pasting JSX into Blade |
| UI kit (npm) | Documented components | Mixing two kits |
| Dribbble / screenshot | Mood, density, hierarchy | Full-site clone, checkout harm |
| AI skills (`impeccable`, etc.) | Polish on *your* files | Random skill = new art direction each chat |

### Map takeaway

> **Design is a system applied to your app shell, not a picture pasted on top.** Logic (routes, Stripe, cart) stays yours; look comes from tokens + reusable Blade components.

---

## Principle 2 — Clear explanations

### What is “skill roulette”?

Switching to a different AI design skill (or a new Dribbble image) every session without a shared `DESIGN.md`. Each run invents new fonts, card styles, and heroes. The site looks “AI-made” and inconsistent.

### What must you do *before* copying a GitHub repo’s design?

1. **License** — MIT/Apache OK with attribution; GPL needs care; no license = no copy.
2. **Stack fit** — Laravel Blade + Tailwind ports cleanly; React demos port as *tokens only*.
3. **Port map** — List: config → CSS → components → layout → which of *your* pages they map to.

### What are the three speed tiers?

| Tier | Goal | E-commerce use |
|------|------|----------------|
| Speed 1 — Ship | Works, trustworthy | Catalog, cart, Stripe (MVP) |
| Speed 2 — Product UI | Consistent tokens + components | All transactional pages |
| Speed 3 — Brand | Marketing flair | Landing only, not checkout |

### What do trending GitHub projects do differently from “clone repo”?

They **install or document** a design system, **separate** admin vs storefront, **version** UI in small PRs, and **keep tests green** — they rarely merge a foreign app tree into an existing MVP.

### Explanation takeaway

> **Professional e-commerce UI is mostly UX clarity and consistency, not illustration.** The common mistake is Speed 3 on checkout because a reference image looked good.

---

## Principle 3 — Different media

**One-line summary:** License and stack first, extract tokens and components second, one page per change third — never merge whole repos or clone Dribbble into checkout.

**Diagram (decision flow):**

```text
Want same design as GitHub repo?
  ├─ License OK? ──no──► stop or pick another source
  ├─ Same stack (Blade+Tailwind)? ──yes──► port config → components → pages
  └─ React/Vue? ──yes──► tokens + wireframe only; rewrite in Blade
       └─ Need full app behavior? ──► fork NEW project, don't graft onto MVP
```

**Analogy:** Copying a repo’s UI into your Laravel app is like wearing another team’s uniform to your own game — you need the same *rule book* (license, stack), not their whole roster (routes, payment logic).

**Comparison table**

| Approach | Speed | Professional result |
|----------|-------|------------------------|
| Dribbble clone in chat | Feels fast | Low — endless tweaks |
| GitHub merge folders | Fast day 1 | Low — breaks tests, dual kits |
| Token + component port | Slower start | High — maintainable |
| Official UI kit + docs | Medium | High — if one kit only |

**Media takeaway:** Match the *system*, not the screenshot.

---

## Principle 4 — Short lessons

**Lesson 1 — First hour on a new reference repo**  
Read LICENSE, `package.json`, `tailwind.config`, one layout file. Run their demo. Write a port map — do not copy `vendor/` or `node_modules`.

**Lesson 2 — Laravel e-commerce register**  
Use **product** register (shop, cart, checkout): restrained color, clear totals, trust on pay. Save **brand** register for a separate marketing page.

**Lesson 3 — One AI session = one surface**  
Example: only `shop/index` + `_product-card.blade.php`. Attach `DESIGN.md`. Say: do not change Stripe routes or checkout controllers.

**Lesson 4 — GitHub React starter**  
Steal spacing, type scale, and card structure; rewrite every component in Blade. Never paste JSX.

**Lesson 5 — When MVP is enough**  
Gray Breeze shop + flash messages + passing tests is correct Speed 1. Polish is Speed 2 as a scoped PR, not a new skill.

**Short-lessons takeaway:** Freeze context, then polish in thin slices.

---

# Step 2 — Automaticity

Understanding fades without retrieval. Use the quiz, schedule, and mixed quiz below.

---

## Principle 5 — Test yourself

### Quiz

1. What are the first three checks before porting UI from GitHub?
2. Why should checkout usually stay in Speed 1–2, not Speed 3?
3. What is a port map?
4. Blade + Tailwind repo vs Next.js repo: what do you copy in each case?
5. Name two things top OSS projects do that “clone folder” does not.
6. What is skill roulette?
7. When is Dribbble a *good* reference?
8. What must stay untouched in a “design-only” PR for Stripe checkout?
9. Why copy `tailwind.config` before random view files?
10. When should you fork the reference repo instead of grafting?

### Answer key

1. License, stack fit, port map (what to extract vs ignore).
2. Trust, clarity, and stable forms beat marketing heroes; clones add noise and break flows.
3. A table mapping *their* components/paths to *your* Blade partials and pages.
4. Blade: config + components + layout. React: tokens and layout rules only; rewrite UI in Blade.
5. Install/document design system; small PRs; tests green; separate admin/storefront.
6. New skill/image each session without shared `DESIGN.md`.
7. One dimension (e.g. grid density) with written constraints, not full-site clone.
8. Route names, form actions, CSRF, Stripe session creation, webhook handlers.
9. Tokens drive every component; views without tokens duplicate inconsistently.
10. When you need their full app architecture, not just look, on a greenfield project.

### Flashcards

| Front | Back |
|-------|------|
| First step for GitHub UI? | License + stack + port map |
| E-commerce UI priority? | UX consistency over illustration |
| React repo → Laravel? | Tokens + rewrite Blade, no JSX paste |
| Speed 3 allowed where? | Marketing landing, not checkout |
| One session scope? | One page or one component family |
| Paid design PR includes? | `npm run build` + `artisan test` + browser smoke |
| GPL UI in closed app? | Stop; understand copyleft implications |
| Anti-pattern #1? | Merge foreign `views/` over working checkout |
| `DESIGN.md` purpose? | Freeze fonts, spacing, cards for AI |
| Top repos vs clone? | System dependency + docs, not tree merge |

---

## Principle 6 — Wait to review

| Session | When | Activity | Done |
|---------|------|----------|------|
| 1 | Today | Read Principles 1–2; write port map for one real repo | ☐ |
| 2 | Day 1 | Quiz + 5 flashcards aloud | ☐ |
| 3 | Day 3 | Mixed quiz; list 5 “do not” items from memory | ☐ |
| 4 | Day 7 | Apply Speed 2 to one Blade partial with `DESIGN.md` | ☐ |
| 5 | Day 14 | Re-quiz; audit one page with accessibility mindset | ☐ |
| 6 | Day 30 | Teach the three speed tiers to someone else in 2 minutes | ☐ |

Spacing beats cramming: you are training *judgment*, not memorizing Tailwind classes.

---

## Principle 7 — Mix it up

1. GPL theme + commercial SaaS — what do you check first?
2. Hero section skill on cart page — which speed tier violation?
3. Two UI kits in one PR — name two risks.
4. Port map lists `ProductCard` → your partial — what file type in Laravel?
5. Dribbble for “checkout trust” — good or bad? Why?
6. Tests fail after CSS change — ship anyway?
7. Admin Filament + Breeze shop — same `DESIGN.md` or split?
8. `image-to-code` on catalog — register mistake?
9. Reference repo Tailwind v4, yours v3 — first action?
10. User says “clone this repo” — your first reply in one sentence?

### Mixed answer key

1. License / legal fit.
2. Speed 3 on transactional UI.
3. Conflicting tokens; huge diff.
4. `.blade.php` component/partial.
5. Bad — use trust badges, clear totals, Stripe UI.
6. No — fix or revert.
7. Split registers/themes.
8. Brand skill on product surface.
9. Plan migration; don't blind copy config.
10. “Let's confirm license and stack, then build a port map.”

---

## Principle 8 — Don't stop

**Overlearning plan (30 days):**

- Week 1: One real GitHub repo — license + port map only.
- Week 2: One Speed 2 PR on your project (tokens + one component).
- Week 3: Browser-verify shop, cart, checkout mobile.
- Week 4: Re-read `PRO-DEV-RUNBOOK.md` and drop one anti-pattern (e.g. no Dribbble in checkout chats).

**Habit to keep:** Every UI prompt starts with “Follow `DESIGN.md`; scope: [single page]; do not change [routes/payment].”

---

## Appendix — Glossary

| Term | Meaning |
|------|---------|
| Port map | Source path → your Blade/CSS target |
| Product register | UI that serves tasks (shop, admin tools) |
| Brand register | Marketing/editorial surfaces |
| Speed tier | Ship / Product UI / Brand depth |
| Token | Color, spacing, radius, type in config/CSS |
| Skill roulette | Unfrozen AI design direction each session |
