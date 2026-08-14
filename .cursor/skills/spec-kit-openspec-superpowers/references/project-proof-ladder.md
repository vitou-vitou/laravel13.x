# Project proof ladder — flow · basic · deep

**Triggers:** `project flow` · `basic` · `basic e2e` · `basic proof` · `deep` · `deep e2e` · `deep proof` · `go next` (after prior proof rung)

**Purpose:** Spec + prove the product pipeline before feature churn. Works for Snapfy-class apps and any scrape→queue→download (or request→service→persist) stack.

## Ladder

| Rung | User says | Complexity | Goal |
|------|-----------|------------|------|
| **0 — Index** | `load index` | — | Orient only ([load-index.md](load-index.md)) |
| **1 — Flow** | `project flow` | Quick | Document happy-path architecture (no heavy download) |
| **2 — Basic** | `basic` / `basic e2e` | Quick | One happy path: scrape → queue → resolve → ≥256KB file |
| **3 — Deep** | `deep` / `deep e2e` | Standard | Multi-plugin/scorecard, batch queue, locked-ep behavior, fuller download |
| **4 — Next gaps** | `go next` | auto | Highest-value open gap from last `progress.md` |

AFK: auto G1; write OpenSpec under `openspec/changes/<name>/`; evidence in `progress.md` + `evidence.json`.

## Project flow (rung 1)

Write or refresh a short flow map (chat + optional `openspec/changes/project-flow/proposal.md`):

1. **Entry** — launcher / UI / CLI
2. **Detect** — plugin/router by URL
3. **Scrape** — series/metadata + episode list
4. **Queue** — persistence (SQLite/DB)
5. **Resolve** — stream URL (mp4/hls) + headers + `locked`
6. **Download** — engine + ffmpeg if needed
7. **Gaps** — VIP, stubs, GUI

Stop after the map unless user also says `basic` / `deep`.

## Basic proof (rung 2)

Typical artifacts:

- Change: `openspec/changes/basic-e2e-pipeline-proof/`
- Script: `scratch/test_basic_e2e.py` (or project equivalent)

Must PASS:

- scrape title + ≥1 episode
- queue add ≥1 row
- resolve `http` stream
- download file ≥ 256KB (partial OK)
- exit 0 + evidence

## Deep proof (rung 3)

Typical artifacts:

- Change: `openspec/changes/deep-e2e-pipeline-proof/`
- Script: `scratch/test_deep_e2e.py`

Must PASS (required checks):

- plugin detect matrix
- multi-episode resolve (where applicable)
- batch queue
- deeper download threshold (e.g. ≥2MB) or full HLS stitch proof
- locked/VIP episode: no crash; empty stream OK
- optional plugins: live fixture or documented SKIP/KNOWN_STUB

## Snapfy defaults (example)

| Rung | Fixture / note |
|------|----------------|
| Flow | UI → PluginManager → Queue → DownloadEngine |
| Basic | NetShort series → EP1 partial |
| Deep | NetShort + Dailymotion HLS + detect Universal; DramaBox real CDN when implemented |
| Next | cookie VIP · GUI smoke · DramaBox CDN · … |

## Rules

- Do not skip to deep if basic never passed (unless user forces `deep`).
- Prefer live fixtures that can die (Dailymotion) → picker / refresh URL.
- Aikido scan first-party code after implement.
- `go next` = pick next open gap from latest progress; one slice only.
