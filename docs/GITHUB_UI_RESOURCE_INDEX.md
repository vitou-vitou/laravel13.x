# GitHub UI resource index (for real-world frontends)

You asked for **GitHub `.md` lists** (like [Top-100-stars](https://github.com/EvanLi/Github-Ranking/blob/master/Top100/Top-100-stars.md)) that point to **Dribbble, Unsplash, UI kits**, etc. — so agents stop shipping gray Breeze defaults.

**Use with:** [`FRONTEND_REAL_WORLD_GATE.md`](FRONTEND_REAL_WORLD_GATE.md) · [`guides/ui-adoption-workflow/`](guides/ui-adoption-workflow/README.md)

---

## Pick by need (start here)

| You need… | Open this GitHub README (curated `.md`) |
|-----------|----------------------------------------|
| **Everything for devs** (photos, icons, Tailwind, inspiration) | [bradtraversy/design-resources-for-developers](https://github.com/bradtraversy/design-resources-for-developers) |
| **Dribbble, Behance, UX tools** | [gztchan/awesome-design](https://github.com/gztchan/awesome-design) (also [snc/awesome-design](https://github.com/snc/awesome-design)) |
| **DESIGN.md for AI agents** (brand-like UI from one file) | [VoltAgent/awesome-design-md](https://github.com/VoltAgent/awesome-design-md) |
| **Design tools/plugins** | [goabstract/Awesome-Design-Tools](https://github.com/goabstract/Awesome-Design-Tools) |
| **Find more awesome lists** | [sindresorhus/awesome](https://github.com/sindresorhus/awesome) → search “design” |
| **Ranked repos by stars** | [EvanLi/Github-Ranking Top-100-stars.md](https://github.com/EvanLi/Github-Ranking/blob/master/Top100/Top-100-stars.md) · [gitstar-ranking.com](https://gitstar-ranking.com/) |
| **Laravel e-commerce UI process** (already in this repo) | [`docs/guides/ui-adoption-workflow/`](guides/ui-adoption-workflow/README.md) |

---

## Fast picks (from bradtraversy list)

| Type | Site | Listed in |
|------|------|-----------|
| Inspiration | [Dribbble](https://dribbble.com/) | design-resources § Design Inspiration |
| Inspiration | [Behance](https://www.behance.net/) | same |
| Inspiration | [Awwwards](https://www.awwwards.com/) | same |
| Marketplace / SaaS layouts | [SaaS Landing Page](https://saaslandingpage.com/) | same |
| Stock photos | [Unsplash](https://unsplash.com/) | design-resources § Stock Photos |
| Stock photos | [Pexels](https://www.pexels.com/) | same |
| Tailwind components | [daisyUI](https://daisyui.com/) · [Flowbite](https://flowbite.com) | design-resources § UI Components |
| React/Livewire-style kits | [shadcn/ui](https://github.com/shadcn-ui/ui) | GitHub Top 100 (~116k ★) — patterns even for Blade |

**Rule from our runbook:** Dribbble = **mood + layout rules**, not pixel-perfect checkout clone. See [`ui-adoption-workflow`](guides/ui-adoption-workflow/PRO-DEV-RUNBOOK.md).

---

## Top GitHub stars relevant to UI (EvanLi ranking)

| Repo | Why it matters |
|------|----------------|
| [shadcn-ui/ui](https://github.com/shadcn-ui/ui) | Component quality bar; copy patterns into Blade/Tailwind |
| [sindresorhus/awesome](https://github.com/sindresorhus/awesome) | Index of all awesome lists |
| [agency-agents](https://github.com/msitarzewski/agency-agents) | Includes design agents (whimsy, UI) — already mirrored under `docs/agency-agents/` |

Full table: [Top-100-stars.md](https://github.com/EvanLi/Github-Ranking/blob/master/Top100/Top-100-stars.md)

---

## Agent workflow (3 steps)

```text
1. Pick reference  →  Dribbble shot OR SaaS Landing Page OR awesome-design-md DESIGN.md
2. Write tokens    →  examples/<app>/docs/DESIGN.md  (colors, type, card style)
3. Polish UI       →  impeccable + design-taste-frontend; tests stay green
```

**Paste to agent:**

```markdown
Read docs/GITHUB_UI_RESOURCE_INDEX.md and docs/FRONTEND_REAL_WORLD_GATE.md.

Project: examples/marketplace-v2
Reference: [paste Dribbble / saaslandingpage / VoltAgent DESIGN.md URL]
Stock images: Unsplash only for product placeholders (cite search terms in commit msg).

Use impeccable + design-taste-frontend. Create docs/DESIGN.md first.
Redesign catalog + PDP only. php artisan test must stay green.
```

---

## What lives only in laravel13.x (not on GitHub)

| Doc | Purpose |
|-----|---------|
| [`ui-adoption-workflow/`](guides/ui-adoption-workflow/README.md) | License, port map, anti-patterns, PR checklist |
| [`FRONTEND_REAL_WORLD_GATE.md`](FRONTEND_REAL_WORLD_GATE.md) | Functional MVP vs PRD UI |
| [`ZERO-MISS` pocket card](ZERO-MISS-97-TASK-ROADMAP-PROMPT.md#pocket-card-remember-this) | When to run UI phase |

---

## Refresh this index

Re-run discovery occasionally:

```bash
# Top starred repos (filter design-related locally)
curl -sL "https://raw.githubusercontent.com/EvanLi/Github-Ranking/master/Top100/Top-100-stars.md" | rg -i "design|ui|tailwind|shadcn"

# Dev design mega-list (sections change — link to repo, don't copy whole file)
# https://github.com/bradtraversy/design-resources-for-developers
```

Bookmark the **bradtraversy** repo for day-to-day; bookmark **awesome-design-md** when you want agent-readable DESIGN.md templates.
