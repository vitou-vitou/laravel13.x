# UI resources — **AI picks for you**

You do **not** need to browse Dribbble or GitHub lists manually.  
**You say:** project path + app type. **Agent picks:** references, component kit, photos, writes `DESIGN.md`, then polishes UI.

**Also read:** [`FRONTEND_REAL_WORLD_GATE.md`](FRONTEND_REAL_WORLD_GATE.md) · [`ui-adoption-workflow/`](guides/ui-adoption-workflow/README.md) · Agent skill **laravel-ui-phase** (`.cursor/skills/laravel-ui-phase/` or `.agents/skills/laravel-ui-phase/`)

---

## One prompt (copy this only)

```markdown
AI pick my UI — you choose all references; I don't know UX/UI.

Project path: [examples/marketplace-v2 | D:\phillipinsurancekh\...]
App type: [multi-vendor marketplace | admin dashboard | landing | insurance portal | Next.js app | other one line]
Screens to polish: [catalog + PDP | whole storefront | one page name]
Speed: [2 product UI — default | 3 brand polish]

Follow docs/GITHUB_UI_RESOURCE_INDEX.md § Agent auto-pick.

Do:
1. Pick inspiration + component kit + stock photo source (document choices in DESIGN.md with URLs)
2. Write examples/.../docs/DESIGN.md (tokens, typography, cards, do-not-copy-checkout rules)
3. Use impeccable + design-taste-frontend — audit existing pages first
4. Implement polish — same routes, same business logic
5. Run project test command; report before/after in 3 bullets

I will not paste Dribbble links — you find suitable public references.
```

---

## Agent auto-pick (rules for the AI)

When the user does **not** supply references, the agent **must** choose and **document** in `DESIGN.md`:

### Step 1 — Classify app

| App type | Default visual direction |
|----------|---------------------------|
| Multi-vendor marketplace | Clean commerce: strong product grid, trust cues, not gray Breeze |
| Single-vendor e-commerce | Catalog + cart like modern DTC shop |
| Admin / Filament | Keep Filament; polish customer-facing only unless asked |
| Insurance / B2B portal | Calm, trustworthy, dense forms OK, clear steps |
| Landing / marketing | One hero, one CTA, editorial type |
| Next.js / React SPA | shadcn-style patterns or project’s existing component lib |

### Step 2 — Pick kit (match stack)

| Stack in repo | Agent picks (default) |
|---------------|------------------------|
| Laravel + Tailwind (Breeze) | **daisyUI** or **Flowbite** patterns ported to Blade — not raw gray cards |
| Laravel + Filament | Filament theme tokens + polished Blade/Livewire **storefront** pages |
| Vue in Laravel | Flowbite Vue or existing project components |
| Next.js + Tailwind | **shadcn/ui** patterns if compatible; else Flowbite |
| Unknown | Read `package.json` / `composer.json` first |

### Step 3 — Pick inspiration (agent searches, user does not)

Use **public browse** (web search or fetch), prefer:

| Need | Where agent looks |
|------|-------------------|
| Marketplace / shop layout | [SaaS Landing Page](https://saaslandingpage.com/) · [Land-book](https://land-book.com/) · “multi vendor marketplace UI” |
| Product grid mood | [Dribbble](https://dribbble.com/search/marketplace) search terms: `marketplace`, `ecommerce grid`, `product catalog` |
| Trust / checkout feel | Real sites (Etsy/Shopify **patterns only** — do not clone branding) |
| **Mobile app UX** (Play/App Store apps) | [Mobbin](https://mobbin.com/) · [Page Flows](https://pageflows.com/) · [Screenlane](https://screenlane.com/) · Chinese apps: [UI Notes](https://www.uinotes.com/) — see `examples/marketplace-v2/docs/DESIGN.md` § Mobile app UX |
| DESIGN.md template | [VoltAgent/awesome-design-md](https://github.com/VoltAgent/awesome-design-md) — pick closest **e-commerce / SaaS** file |
| Deep link lists | [bradtraversy/design-resources-for-developers](https://github.com/bradtraversy/design-resources-for-developers) |

**Record in DESIGN.md:** 2–3 inspiration URLs + what was borrowed (spacing, card, nav — not logos).

### Step 4 — Stock images

| Use | Source |
|-----|--------|
| Product placeholders | [Unsplash](https://unsplash.com/) (search terms in DESIGN.md) |
| Avatars / empty states | [undraw.co](https://undraw.co/) or [humaaans](https://www.humaaans.com/) |

**Avoid:** random Google images; Pexels if Unsplash blocked (per agency runbook).

### Step 5 — Anti-patterns (always)

From [`ui-adoption-workflow`](guides/ui-adoption-workflow/PRO-DEV-RUNBOOK.md):

- No full Dribbble pixel clone on **checkout / payment**
- No new fonts every session — pick **one** pair in DESIGN.md
- No skill roulette — this session: `impeccable` + `design-taste-frontend` only
- Tests / webhooks / auth logic **unchanged**

---

## Default pick: `marketplace-v2`

If user says marketplace and path is `examples/marketplace-v2`:

| Choice | Agent default |
|--------|----------------|
| Kit | daisyUI-style cards + Tailwind (Blade, no new npm major unless approved) |
| Inspiration | SaaS/marketplace catalog patterns; Dribbble search “ecommerce product grid” |
| Photos | Unsplash: `product`, `minimal product photography` |
| Pages first | `catalog/index`, product detail, cart/checkout **shell** only |
| Proof | `php artisan test` green + note visual changes in `docs/NEXT_SESSION.md` |

---

## GitHub `.md` lists (agent uses internally — you skip)

| List | When agent opens it |
|------|-------------------|
| [bradtraversy/design-resources-for-developers](https://github.com/bradtraversy/design-resources-for-developers) | Icons, fonts, Tailwind blocks, Dribbble section |
| [gztchan/awesome-design](https://github.com/gztchan/awesome-design) | Extra UX tools |
| [VoltAgent/awesome-design-md](https://github.com/VoltAgent/awesome-design-md) | Starting DESIGN.md |
| [EvanLi Top-100-stars](https://github.com/EvanLi/Github-Ranking/blob/master/Top100/Top-100-stars.md) | Discover high-star UI repos (e.g. shadcn) |

---

## Speed tiers (agent sets expectations)

| Tier | What user gets | Time |
|------|----------------|------|
| **1** | Fix worst page only | 1 session |
| **2** | DESIGN.md + catalog + PDP (default) | 1–2 sessions |
| **3** | Full storefront + mobile pass | Multi-session OpenSpec |

Functional MVP (tests only) is **separate** — see [`FRONTEND_REAL_WORLD_GATE.md`](FRONTEND_REAL_WORLD_GATE.md).

---

## Refresh

Agent may re-check [gitstar-ranking.com](https://gitstar-ranking.com/) or Top-100-stars for new UI repos; update **§ Default pick** if a better default kit appears. User does not need to run this.
