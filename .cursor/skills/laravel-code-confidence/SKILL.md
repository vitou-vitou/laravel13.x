---
name: laravel-code-confidence
description: >-
  Multi-stack code confidence dial: official/first-party vs app code, blast-radius
  ladder, thin idioms for Laravel (Blade, Inertia, SPA, Livewire, Filament),
  Node monorepos, Keycloakify, and extensible stack packs. Use when reviewing
  slop risk, scaffolding, switching between many test stacks, asking what is
  official, or saying /laravel-code-confidence or /code-confidence.
---

# Code confidence (multi-stack)

**Hard truth:** “Official” means **first-party for the detected stack** (lockfile + upstream org/docs). Your app/domain code is never first-party — best case **idiomatic**. Chat sessions do **not** auto-improve this skill; patch files when a lesson repeats twice.

**Scope ceiling:** Packs cover **UI / delivery dialect** only. They do **not** cover the Laravel engine (Eloquent, queues, scheduler, mail, cache, broadcasting, container). Engine behaviour → [laravel.com/docs](https://laravel.com/docs) for the locked major. “Is this all of Laravel?” → **no**. Five-year admin maturity → [stacks/admin-5y-roadmap.md](stacks/admin-5y-roadmap.md) — read **only** when the ask is roadmap or coverage, never instead of the UI pack.

Do **not** run the full 5-point ladder daily. Scale by **blast radius**.

## Goals

1. **Stop architecture drift & AI slop**: Keep implementations thin, idiom-aligned, and strictly bounded to the active project stack.
2. **Clarify boundary authority**: Distinguish first-party upstream contracts (`vendor/laravel/*`, Inertia org, Filament) from app-authored domain code.
3. **Enforce single-portal UX**: Prevent competing parallel portals (dual FE+BE, second admin UIs, redundant client stores) for the same domain slice.
4. **Scale rigor by blast radius**: Default to a fast daily bar; reserve the 5-point ladder for high-risk boundaries (auth, money, PII, public APIs).

## Rules

- **Lockfile is authority**: Match the installed major version in `composer.lock` or `package.json`; reject outdated blogs and mismatched APIs.
- **Never switch stacks mid-ticket**: Do not rewrite Blade to Inertia, Filament to custom MVC, or vice versa without an approved change order.
- **Single heavy-work seam**: Never introduce Service + Action + Repository for the same job. One clear write place only.
- **Progressive disclosure**: Detect primary stack from `_registry.md`, then read ONLY that pack file. Never dump all packs into context.
- **No narration comments or predicted fallbacks**: Zero noise docblocks, no `a ?? b ?? c` guesses without an authoritative contract.
- **Mode D patch discipline**: Chat sessions do not retain memory. Patch skill files only when a concrete lesson repeats twice.

## Details

### Modes

| Mode | When | Goal |
|---|---|---|
| **A. Confidence check** | slop / official / OK? | Verdict + stack pack |
| **B. Existing project** | any repo you already have | Match *this* dialect + locks |
| **C. Scaffold new** | greenfield | First-party installer for that stack |
| **D. Patch skill** | new stack / repeated lesson | Add or edit a pack under `stacks/` |

Matt skills present + fuzzy idea → `/grill-with-docs` before Mode C build.

### Workflow (every Mode A/B/C)

1. **Detect** primary stack (+ secondary if mixed) via [stacks/_registry.md](stacks/_registry.md).
2. **Read only that pack** (progressive disclosure — do not load every pack).
3. Apply **daily bar** unless dial-up triggers fire.
4. Output Mode A format if checking confidence.

Never “correct” stack A into stack B mid-ticket (Inertia → Blade, Filament → custom admin, etc.).

### Daily bar vs dial-up

**Daily bar**

1. Trust lockfiles (`composer.lock`, `package-lock.json` / `pnpm-lock.yaml` / `yarn.lock`).
2. Thin shape from the **active pack**.
3. Test only if behaviour moves.

**Dial up when:** new folder tree · money/auth/uploads/PII · new public contract (HTTP, Inertia props, Keycloak theme API) · multi-session feature · “is this slop?”

Full 5-point ladder + false confidence: [reference.md](reference.md).

### Cross-stack anti-slop (always)

- One heavy-work place, not Service + Action + Repository for the same job
- No parallel portals for the same hydrate/save gap
- No narration comments; no unproven `a ?? b ?? c`
- Match in-repo neighbors in Mode B
- Don’t paste another stack’s folder religion into this one

### Mode A — output

```markdown
## Verdict
[idiomatic | suspect | not first-party | blocked]

## Stack
[primary from registry] (+ secondary: …)

## Pack
stacks/<file>.md

## Blast radius
[daily-bar | dial-up: reason]

## First-party vs app
- Locked first-party: …
- App: …

## Slop smells
- … or “none”

## Next
1. …
```

## Mode D — how to patch this skill

When you hit a stack this skill doesn’t know (or a lesson repeats twice):

1. Add a row to [stacks/_registry.md](stacks/_registry.md) (signals → pack path).
2. Copy [stacks/_pack-template.md](stacks/_pack-template.md) → `stacks/<name>.md`.
3. Fill: detect, first-party inventory, thin shape, scaffold, slop smells, dial-up.
4. Keep pack **short** (aim &lt; 80 lines). Link out to upstream docs; don’t paste tutorials.
5. Sync copies if you keep both personal + repo mirrors:
   - Personal: `~/.cursor/skills/laravel-code-confidence/`
   - Optional repo: `.cursor/skills/laravel-code-confidence/`
6. Bump a one-line note in pack (“Last lesson: …”) — still manual, not chat-memory.

**Do not** dump every stack into `SKILL.md`. Registry + one pack per stack.

## Mode B / C (summary)

- **B:** locks → registry detect → open pack → match repo dialect.
- **C:** ask UI/admin/auth choice if unclear → pack’s scaffold section → thin first feature.

## Matt hooks (optional)

| Need | Skill |
|---|---|
| Sharpen idea | `/grill-with-docs` |
| Learn over sessions | `/teach` |
| Primary-source dump | `/research` → then Mode D fold-in |
| Build / review | `/tdd` `/implement` `/code-review` |

## Additional resources

- Ladder + patch tips: [reference.md](reference.md)
- Stack index: [stacks/_registry.md](stacks/_registry.md)
- Scope + 5-year admin (not a UI stack): [stacks/admin-5y-roadmap.md](stacks/admin-5y-roadmap.md)
