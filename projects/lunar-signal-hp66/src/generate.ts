import { randomBytes } from "node:crypto";
import { ADJECTIVES, NOUNS } from "./words.js";

export type SlugParts = {
  adjective: string;
  noun: string;
  id: string;
  slug: string;
};

function pick<T extends readonly string[]>(list: T): T[number] {
  const i = randomBytes(1)[0]! % list.length;
  return list[i]!;
}

function randomId(length = 4): string {
  const alphabet = "abcdefghijklmnopqrstuvwxyz0123456789";
  const bytes = randomBytes(length);
  let out = "";
  for (let i = 0; i < length; i++) {
    out += alphabet[bytes[i]! % alphabet.length];
  }
  return out;
}

/** `${adjective}-${noun}-${randomid}` */
export function generateSlug(idLength = 4): SlugParts {
  const adjective = pick(ADJECTIVES);
  const noun = pick(NOUNS);
  const id = randomId(idLength);
  return {
    adjective,
    noun,
    id,
    slug: `${adjective}-${noun}-${id}`,
  };
}

export function generateMany(count: number, idLength = 4): SlugParts[] {
  const seen = new Set<string>();
  const out: SlugParts[] = [];
  const max = Math.max(1, count);
  let guard = 0;
  while (out.length < max && guard < max * 20) {
    guard += 1;
    const part = generateSlug(idLength);
    if (seen.has(part.slug)) continue;
    seen.add(part.slug);
    out.push(part);
  }
  return out;
}
