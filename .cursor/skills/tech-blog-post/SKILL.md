---
name: tech-blog-post
description: >-
  Format for a technical blog post on any topic (Medium, Dev.to, Laravel News,
  Laravel Daily, freeCodeCamp style). Goals, hard rules, and a reusable outline.
  Use when the user asks to write, rewrite, or review a blog, article, tutorial,
  or "make it look like a real blog", or says /tech-blog-post or /blog.
---

# Tech blog post

Source of truth: [docs/research/tech-blog-post-format.md](../../../docs/research/tech-blog-post-format.md) (publisher-stated, cited). This file is the compressed rule set. Patch the research doc first, then this file.

Pairs with `08-reader-loved-code` (humanizer pass) after the draft.

## Goals

1. **Teach one thing** from first-hand experience. "Write the article you wish you found when you Googled" (CSS-Tricks).
2. **Non-derivative.** No rehash of the docs (Medium Boost). Reader must get something they cannot copy from the manual.
3. **Takeaway up front.** Lede states the problem and what the reader gets (freeCodeCamp, Smashing).
4. **One canonical home.** `canonical_url` when syndicating (Dev.to, Medium). Never duplicate copies on one platform.
5. **Reader finishes.** Short paragraphs, code that runs, one CTA at the end, not the top.

## Rules

- **Title:** honest and specific. "How to X with Y" / "What is X" / "How X works" / "Topic: Payoff". No "-ing" opener. No clickbait, no mystery (Medium, fCC, Google).
- **Headings:** H1 = title only. Body starts at H2. Never skip a level. Numbered H2 only when order matters.
- **Prose:** 1–3 sentence paragraphs. Active voice, second person. Spell out acronyms once. No bold soup, no list overload ("feels like AI spit it out", fCC).
- **Code:** one intro sentence ending `:` → file path line → fenced block with language → 1–2 sentences on the *result*. Only relevant lines. Omissions as a code comment, never `...`. Every sample tested.
- **Images:** only when they add value. Alt text always. Own screenshots or credited stock. AI images captioned as AI.
- **Length:** ≥500 words minimum; 600–1,500 is the sweet spot for one idea. Long single post beats a multi-part series.
- **Meta:** tags ≤4 (Dev.to) / ≤5 (fCC). `description`, `cover_image`, `canonical_url` filled.
- **Disclosure:** AI assistance, affiliate links, paid relationship — say so. Never open with a product link.
- **Voice check:** no skill jargon in a published post. No "Mode A", "pack", "dial-up". Name real tools (Filament, Laravel docs) the way a reader would.

## Details

### Outline (~1,000 words)

| # | Block | Content |
|---|---|---|
| 1 | Title | Verb or "What is X"; optional `: subtitle` or `(N things)` |
| 2 | Front matter | `title` `published` `description` `tags` `cover_image` `canonical_url` |
| 3 | Lede | Problem in 1–3 sentences → what you get → "Let's see how" |
| 4 | Context | Version, prerequisites, one disclaimer line |
| 5 | Body | H2 per step or concept; 1–3 sentence paragraphs |
| 6 | Code pattern | intro `:` → path → fenced block → result sentence; Before/After pairs |
| 7 | Proof | Screenshot or output block, with alt text |
| 8 | What can go wrong | Edge case, cost, when not to use (observed convention, not a publisher rule) |
| 9 | Summary | Restate what was learned; one-line recommendation |
| 10 | Further reading | Docs + 2–4 related links |
| 11 | CTA | One sentence, at the end |
| 12 | Disclosures | AI, affiliate, sponsor |

### Laravel Daily shape (observed)

"Topic: Payoff (N things)" title → one first-person paragraph ending "Let's dive in" → H2 "Level 1:" / "Feature 1/4." → file path before each PHP block → Before/After → screenshot per step → "Summary" H2 → fixed membership CTA.

### Laravel News shape (observed)

Symptom → fix → "Let's see how" lede → ~6 H2 → code with `// null` result comments → caveat section ("X or Y?") → "Further Reading" → author bio + newsletter.

### Anti-patterns publishers reject

Clickbait or generic title · undisclosed or bulk AI · listicles and round-ups · backlink marketing · uncredited images, no alt · patchwork plagiarism · walls of text · whole-file code dumps · duplicate cross-posts · tag spam · multi-part series · ghost-writing.

### Review checklist

- [ ] Title passes "would I click and not feel tricked"
- [ ] First paragraph names problem + takeaway
- [ ] No H1 in body; no skipped levels
- [ ] Every code block: intro sentence, language, tested, result sentence
- [ ] Every image: alt text, credit
- [ ] One CTA, at the end
- [ ] Zero internal skill vocabulary
- [ ] Humanizer pass done (`08-reader-loved-code`)

## Last lesson

- 2026-09-27: five roadmap posts read like skill worksheets (Mode A blocks, pack names). Published prose must drop internal vocabulary.
