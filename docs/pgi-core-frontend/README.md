# pgi-core-frontend — reading backup

Synced from `D:\vitou\projects\pgi-core-frontend` for ops / UAT / agent reuse.

**Why here:** pgi local git often excludes `/docs/` (`.git/info/exclude`). This hub keeps journey docs versioned + pushable.

## Layout

| Path | Contents |
|------|----------|
| `diagrams/` | Reject / endorsement journey markdown, HTML, PDF, render script |
| `prompts/` | Reusable Cursor prompts to regenerate the same style |

## Current diagrams (2026-07-27)

| File | Purpose |
|------|---------|
| `auto-hs-reject-flow.md` | Auto + HS **quotation** reject tables/cols + sequences; Auto **endorsement** reject + full journey section |
| `auto-endorsement-journey.md` | Auto endorsement full user journey (standalone) |
| `auto-endorsement-journey.html` | Printable HTML with Mermaid |
| `auto-endorsement-journey.pdf` | Reading PDF (Mermaid rendered) |
| `_render-endt-journey-pdf.cjs` | Re-render PDF from HTML (`node …`) |

## Sync rule (manual / agent)

When pgi generates new `docs/diagrams/{slug}-journey.*`:

1. Copy into `docs/pgi-core-frontend/diagrams/`
2. Commit + push `laravel13.x` `main` — title style **Adjective Noun** (e.g. `Journey Doc Sync`)
3. Skip if user says `pgi only` / `don't sync`

Source project: `phillipinsurancekh/pgi-core-frontend` (local: `D:\vitou\projects\pgi-core-frontend`).
