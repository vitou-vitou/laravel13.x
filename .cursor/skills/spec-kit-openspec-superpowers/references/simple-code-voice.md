# Simple code + plain voice (pgi default)

Applies during **Phase 4 (implement)** and **G4 review**. Works with Caveman voice — spec gates (G1–G4) still required.

## Talk (anyone can read)

| Do | Don't |
|----|-------|
| Short sentences. Plain words. | Long paragraphs, jargon stacks |
| Conclusion first | Preamble, filler |
| Bullets / small tables | Walls of text |
| Say what changed and why | Narrate every tool call |

**Off:** user says `normal mode` → full prose until they re-enable caveman.

## Code — small + short names

| Do | Don't |
|----|-------|
| One job per function | 50-line god functions |
| Short names: `syncCommission`, `patchForm`, `plCommRate` | `applyProductCommissionRateFromBusinessChannel` |
| Verb-first: `loadForm`, `pickOpts`, `syncDropdowns` | `LegacyPolicyEditFormHydrationHelper` |
| Short files: `pl-policy-edit.js` | `pl-legacy-policy-info-edit-hydration.js` |
| Match repo style around the edit | Drive-by renames outside task scope |

## Comments

| Do | Don't |
|----|-------|
| Nothing if code is clear | `/** Fraction in storage... */` on obvious helpers |
| One line only for tricky business rules | Essay comments |

## Name length guide

- **JS/PHP functions:** aim ≤ 3 words (`syncCommission`, `normComm`, `afterEditLoad`)
- **If longer:** split into another small function or use domain shorthand already in repo (`plCommRate`, `DataMaster`)
- **G4 self-check:** flag new exports with 4+ camelCase words unless matching an existing API name

## Examples (this repo)

```text
Good:  pickOpts, patchForm, syncDropdowns, syncCommission, plCommRate, normComm
Bad:   pickEditDropdownOptions, buildEditFormPatch, normalizeCommissionRateForForm
```

## With spec-kit / OpenSpec / Superpowers

- **G1 spec** — plain language; AC anyone can test
- **G2 plan** — task bullets name files + short function intent, not paragraph specs
- **G4 verify** — review includes name length + “can a new dev read this in 30s?”

See also: `.cursor/rules/caveman-mode.mdc`, `.cursor/rules/04-simple-code-voice.mdc`, `pgi-core-policy.md`.
