# Frontend — functional vs real-world (PRD)

**Problem you noticed:** Apps like [http://marketplace-v2.test](http://marketplace-v2.test) **work** but look like default Breeze (gray, basic cards). That is **by design** of our current MD pipeline — not a bug in the agent.

---

## Two different “done”

| | Functional MVP (what we optimize today) | Real-world / PRD UI |
|--|----------------------------------------|---------------------|
| **Goal** | Flows work, tests pass | Looks like product brief, reference site, or Figma |
| **Proof** | `php artisan test` green | Screenshot + design checklist |
| **Typical output** | Breeze layout, gray-100, simple grid | Typography, brand, imagery, empty states, mobile |
| **When** | Waves 1–10 / 97 tasks | **Separate phase** after MVP (or explicit from day 1) |

**marketplace-v2:** 38 tests, checkout/Stripe/vendor/admin **work** — `spec.md` was never filled with visual requirements; roadmap tasks say `frontend` meaning **pages exist**, not **polished shop**.

**Contrast:** `clone-the-fb-nav` has `docs/DESIGN.md` + reference PNG → closer to PRD because **design was in scope**.

---

## Why our MD builds “basic” frontends

1. **97-task roadmaps** — backend, auth, payments, tenant scope first.  
2. **Superpowers TDD** — asserts behavior, not pixels.  
3. **Spec-Kit stub** — `spec.md` often TBD; no screen list, no reference URL.  
4. **No design skill in pocket card** — agents default to Laravel Breeze/Tailwind boilerplate.  
5. **“Ship” task** — test suite green, not “matches mockup”.

---

## What to add when you want real-world UI

### 1 — Say it in the brief (before build)

Add to Project Brief or `spec.md`:

```markdown
## UI (mandatory — not default Breeze)
- Reference: [URL or attach screenshot/Figma]
- Brand: [name, primary color, font preference]
- Key screens: [catalog grid, PDP, cart, checkout — bullet each]
- Not acceptable: generic gray Laravel starter look
```

### 2 — Add `docs/DESIGN.md` in the example (like clone-the-fb-nav)

Tokens: colors, type scale, spacing, component rules. Agent reads it **before** editing Blade/Vue.

### 3 — Run a **UI phase** (after functional MVP or Wave 11)

```markdown
Functional MVP is done (tests green). Do NOT change business logic.

Project: examples/marketplace-v2
Reference: [Shopee catalog / Amazon PDP / your Figma — paste URL or path]

Use impeccable + design-taste-frontend (audit-first on existing pages).

Deliver:
1. docs/DESIGN.md (tokens)
2. Redesign catalog, PDP, cart, checkout — same routes, better UI
3. npm run build; php artisan test still green
4. Screenshot catalog + PDP in summary
```

### 4 — Optional skills (stack)

| Stack | Skills |
|-------|--------|
| Blade / Tailwind (marketplace-v2) | `impeccable`, `design-taste-frontend` |
| Landing / marketing | `epic-design`, `high-end-visual-design` |
| Image → code | `image-to-code` (provide mockup per section) |
| React / Next company app | `senior-frontend` + reference URL |

### 5 — Acceptance (not only tests)

- [ ] Matches reference or DESIGN.md  
- [ ] Mobile width usable  
- [ ] Empty/error states not raw HTML  
- [ ] Tests still green  

---

## Where this fits in pocket card

```text
Functional MVP     →  spec-kit + superpowers (today)
Real-world UI      →  OpenSpec “UI polish” OR Wave 11 + impeccable + DESIGN.md
```

Do **not** expect one 97-task pass to deliver both unless **UI is explicit** in spec and tasks (e.g. T034 = “catalog per DESIGN.md + reference”, not “catalog index paginated”).

---

## marketplace-v2 specifically

| Done | Not done |
|------|----------|
| Multi-vendor flows, Stripe, admin, disputes, GDPR API | Shop UX, product imagery, filters UI, trust badges, vendor storefront polish |

**Next step if you want it to feel real:** OpenSpec or one agent session with reference marketplace URL + `impeccable` — **no** full 97-task rewrite.

**Where to find references:** [`GITHUB_UI_RESOURCE_INDEX.md`](GITHUB_UI_RESOURCE_INDEX.md) — bradtraversy list (Dribbble, Unsplash), awesome-design-md, gitstar ranking.

---

## Company projects

Same rule: company repo can be **functionally correct** and **visually basic**. Copy **DESIGN.md + UI phase prompt** into company workflow; PRD must include **visual acceptance**, not only user stories.

See also: [pocket card](ZERO-MISS-97-TASK-ROADMAP-PROMPT.md#pocket-card-remember-this) · [company workflow](COMPANY_PROJECTS_WORKFLOW.md)
