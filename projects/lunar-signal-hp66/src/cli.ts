#!/usr/bin/env node
import { generateMany, generateSlug } from "./generate.js";

function usage(): void {
  console.log(`Usage:
  npm run dev -- [count] [--id-length N] [--json]

Examples:
  npm run dev              # one slug
  npm run dev -- 5         # five unique slugs
  npm run dev -- 1 --json  # JSON object
`);
}

function parseArgs(argv: string[]): {
  count: number;
  idLength: number;
  json: boolean;
} {
  let count = 1;
  let idLength = 4;
  let json = false;

  for (let i = 0; i < argv.length; i++) {
    const arg = argv[i];
    if (arg === "--help" || arg === "-h") {
      usage();
      process.exit(0);
    }
    if (arg === "--json") {
      json = true;
      continue;
    }
    if (arg === "--id-length" && argv[i + 1]) {
      idLength = Number(argv[++i]);
      continue;
    }
    const n = Number(arg);
    if (!Number.isNaN(n) && n > 0) count = Math.floor(n);
  }

  return { count, idLength, json };
}

function main(): void {
  const { count, idLength, json } = parseArgs(process.argv.slice(2));
  const items = generateMany(count, idLength);

  if (json) {
    console.log(JSON.stringify(count === 1 ? items[0] : items, null, 2));
    return;
  }

  for (const item of items) {
    console.log(item.slug);
  }
}

main();
