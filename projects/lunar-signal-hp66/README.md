# lunar-signal-hp66

Standalone **TypeScript 100%** CLI — generates random project slugs:

```text
${adjective}-${noun}-${randomid}
```

Example: `lunar-signal-hp66`, `brisk-harbor-x4m9`

## Stack

| Layer | Choice |
|-------|--------|
| Language | TypeScript 100% |
| Runtime | Node.js 20+ |
| Module | ESM (`"type": "module"`) |
| Build | `tsc` → `dist/` |

No Laravel, no shell scripts (npm scripts only).

## Setup

```bash
cd projects/lunar-signal-hp66
npm install
npm run dev          # one slug
npm run dev -- 5     # five slugs
npm run dev -- 1 --json
npm run build && npm start -- 3
```

## Layout

```text
src/
  words.ts      # adjective + noun lists
  generate.ts   # core slug logic
  cli.ts        # CLI entry
```

## Rename / fork

This repo folder name is itself one generated slug. Copy the folder and run `npm run dev` to name the next project.
