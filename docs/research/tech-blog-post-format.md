# Technical blog post format — publisher-stated goals, rules, structure

**Date:** 2026-09-27  
**Scope:** What the publishers themselves say a good technical post is: Medium (help center via Zendesk API — HTML pages 403 to bots), Dev.to (editor guide, terms, CoC, AI guidelines), freeCodeCamp publication style guide, Google developer documentation style guide, Smashing Magazine "Write for us", CSS-Tricks "Guest writing", Laravel News (about/contact + observed post), Laravel Daily (observed posts — no author guide exists; it is a single-author site). No third-party listicles. Hashnode writing guide **unreachable** (see § Sources).

**Verdict:** Every publisher that states rules converges on the same skeleton: honest, specific title (no clickbait, no "-ing" openers) → 1–3 paragraph lede that says what the reader gets → H2 per step/concept (H1 reserved for title) → short paragraphs → code blocks introduced by a sentence, showing only the relevant part, tested → images only when they add value, with alt text and credit → short conclusion that restates what was learned → **one** tasteful CTA at the end, never at the top. Differences are in numbers (freeCodeCamp ≥500 words, CSS-Tricks 600–1,500; Dev.to max 4 tags, fCC 1–5) and in republishing policy (Medium/Dev.to embrace canonical-URL cross-posting; freeCodeCamp and Smashing want originals). All reachable publishers now have explicit AI-content rules: disclose assistance, no bulk undisclosed generation.

---

## Goals

Why a post exists, in the publisher's own words.

| Publisher | Stated goal of a post | Source |
|---|---|---|
| **Medium** | Boost = "especially high-quality stories" where "the reader's life is enriched"; writer has "credible, first-hand experience"; story is "non-derivative" — "doesn't just paraphrase, recombine, or rehash information that is easily found elsewhere". Three distribution tiers: Network (baseline) → General → Boost. | [Distribution Guidelines](https://help.medium.com/hc/en-us/articles/360006362473) (page says "Updated: June 29, 2026"; API `updated_at` 2026-09-22) |
| **Medium** | Publishing puts the story at "a URL under your control", indexes it for search, and pushes it to followers' feed + Digest emails; broader reach only via curation/Boost. | [What happens to your story when you publish](https://help.medium.com/hc/en-us/articles/360018677974) (updated 2026-08-07) |
| **Dev.to** | "A space to discuss and keep up software development and manage your software career"; content must be "on-topic, of high-quality, and is not designed primarily for the purposes of promotion or creating backlinks". Canonical URL is a first-class front-matter field — syndication is expected. | [Editor Guide](https://dev.to/p/editor_guide) · [Terms §11 Content Policy](https://dev.to/terms) (observed 2026-09-27) |
| **freeCodeCamp** | "Share your expertise and your insights with the developer community"; reach via "strong accessibility and SEO"; "Substance Wins the Day" — "the more in-depth and/or detailed a tutorial is … the longer people will spend reading it, and the more likely they will share it". | [Publication Style Guide](https://www.freecodecamp.org/news/developer-news-style-guide/) (page dated Oct 1, 2019, content updated for Hashnode editor; observed 2026-09-27) |
| **Google dev docs** | "Consistent, clear, and user-friendly documentation"; "write accessibly", "write for a global audience". | [Style guide highlights](https://developers.google.com/style/highlights) (last updated 2025-04-02) |
| **Smashing Magazine** | "Our primary goal is to deliver quality content" — "share the tips and tricks they have learned"; pitch must state "What will the target audience take away from reading the article?" | [Writing A Smashing Article](https://www.smashingmagazine.com/write-for-us/) (last updated 4 Jan 2021) |
| **CSS-Tricks** | "Write the article you wish you found when you Googled for it." Must deliver "a sensation of lived experience and professional acumen"; "something new to bookmark and use later". | [Guest Writing](https://css-tricks.com/guest-writing/) (page dated Oct 29, 2014; banner: not currently accepting proposals) |
| **Laravel News** | Site is "news, tutorials, and code examples" for the Laravel community; outside tutorials/packages are fed in via the **Links** section, which "feed into the weekly newsletter, social accounts, and more". | [About](https://laravel-news.com/about) · [Contact](https://laravel-news.com/contact) (observed 2026-09-27) |
| **Laravel Daily** | Observed: free posts answer one question or catalogue one feature set; every post ends in a Premium membership CTA ("Join Premium – $29/month"). No stated editorial goals page. | [laraveldaily.com](https://laraveldaily.com/) (observed 2026-09-27) |

## Rules

Hard constraints stated by publishers. "Observed" = seen on the site, not written as a rule.

| Topic | Medium | Dev.to | freeCodeCamp | Google style | Smashing / CSS-Tricks |
|---|---|---|---|---|---|
| **Title** | Not "sensationalistic … screaming tabloid"; not "overly generic, mysterious, or formulaic"; title+subtitle+cover must "give the reader a good idea of what they'll be clicking into". Custom display title/subtitle allowed. ([Distribution Guidelines](https://help.medium.com/hc/en-us/articles/360006362473), [Custom titles](https://help.medium.com/hc/en-us/articles/214895188)) | `title:` front-matter field; no style rule stated. ([Editor Guide](https://dev.to/p/editor_guide)) | Patterns: "How to fix…", "How to build…", "How to [do task] with [tool]", "How [something] works", "What is [noun]?", "The [X] Handbook" (≥5k words). **No "-ing" openers** ("How to Build X" not "Building X"). Some search keywords, no stuffing. ([Style Guide](https://www.freecodecamp.org/news/developer-news-style-guide/)) | Sentence case. Task-based title = bare infinitive ("Create an instance"); concept = noun phrase; avoid "-ing" first word; one unique H1 per page. ([Headings](https://developers.google.com/style/headings), updated 2026-06-08) | Not stated. |
| **Lede / intro** | Not stated as a rule; Boost examples praise stories that "launch the reader directly into the story". | Not stated. | "Write a concise introduction that tells readers what they'll learn … what your goals are, and/or what they'll accomplish." Then prerequisites. | "Put conditions before instructions." | Pitch must state audience level and takeaway (Smashing). |
| **Headings** | "Correct formatting is one of many indicators of good craftsmanship." | H1 is the title; body starts at H2, increase by one level for sub-sections (accessibility). | H2 for main topics, H3/H4 inside; "We reserve H1 for the article title"; "How to Do X" not "Doing X" in subheadings. | Sentence case; hierarchical, don't skip levels; no numbers/excess punctuation in headings. | Not stated. |
| **Paragraphs / prose** | "Well-written, free of errors, appropriately sourced." | Not stated. | 1–3 sentence paragraphs ("Walls of text will make your readers abandon"); short sentences; active voice; spell out acronyms; no excessive bold/italic; no exclamation marks/semicolons/ellipses. | Second person, active voice, conversational not frivolous, no "please" in instructions, American spelling. ([Highlights](https://developers.google.com/style/highlights), [Tone](https://developers.google.com/style/tone) updated 2026-05-27) | "Friendly but formal", "Technically detailed and correct", "Practical, useful, and self-contained" (CSS-Tricks). Assume "a knowledgeable peer" (Smashing). |
| **Code blocks** | Not stated. | Fenced Markdown; Liquid embeds for GitHub/CodePen/etc.; wrap Liquid in backticks to show as code. | Triple-backtick with language set for highlighting; inline code in single backticks; "highlighted code examples and links to a GitHub repo should be enough"; live demos via CodeSandbox/CodePen embed; **run/test all code samples**, run through Prettier. | Precede sample with an intro sentence ending in `:` (or `.` if material sits between); 2-space indent, wrap at 80 chars; omitted code shown by a language comment, **never** `...`. ([Code samples](https://developers.google.com/style/code-samples), updated 2025-10-10) | "Focus on just the most relevant parts of the code rather than plopping an entire file on the page"; CodePen demos preferred (CSS-Tricks). |
| **Images / alt** | "Images, if any, add value"; "We like to see ALT text … along with appropriate credits"; no cover image beats a bad one; AI cover art must be credited; copyrighted images without permission = rules violation. Editor: alt-text button, captions, ≥1192px wide, ≤25MB. ([Using images](https://help.medium.com/hc/en-us/articles/215679797)) | `![description](url)` — replace "Image description" with real alt text (screen-reader example given); GIF limit 200 megapixels/frame; `cover_image:` best 1000×420. | Own screenshots/diagrams or no-attribution-needed stock (Pexels, Unsplash, Wikipedia); no hotlinking, upload directly; <1MB; "informative alt text on all images"; cover image made by fCC designer. | Alt text required; high-res or vector when practical. | "Visual aides are strongly encouraged … proper alt text and captions … No memes" (CSS-Tricks). |
| **Length** | "Well-crafted stories can be short or long" — length "serves the purpose". | Post "must contain substantial content — may not merely reference an external link that contains the full post". | ≥500 words minimum (narrow topics excepted); handbook 5k–15k, book 15k+; long single tutorials over multi-part series; ToC for long pieces. | Not stated. | 600–1,500 words "sweet spot" (CSS-Tricks); outline pitch 200–300 words (Smashing). |
| **Tags** | Don't tag off-topic to "spam" topic readers; don't mass-mention users. | `tags:` max 4, comma-separated. | 1–5 tags; first tag shows above the article. | n/a | n/a |
| **Canonical / republish** | Cross-posting from own blog allowed with rights; set "This story was originally published elsewhere" → canonical link; import tool sets it automatically. **No duplicate copies on Medium itself** (account suspension). Republished translations not Boost-eligible. ([Set a canonical link](https://help.medium.com/hc/en-us/articles/360033930293), [Republish](https://help.medium.com/hc/en-us/articles/360051846853), [No Duplicate Content](https://help.medium.com/hc/en-us/articles/360039513913)) | `canonical_url:` front-matter field; RSS import maps `<link>` → canonical when enabled. ([RSS guide](https://dev.to/p/publishing_from_rss_guide)) | "No Cross-Posting, Please" to open sites like Medium; own-blog copy OK **only with canonical URL pointing back to fCC**. | Content CC-BY 4.0, code Apache 2.0 (footer). | "Original piece of work for Smashing Magazine, not something you have published elsewhere." |
| **Disclosure / promotion** | First-party promotion allowed; affiliate links must be disclosed (FTC); content whose "primary point" is signups/selling is not distributed. ([Medium Rules](https://policy.medium.com/medium-rules-30e5502c4eb4)) | Affiliate links must be "clearly disclosed" (sample wording given); no posts mainly for backlinks (exceptions: personal blog, own org blog). | One-sentence CTA at the end is fine; **don't open with a product link**; affiliate links only to your own books/courses; disclose if a company pays you; no ghost-writing or branded accounts; all links are `dofollow` — don't abuse. | n/a | Content marketing / product walkthroughs rejected; sponsored posts are a separate labeled program (Smashing). |
| **AI content** | AI-generated writing must be disclosed; undisclosed → Network-only; AI-assisted text must be labeled; AI images must be captioned as such; grammar/outline tools need no disclosure. ([AI policy](https://help.medium.com/hc/en-us/articles/22576852947223), updated 2026-08-26) | Disclose in post or via `#ABotWroteThis`; fact-check; must not promote a business/course; must not contain info the human author "did not already know". ([AI guidelines](https://dev.to/guidelines-for-ai-assisted-articles-on-dev), Apr 8 2024) | Research/code help OK; "don't copy/paste an entire article written by ChatGPT"; test the code; "quality is better than quantity". | n/a | n/a |
| **Attribution** | Plagiarism guidelines; copyrighted images need permission or credited fair use. | Cite any external source; quote + cite direct copies; attribute images, code, videos too; "mosaic or patchwork plagiarism" banned. ([How to avoid plagiarism](https://dev.to/how-to-avoid-plagiarism)) | Link source + pull-quote formatting; credit borrowed code; close paraphrase = plagiarism (examples given). | n/a | n/a |

## Details (structure template)

Reusable outline for a ~1,000-word technical post. **Asked** = publisher states it; **Observed** = seen in published posts only.

| # | Block | What goes in it | Asked by | Observed at |
|---|---|---|---|---|
| 1 | **Title** | Task verb or "What is X" / "How X works"; specific, honest; sentence case (Google) or Title Case (fCC/LD); no "-ing" first word; optional `: subtitle` or `(N things)` | fCC, Google, Medium (honesty) | Laravel Daily colon titles; Laravel News "Eloquent Refreshes: Load Generated Columns After Save" |
| 2 | **Front matter / metadata** | `title`, `published`, `description`, `tags` (≤4 Dev.to / ≤5 fCC), `cover_image`, `canonical_url`, `series` | Dev.to (fields), Medium (canonical), fCC (slug, tags) | — |
| 3 | **Lede (1–3 sentences)** | The problem or question, then what the reader gets. Laravel News: symptom sentence → fix sentence → "Let's see how…" | fCC ("tells readers what they'll learn"), Smashing (takeaway in pitch) | LN, LD ("So, a post explaining the 3 LEVELS…") |
| 4 | **Context / prerequisites** | One short paragraph: version, assumed knowledge, disclaimer (LD: "Important notice: those attributes are optional") | fCC ("Explain any prerequisites") | LD attributes guide |
| 5 | **Body: H2 per step or concept** | Numbered H2/H3 when order matters; noun-phrase H2 for concepts; 1–3 sentence paragraphs; don't overuse lists ("feel like AI spit it out" — fCC) | fCC, Google, Dev.to (H2 start) | LN 6× H2; LD "Level 1/2/3", "Feature 1/4." |
| 6 | **Code sample pattern** | Intro sentence ending `:` → file path line (LD) → fenced block with language → 1–2 sentence explanation of *result*. Show only relevant lines; omitted code = language comment; tested; Before/After pairs for migrations | Google (intro sentence, omission), fCC (tested, highlighted), CSS-Tricks (relevant parts only) | LD `app/Models/Workspace.php:` then block; LN `$order->total; // null` result comments |
| 7 | **Result proof** | Screenshot or output block showing it worked; alt text describing what is shown | Medium/Dev.to/fCC (alt text) | LD screenshots after each step (empty alt observed — a gap vs. the rules) |
| 8 | **"What can go wrong" / caveats** | Edge cases, performance cost, when *not* to use it | *Not asked by any publisher* — observed convention | LN "The query runs on the write connection. With read replicas, a replica might not have the new row yet."; "Refreshes or refresh()" section |
| 9 | **Conclusion / summary** | Restate what was learned; bullet recap for long posts; one-line recommendation | fCC ("remind readers what they just learned"), fCC how-to post ("Give them a quick summary … a call to action") | LD "Summary" H2 + "Pick whichever style your team prefers and stay consistent." |
| 10 | **Further reading** | Links to docs + related posts | *Observed only* | LN "Further Reading" H2 with 4 internal links + docs link |
| 11 | **CTA (one, at the end)** | Newsletter / course / repo / product — one sentence; never at the top | fCC (one-sentence CTA at end, not opening), Medium (no "sales pitch" feel) | LD "Enjoyed This Tutorial? … Join Premium"; LN author bio + newsletter box + sponsor card |
| 12 | **Disclosures** | AI assistance, affiliate links, paid placement | Medium, Dev.to, fCC | — |

## Per-publisher table

| Publisher | Front matter / metadata | Title guidance | Length guidance | Code/image rules | Tone | Republish/canonical | Source |
|---|---|---|---|---|---|---|---|
| **Medium** | Title, subtitle, cover image, topics/tags, custom display title/subtitle, canonical link ("originally published elsewhere"), alt text + captions | Honest; not sensational, not generic/formulaic; must preview the story | "Short or long" — must serve the story | Alt text + credits; no uncredited/copyrighted images; AI images captioned; formatting = craftsmanship | Human, first-hand experience, "respect for the reader" | Own-blog cross-post OK with rights + canonical; no duplicates on Medium; translations not Boost-eligible | [Distribution Guidelines](https://help.medium.com/hc/en-us/articles/360006362473), [Canonical](https://help.medium.com/hc/en-us/articles/360033930293), [AI policy](https://help.medium.com/hc/en-us/articles/22576852947223), [Rules](https://policy.medium.com/medium-rules-30e5502c4eb4) |
| **Dev.to** | Jekyll front matter: `title`, `published`, `tags` (≤4), `canonical_url`, `cover_image` (1000×420), `series`; description shown in Twitter/OG cards | None stated | "Substantial content"; not link-only | Markdown + Liquid embeds; alt text; start body at H2; GIF ≤200 MP | Inclusive, good-faith; disclose AI | `canonical_url` field; RSS `<link>` → canonical | [Editor Guide](https://dev.to/p/editor_guide), [Terms](https://dev.to/terms), [CoC](https://dev.to/code-of-conduct), [AI guidelines](https://dev.to/guidelines-for-ai-assisted-articles-on-dev) |
| **freeCodeCamp** | Slug (short, descriptive), 1–5 tags (first is shown), cover made by fCC designer, canonical only for own-blog copies | "How to …", "What is …", "How X works", "The X Handbook"; no "-ing" openers; some keywords | ≥500 words; handbook 5–15k; book 15k+; long > multi-part; ToC for long | Highlighted fenced code, tested, Prettier'd; repo link; own or no-attribution images <1MB, alt text, no hotlink | Simple, short sentences, 1–3 sentence paragraphs, active voice, spell out acronyms, G-rated | No cross-posting to Medium etc.; original for fCC; own-blog copy must canonical back | [Style Guide](https://www.freecodecamp.org/news/developer-news-style-guide/) |
| **Google dev docs** | n/a (docs) | Sentence case; bare infinitive for tasks; noun phrase for concepts; unique H1 | n/a | Intro sentence → sample; 2-space indent; 80-char wrap; omission via code comment; alt text; hi-res images | Second person, active voice, conversational not frivolous, global audience, no "please" | CC-BY 4.0 text / Apache 2.0 code | [Highlights](https://developers.google.com/style/highlights), [Headings](https://developers.google.com/style/headings), [Code samples](https://developers.google.com/style/code-samples), [Tone](https://developers.google.com/style/tone) |
| **Smashing Magazine** | Pitch: audience + level, takeaway, why you, 200–300-word outline | None stated | Outline 200–300 words; "Ultimate Guides" are longer reference pieces | Not stated | Reader = "knowledgeable peer"; practical advice from real experience | Original only; honorarium; no content marketing, press releases, listicles, product reviews | [Write for us](https://www.smashingmagazine.com/write-for-us/) |
| **CSS-Tricks** | Not stated | "Write the article you wish you found when you Googled for it" | 600–1,500 words | Only relevant code parts; CodePen demos; images with alt + captions; no memes | "Friendly but formal"; all experience levels; technically correct; self-contained | Not stated; $250 per article (page is from 2014, currently closed) | [Guest Writing](https://css-tricks.com/guest-writing/) |
| **Laravel News** | Observed: category (Tutorials/Packages), author byline + bio, date, cover image with alt = title + " image", H2 anchors | Observed: "Noun: Verb Phrase" or feature name | Observed ~700–900 words for tutorials | Observed: fenced PHP with result comments; no screenshots in tutorial | Observed: direct, second person, "Let's see how" | No contributor page found; external tutorials go via [Links](https://laravel-news.com/links) → newsletter | [About](https://laravel-news.com/about), [Contact](https://laravel-news.com/contact), [Eloquent Refreshes post](https://laravel-news.com/eloquent-refreshes-attribute) |
| **Laravel Daily** | Observed: badge (Tutorial · Free / Premium Tutorial), date, "N min read" or "N mins video" | Observed: colon subtitle, counts, question form, "NEW in …" | Observed: 2-min (~250 words) to 14-min (~3,500 words) | Observed: file path line → PHP block → 1–2 sentence result; Before/After pairs; screenshots per step (empty alt) | Observed: first person, blunt, ALL-CAPS emphasis ("NOT breaking changes") | n/a — own site; premium paywall after intro or after section 2 | see § Laravel Daily observed pattern |
| **Hashnode** | Homepage claims only: Markdown, "Import Markdown with tags, dates, and canonical URLs intact", GitHub mirror, custom domain | — | — | "Syntax highlighting in 25 languages, LaTeX, tables, embeds" | — | Canonical preserved on import | [hashnode.com](https://hashnode.com/) (observed 2026-09-27); **writing guide unreachable** |

## Laravel Daily observed pattern

Posts read 2026-09-27 (site has no author guideline page; all posts by Povilas Korop):

1. [Is The New Laravel 13 Teams The Same As "Multi-Tenancy"?](https://laraveldaily.com/post/is-the-new-laravel-13-teams-the-same-as-multi-tenancy) — Tutorial · Free · April 01, 2026 · "2 min read"
2. [PHP Attributes in Laravel 13: The Ultimate Guide (36 New Attributes!)](https://laraveldaily.com/post/php-attributes-in-laravel-13-the-ultimate-guide-36-new-attributes) — Tutorial · Free · March 18, 2026 · "14 min read"
3. [Laravel AI SDK v.0.10: 4 New Features](https://laraveldaily.com/post/laravel-ai-sdk-v010-4-new-features) — Premium Tutorial · August 12, 2026 · "12 min read" (first section free, paywall before "Feature 2/4")
4. [Upsert 1M Rows to Laravel DB: 5 Ways on MySQL / SQLite / PostgreSQL (Benchmarks)](https://laraveldaily.com/post/upsert-1m-rows-to-laravel-db-5-ways-on-mysql-sqlite-postgresql-benchmarks) — Premium · May 19, 2026 · "20 mins video" (one-paragraph lede then paywall)

| Element | Concrete pattern | Evidence |
|---|---|---|
| **Title** | Topic `:` payoff, often with a count or parenthetical; or a direct question; "NEW in X" prefix for release coverage | Posts 2, 3, 4; post 1 (question); "NEW in Laravel Debugbar 4.2: AI Skill for Laravel Boost" |
| **Meta line** | Badge (Tutorial / Premium Tutorial, Free), date, "N min read" or "N mins video" | All four |
| **Lede** | One paragraph, first person, states the trigger and the promise: "I saw this question a few times… So, a post explaining the 3 LEVELS" (1); "In this article, I'll walk you through every PHP attribute… with practical code examples for each." (2); "This tutorial shows four practical features… Let's dive in." (3) | Posts 1–3 |
| **Disclaimer up front** | "Important notice: those attributes are optional… NOT breaking changes" | Post 2 |
| **Body headings** | H2 per concept/step, numbered in the heading text: "Level 1: Application-Level", "Feature 1/4. Filesystem Tools for AI Agents."; long guide nests H2 (era) → H3 (domain) → H4 "N. #[Attr] — What it does" | Posts 1, 2, 3 |
| **Code pattern** | File path as its own line (`app/Models/Workspace.php:`) → fenced PHP block → 1–2 sentences on what happens; **Before / After** paired blocks for syntax migrations | Post 3 (5 files shown); post 2 (36 Before/After pairs) |
| **Screenshots** | Image after each step showing result; alt text is either the heading text (post 1) or empty (post 3) | Post 1: 4 images; post 3: 3 images in section 1 |
| **Prose density** | 1–3 short lines between blocks; ALL-CAPS for emphasis ("TYPE/LEVEL", "NO", "WANT"); "Use when:" bullets | Posts 1, 2 |
| **Ending** | Blunt takeaway ("Don't architect for the bank compliance you may never have. Start with team_id, add global scopes, and ship.") or H2 "Summary" with 4 bullets + one-line recommendation | Posts 1, 2 |
| **CTA block** | Fixed H3 "Enjoyed This Tutorial?" → "Join Premium – $29/month" + "View All Plans" → "Recent Courses on Laravel Daily" (3 cards) → comments | Posts 1, 2 |
| **Premium gating** | Video posts: lede + "Premium Members Only" card; long text posts: first section free, paywall before section 2 | Posts 3, 4 |
| **Repo / video link** | **None found** in the free posts read; premium posts embed video behind paywall | rg for github.com / youtube across fetched posts: 0 hits |

Laravel News comparison (observed, [Eloquent Refreshes post](https://laravel-news.com/eloquent-refreshes-attribute), Sept 24, 2026): 3-sentence lede (symptom → release that fixes it → "Let's see how"), 6 H2s, 7 code blocks with `// null` / `// "108.25"` result comments, a caveat section ("The query runs on the write connection…"), "Refreshes or refresh()" decision section, "Further Reading" H2 with 4 links, then author bio, newsletter box, sponsor card. No screenshots.

## Anti-patterns publishers reject

| Anti-pattern | Who says so | Wording | Source |
|---|---|---|---|
| Clickbait / sensational or generic title, cover that misrepresents | Medium | "misleading, dishonest, or overly sensational" → no General Distribution; "overly generic, mysterious, or formulaic is just as bad" | [Distribution Guidelines](https://help.medium.com/hc/en-us/articles/360006362473) |
| "-ing" title/heading openers | freeCodeCamp, Google | "we don't recommend starting a headline with the -ing form"; "avoid using -ing verb forms as the first word" | [fCC Style Guide](https://www.freecodecamp.org/news/developer-news-style-guide/), [Google Headings](https://developers.google.com/style/headings) |
| Undisclosed / bulk AI content | Medium, Dev.to, freeCodeCamp | Medium: undisclosed AI → Network-only, not paywall-eligible; Dev.to: "wholly discouraging … prolifically generate content which has not been scrutinized"; fCC: "don't copy/paste an entire article written by ChatGPT" | [Medium AI policy](https://help.medium.com/hc/en-us/articles/22576852947223), [Dev.to AI guidelines](https://dev.to/guidelines-for-ai-assisted-articles-on-dev), [fCC](https://www.freecodecamp.org/news/developer-news-style-guide/) |
| Derivative rehash, summaries, link round-ups, listicles | Medium, Smashing | "Unoriginal, derivative, and generic content; summaries of content from other sources; link 'round-ups'; link-farming" = low-value; "Press releases, listicles, and product reviews are unlikely to be interesting" | [Medium](https://help.medium.com/hc/en-us/articles/360006362473), [Smashing](https://www.smashingmagazine.com/write-for-us/) |
| Content marketing / product walkthrough / lead-gen | Medium, Smashing, Dev.to, fCC | "Sponsored content, content marketing, PR pieces"; "pieces full of links to your product … will be rejected"; "not designed primarily for the purposes of promotion or creating backlinks"; "Don't open your tutorial with a link to your product" | as above + [Dev.to Terms](https://dev.to/terms) |
| Uncredited or copyrighted images; missing alt text | Medium, fCC, Dev.to, CSS-Tricks | "copyrighted images without permission" = rules violation; fCC: own or no-attribution images + alt on all; Dev.to: attribute "images, code, videos"; CSS-Tricks: "No memes" | [Medium](https://help.medium.com/hc/en-us/articles/360006362473), [fCC](https://www.freecodecamp.org/news/developer-news-style-guide/), [Dev.to plagiarism](https://dev.to/how-to-avoid-plagiarism), [CSS-Tricks](https://css-tricks.com/guest-writing/) |
| Plagiarism incl. close paraphrase / mosaic | Dev.to, fCC, Medium | "mosaic or patchwork plagiarism"; fCC gives side-by-side examples; Medium AI policy bans AI rephrasing that yields derivative work | [Dev.to Terms §7](https://dev.to/terms), [fCC](https://www.freecodecamp.org/news/developer-news-style-guide/), [Medium AI policy](https://help.medium.com/hc/en-us/articles/22576852947223) |
| Walls of text, excessive formatting, list overload | freeCodeCamp | "Walls of text will make your readers abandon"; "Don't use excessive bold, italics"; "don't overuse lists. It makes the tutorial feel like AI spit it out" | [fCC](https://www.freecodecamp.org/news/developer-news-style-guide/) |
| Whole-file code dumps; `...` for omissions | CSS-Tricks, Google | "rather than plopping an entire file on the page"; "Don't use three dots or the ellipsis character" | [CSS-Tricks](https://css-tricks.com/guest-writing/), [Google Code samples](https://developers.google.com/style/code-samples) |
| Duplicate posts / cross-posting the same piece | Medium, fCC | No duplicate copies on Medium (suspension); fCC "No Cross-Posting, Please" | [No Duplicate Content](https://help.medium.com/hc/en-us/articles/360039513913), [fCC](https://www.freecodecamp.org/news/developer-news-style-guide/) |
| Tag spam / mass mentions | Medium | off-topic tags and "large numbers of mentions" → no General Distribution | [Distribution Guidelines](https://help.medium.com/hc/en-us/articles/360006362473) |
| Multi-part series | freeCodeCamp | "people won't bother reading the second, third, or nth part" — write one long tutorial | [fCC](https://www.freecodecamp.org/news/developer-news-style-guide/) |
| Ghost-written / on-behalf-of-CEO / branded accounts | Smashing, fCC | "immediately reject pieces which are submitted on behalf of your CEO"; "We forbid any sort of ghost writing" | [Smashing](https://www.smashingmagazine.com/write-for-us/), [fCC](https://www.freecodecamp.org/news/developer-news-style-guide/) |
| Undisclosed affiliate links | Medium, Dev.to, fCC | FTC disclosure required; Dev.to gives sample sentence; fCC allows only own books/courses | [Medium Rules](https://policy.medium.com/medium-rules-30e5502c4eb4), [Dev.to Terms](https://dev.to/terms), [fCC](https://www.freecodecamp.org/news/developer-news-style-guide/) |

## Sources

All observed 2026-09-27 unless a page date is shown. Medium help-center HTML returned HTTP 403 to non-browser clients; content was read through the public Zendesk endpoint `help.medium.com/api/v2/help_center/en-us/articles/{id}.json` (same article IDs as the HTML URLs).

**Medium**
- [Medium's Distribution Guidelines: How curators review stories for Boost, General, and Network Distribution](https://help.medium.com/hc/en-us/articles/360006362473) — "Updated: June 29, 2026"
- [What happens to your story when you publish on Medium](https://help.medium.com/hc/en-us/articles/360018677974) — updated 2026-08-07
- [Set a canonical link](https://help.medium.com/hc/en-us/articles/360033930293) — updated 2026-05-31
- [Can I republish content from my own blog?](https://help.medium.com/hc/en-us/articles/360051846853) — updated 2024-04-27
- [Importing a post to Medium](https://help.medium.com/hc/en-us/articles/214550207) — updated 2026-08-27
- [About the No Duplicate Content rule](https://help.medium.com/hc/en-us/articles/360039513913) — updated 2024-04-30
- [Using images](https://help.medium.com/hc/en-us/articles/215679797) — updated 2026-09-10
- [Custom titles & subtitles](https://help.medium.com/hc/en-us/articles/214895188) — updated 2024-04-30
- [Artificial Intelligence (AI) content policy](https://help.medium.com/hc/en-us/articles/22576852947223) — updated 2026-08-26
- [Medium Rules](https://policy.medium.com/medium-rules-30e5502c4eb4)

**Dev.to**
- [Editor Guide](https://dev.to/p/editor_guide)
- [Publishing from RSS or Atom](https://dev.to/p/publishing_from_rss_guide)
- [Terms of Use](https://dev.to/terms) — §7 Copyright/Takedown, §11 Content Policy
- [Code of Conduct](https://dev.to/code-of-conduct) — last updated July 31, 2023
- [Guidelines for AI-assisted Articles on DEV](https://dev.to/guidelines-for-ai-assisted-articles-on-dev) — last updated April 8, 2024
- [DEV Community: How to Avoid Plagiarism](https://dev.to/how-to-avoid-plagiarism)

**freeCodeCamp**
- [The freeCodeCamp Publication Style Guide](https://www.freecodecamp.org/news/developer-news-style-guide/) — page dated October 1, 2019 (content references current Hashnode editor)
- [How to write a great technical blog post](https://www.freecodecamp.org/news/how-to-write-a-great-technical-blog-post-414c414b67f6/) — Sashko Stubailo, August 10, 2018 (published on fCC; used only for the beginning/middle/end convention)

**Google developer documentation style guide**
- [Highlights](https://developers.google.com/style/highlights) — last updated 2025-04-02
- [Headings and titles](https://developers.google.com/style/headings) — last updated 2026-06-08
- [Code samples](https://developers.google.com/style/code-samples) — last updated 2025-10-10
- [Tone](https://developers.google.com/style/tone) — last updated 2026-05-27
- [Voice](https://developers.google.com/style/voice)

**Smashing Magazine / CSS-Tricks**
- [Writing A Smashing Article](https://www.smashingmagazine.com/write-for-us/) — last updated 4 January 2021
- [Write for CSS-Tricks! (Guest Writing)](https://css-tricks.com/guest-writing/) — Oct 29, 2014; banner says proposals currently closed

**Laravel News**
- [About Laravel News](https://laravel-news.com/about)
- [Contact us](https://laravel-news.com/contact) — "If you have written a package or a tutorial please use the links section"
- [The Laravel link feed](https://laravel-news.com/links)
- [Eloquent Refreshes: Load Generated Columns After Save](https://laravel-news.com/eloquent-refreshes-attribute) — Paul Redmond, September 24, 2026 (observed structure)

**Laravel Daily**
- [Home / Recent Tutorials](https://laraveldaily.com/)
- [Is The New Laravel 13 Teams The Same As "Multi-Tenancy"?](https://laraveldaily.com/post/is-the-new-laravel-13-teams-the-same-as-multi-tenancy) — April 01, 2026
- [PHP Attributes in Laravel 13: The Ultimate Guide (36 New Attributes!)](https://laraveldaily.com/post/php-attributes-in-laravel-13-the-ultimate-guide-36-new-attributes) — March 18, 2026
- [Laravel AI SDK v.0.10: 4 New Features](https://laraveldaily.com/post/laravel-ai-sdk-v010-4-new-features) — August 12, 2026 (premium; first section visible)
- [Upsert 1M Rows to Laravel DB: 5 Ways…](https://laraveldaily.com/post/upsert-1m-rows-to-laravel-db-5-ways-on-mysql-sqlite-postgresql-benchmarks) — May 19, 2026 (premium video; lede visible)

**Hashnode**
- [hashnode.com](https://hashnode.com/) — homepage feature claims only

**Unreachable (not used as evidence)**
- `https://support.hashnode.com/en/articles/6392311-writing-a-blog-post` → redirects to `docs.hashnode.com` → HTTP 404; `https://support.hashnode.com/`, `https://docs.hashnode.com/` → 404. No Hashnode writing guide could be read.
- `https://laravel-news.com/contributing`, `/write-for-us`, `/contribute` → 404. Laravel News publishes no contributor guideline page; `/links/submit` requires login.
- `https://css-tricks.com/guest-posting/`, `/write-for-css-tricks/` → 404 (correct page is `/guest-writing/`).
- `https://laraveldaily.com/posts` → 404 (listing is the homepage with `?page=N`).
- `https://medium.com/blog/introducing-boost-…` guessed URL → Medium 404; Boost launch post linked from the guidelines is `https://blog.medium.com/a-new-boost-for-top-stories-541884654fdb` (not fetched).
- `https://dev.to/content-policy`, `/content-policy-guidelines` → 404 (content policy lives in Terms §11).
