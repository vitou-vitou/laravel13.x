---
name: 8-principle-study
description: >-
  Turns any topic, video, article, transcript, or document into a structured
  study packet built on a proven 8-principle learning method (system map, clear
  explanations, multi-media framing, short lessons, retrieval-practice quizzes,
  flashcards, a spaced-repetition schedule, interleaved mixed quizzes, and an
  overlearning plan). Use this skill whenever the user wants to learn, study,
  revise, or memorise something and asks for a study guide, revision packet,
  learning material, flashcards, quiz, cheat sheet, or "help me learn X" — even
  if they don't name the method. Also use it when the user shares a video link,
  transcript, lecture, or reading and wants it converted into something they can
  study from. Output is the user's choice of Word (.docx), PDF, interactive
  HTML, or Markdown.
---

# 8-Principle Study Packet Builder

## What this skill does

This skill builds a complete, self-contained study packet on any topic. It is
not a summariser — it is a learning system. The packet is organised around eight
evidence-based learning principles, grouped into two steps:

**Step 1 — Understanding** (build a correct mental model)
1. Map of the system — an overview so the learner sees how the parts connect.
2. Clear explanations — plain language, no jargon left undefined.
3. Different media — the same ideas as summary, diagram, analogy, and table.
4. Short lessons — content split into bite-sized micro-lessons.

**Step 2 — Automaticity** (make the knowledge stick)
5. Test yourself — retrieval-practice quizzes with answer keys.
6. Wait to review — a spaced-repetition schedule.
7. Mix it up — an interleaved quiz that shuffles topics.
8. Don't stop — an overlearning plan for long-term retention.

The point of the method: understanding alone fades. Knowledge becomes permanent
only when the learner keeps retrieving it, spaced out over time. The packet
gives them everything to do that, in one document.

## Workflow

Follow these steps in order. Do not skip the clarifying questions or the
language-quality pass — they are what separate a useful packet from a generic one.

### Step 1 — Clarify the request

Before building anything, confirm three things with the user. Ask them together
(one round of questions, not a slow interrogation):

- **Source.** What is the packet about? A topic from general knowledge? A video,
  article, or document they will provide? If they give a link, see "Handling
  sources" below.
- **Format.** Which output do they want — Word (.docx), PDF, interactive HTML, or
  Markdown? Always ask; do not assume. Each has a trade-off worth mentioning
  briefly: .docx is editable and printable, PDF is fixed-layout, HTML has
  clickable flashcards and quizzes, Markdown is lightweight and portable.
- **Level and length.** Who is the learner (complete beginner, student,
  professional brushing up) and roughly how deep should it go? This sets
  vocabulary and how many lessons and quiz items to include.

If the user has already answered any of these in the conversation, don't re-ask —
just confirm your understanding and move on.

### Step 2 — Gather the content

Collect the actual substance before touching any output format. A packet is only
as good as the facts in it.

- For a **general topic**, draw on reliable knowledge. If the topic is recent,
  fast-changing, or factual in a way that could be out of date, search the web
  first.
- For a **provided document or transcript**, read it fully and base the packet on
  its content.
- For a **video link**, see "Handling sources" below.

Aim for accuracy over volume. It is better to have ten correct, well-explained
ideas than thirty shaky ones.

### Step 3 — Write the packet content

Write every section the method calls for. Use `references/packet-structure.md` as
the section-by-section blueprint — it spells out exactly what each of the eight
principles becomes on the page, with guidance on length and tone. Read it before
drafting.

Two things matter throughout:

- **Plain, precise English.** Explanations should be simple enough for the stated
  learner level, but every sentence should be grammatically correct, clear, and
  unambiguous. Define a term the first time it appears. Prefer short sentences.
  Avoid filler. This is a learning document — clarity is the product.
- **The method must be visible.** The learner should be able to see which
  principle each section serves (e.g. a heading like "Principle 5 — Test
  Yourself"). The structure is itself a lesson in how to learn.

### Step 4 — Build the deliverable in the chosen format

Now produce the file. Assemble the packet content into the right shape for the
chosen format, then build it.

**Word (.docx).** Use the bundled `scripts/build_docx.js`. It is data-driven:
you write the packet content into a single JSON file and the script turns it
into a polished, validated document (title page, generated table of contents,
styled headings, tables, callout boxes, flashcard tables, glossary). The exact
JSON shape is documented in the header comment of `build_docx.js` — read it
before writing the content file. To run it:

```
cd "$HOME/.cursor/skills/8-principle-study/scripts"
npm install docx              # first time only
node build_docx.js <content.json> <output.docx>
```

Running from the `scripts/` directory matters: `npm install` puts `docx` in a
local `node_modules` there, and Node looks for it relative to the script. After
building, the script prints a confirmation; the `docx` skill's `validate.py` can
double-check the file if you want extra assurance.

**PDF.** Build the .docx with the script above, then convert it — the `docx`
skill's LibreOffice converter is the reliable route. Alternatively, build the
PDF directly with the `pdf` skill.

**Interactive HTML.** Build a single self-contained `.html` file (all CSS and JS
inline — no external files, no CDN). The interactivity is the point of this
format, so make it genuinely usable:

- *Flashcards* flip on click. A simple, robust approach is a CSS class toggled by
  a click handler that swaps the visible face (front prompt ↔ back answer); a 3D
  flip animation is a nice touch but not required.
- *Quiz answers* stay hidden until the learner clicks to reveal them, with a
  per-question reveal and ideally a "show all answers" toggle so the learner can
  self-mark.
- Keep the eight principles as clearly labelled sections, with a jump menu at the
  top. A clean, readable layout (generous spacing, a calm colour for headings)
  helps; match the spirit of the .docx packet.
- Do **not** use `localStorage`, `sessionStorage`, or any browser storage API —
  keep all state in memory. Storage APIs fail in some embedded viewers.

**Markdown.** Write a single `.md` file following the same section structure.
Render flashcards in a clear front/back format (e.g. a bold prompt followed by
the answer, or a two-column table) so they are still usable for self-testing.

Whichever format, save the final file to the outputs folder and give the user a
link.

### Step 5 — Verify before delivering

Quickly check the finished packet:
- All eight principles are present and clearly labelled.
- The quiz answer keys actually match the questions.
- The spaced-repetition schedule has concrete dates or day-offsets.
- For .docx, the build script reported a successful validation.
- Skim for grammar and clarity — fix anything awkward.

Then deliver the file with a one or two sentence summary. Don't over-explain;
the learner can open it themselves.

## Handling sources

If the user gives a **video link** (e.g. YouTube), be straightforward: videos
cannot be auto-transcribed here. Offer the three honest options — they paste a
transcript, they summarise the video's key points, or the packet is built from
general knowledge of the topic. Never claim to have "watched" or "extracted" a
video. If the link is a search-results or playlist page rather than a single
video, point that out too.

If the user gives an **article or document link**, fetch it. If the fetch returns
only a page shell (client-rendered site), say so and ask for the text directly.

## Quality bar

A good packet from this skill has these qualities, and it is worth re-reading the
draft once with fresh eyes to check them:

- A beginner could read Step 1 and come away with a correct mental model.
- Every quiz question is answerable purely from the packet's own explanations.
- The flashcards are genuinely two-sided: a real prompt, a crisp answer.
- The English is clean throughout — a learner is also absorbing the writing.
- The method is felt, not just stated: the learner finishes knowing *how* they
  were taught and could reuse the approach on another topic.

---

## Cross-machine sync

| Location | Purpose |
|----------|---------|
| `~/.cursor/skills/8-principle-study/` | Personal skill (Cursor account sync) |
| `$HOME/.cursor/skills/8-principle-study/scripts/` | docx builder scripts |
| `$HOME/.cursor/skills/8-principle-study/references/packet-structure.md` | Section blueprint |

**Related:** `system-study-packet/` generates repo-specific Markdown study packs; this skill builds general topic packets in docx/PDF/HTML/Markdown.

## Invocation

```text
Use 8-principle-study: build a study packet on Laravel Sanctum for a student, Markdown format.
```