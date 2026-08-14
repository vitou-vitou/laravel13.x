# Chat intent router

Maps chat text intents to the triad stack. Use when the user (or a chat UI) selects or implies: **plan new project**, **maintain project**, **fix bug**, **explorer**, **developer**.

Source policy: [framework-best-used.md](framework-best-used.md) (Nebutra). Spine = Superpowers; ECC cherry-pick only.

## Flow

```text
Chat text (user intent)
        │
        ▼
┌───────────────────┐
│  Intent router    │  classify: new | maintain | bug | explore | develop
└─────────┬─────────┘
          │
          ├─ plan new project ──► Spec-Kit + Superpowers
          ├─ maintain project ──► OpenSpec + Superpowers
          ├─ fix bug ───────────► Superpowers debug/verify [+ OpenSpec if behavior changes]
          ├─ explorer ──────────► read-only survey + Top-3 star tools (below)
          └─ developer ─────────► Superpowers implement (+ ECC cherry-pick if gap)
```

## Mode map

| Chat mode | User means | Stack | Do / don’t |
|-----------|------------|-------|------------|
| **Plan new project** | Greenfield | Spec-Kit + Superpowers | No OpenSpec. No full ECC. G1 before code. |
| **Maintain project** | Post-MVP change | OpenSpec + Superpowers | Never Spec-Kit + OpenSpec same feature. |
| **Fix bug** | Restore correctness | Superpowers `systematic-debugging` → red test → fix → `verification-before-completion` | OpenSpec only if public behavior/API changes. |
| **Explorer** | Understand options / ecosystem / docs | Read-only + **Top-3 GitHub-star explorer tools** | No commits. No wholesale installs. |
| **Developer** | Build next slice | Superpowers Phase 4 | ECC only named cherry-picks. |

## One-line system prompts

- *Plan new:* Spec-Kit + Superpowers. No code until G1.
- *Maintain:* OpenSpec change order + Superpowers TDD.
- *Fix bug:* Debug gates first; done only with fresh verification.
- *Explorer:* Read-only. Suggest top-3 star tools; run research without writing code.
- *Developer:* Superpowers implement; ECC only if a named skill gap.

---

## Explorer mode — Top-3 GitHub-star suggestions

When intent = **explorer**, after (or instead of) a local codebase skim, **suggest exactly 3 tools** ranked by **GitHub stars** for research / docs / web evidence. Prefer live `gh` counts; fall back to the seed table below (refresh periodically).

### Algorithm

1. Classify explore facet:
   - **community / recency** → social + last-30-days research
   - **library docs / API truth** → versioned docs MCP
   - **web / site scrape** → crawl / browser research
2. Run live ranking when `gh` is available:

```bash
# Examples — adjust queries to the facet
gh api repos/firecrawl/firecrawl --jq '{name:.full_name,stars:.stargazers_count}'
gh api repos/upstash/context7 --jq '{name:.full_name,stars:.stargazers_count}'
gh api repos/mvanhorn/last30days-skill --jq '{name:.full_name,stars:.stargazers_count}'
```

3. Emit **Top 3** as markdown:
   - rank, repo, stars, one-line why, when to use in *this* explore turn
4. Offer to **run** #1 (or user’s pick) next — still read-only relative to the product repo unless user switches to Developer.

### Seed Top-3 (explorer research stack) — snapshot 2026-07-30

Ranked by **GitHub stars** (live `gh` preferred). User examples **last30days** + **Context7** (“7context”) sit in this set; Firecrawl currently leads on stars for web crawl.

| Rank | Tool | Repo | Stars (approx.) | Job in Explorer |
|-----:|------|------|----------------:|-----------------|
| 1 | **Firecrawl** | [firecrawl/firecrawl](https://github.com/firecrawl/firecrawl) | ~158k | Crawl / extract **web pages** into clean markdown for agents |
| 2 | **Context7** (“7context”) | [upstash/context7](https://github.com/upstash/context7) | ~60k | Fresh, version-specific **library docs** (`resolve-library-id`, `query-docs`) |
| 3 | **last30days** | [mvanhorn/last30days-skill](https://github.com/mvanhorn/last30days-skill) | ~55k | What people said in the **last 30 days** (Reddit/X/YT/HN/web) |

Facet override: if the question is **docs-only**, promote Context7 to #1 and keep last30days + Firecrawl as #2/#3. If **community recency**, promote last30days to #1.

Also strong (not default top-3 unless facet fits): `microsoft/playwright-mcp` (~36k), `browserbase/stagehand` (~24k), `modelcontextprotocol/servers` (~89k catalog — not a single explorer skill).

### Explorer output template

```text
Explorer · Top 3 by GitHub stars (facet: {community|docs|web})

1. {name} — {stars}★ — {repo}
   Why: …
   Use now: …

2. …

3. …

Next: run #N, or switch to plan-new / maintain / fix-bug / developer.
```

### Explorer rules

- **Read-only** on the project unless user explicitly leaves Explorer.
- Do **not** install full ECC / gstack from Explorer suggestions.
- Context7 + last30days are the default first two; #3 is the highest-star crawl/docs peer for the facet.
- After research, hand off: Plan new / Maintain / Fix bug / Developer with a 3-bullet brief.

## Keyword triggers

| Chat / keywords | Mode |
|-----------------|------|
| `plan new`, greenfield, from scratch, bootstrap | Plan new project |
| `maintain`, change request, post-MVP, `/opsx` | Maintain project |
| `fix bug`, broken, regression, error | Fix bug |
| `explore`, research, survey, compare tools, last30days, context7, top stars | Explorer |
| `implement`, build, code this, developer | Developer |

## Agent checklist on classify

1. State mode in one line.
2. Load only the stack for that mode (see session-combo-stack).
3. If Explorer → print Top-3 stars table (live preferred).
4. If Plan/Maintain → G1 before code.
5. If Fix bug / Developer → Superpowers gates; no full ECC.
