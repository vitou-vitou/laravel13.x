# Ask-Matt Flow Router vs the Spec-Kit / OpenSpec / Superpowers / Caveman Triad

### A Structured Study Packet
Built with an 8-principle learning method.

---

## How to use this packet

This is a **permanent daily reference**. It maps two orthogonal systems you use together: **ask-matt** (the *flow router* — which skill to reach for at each phase of work) and the **triad** (Spec-Kit / OpenSpec / Superpowers, plus optional Caveman voice — the *tooling layer* that fills each phase). Read Step 1 to hold the whole structure in your head; drill Step 2 until you never need to open the skill files again.

The method has two steps. **Step 1 — Understanding**: build a correct mental model (Principles 1–4). **Step 2 — Automaticity**: make it stick through retrieval, spacing, interleaving, and overlearning (Principles 5–8).

Source of truth: the `ask-matt/SKILL.md` and `spec-kit-openspec-superpowers/SKILL.md` skills. Structure is quoted from them, not invented.

> **The 8 principles**
> 1. Map of the system · 2. Clear explanations · 3. Different media · 4. Short lessons
> 5. Test yourself · 6. Wait to review · 7. Mix it up · 8. Don't stop

## Table of contents

- [Step 1 — Understanding](#step-1--understanding)
  - [Principle 1 — Map of the system](#principle-1--map-of-the-system)
  - [Principle 2 — Clear explanations](#principle-2--clear-explanations)
  - [Principle 3 — Different media](#principle-3--different-media)
  - [Principle 4 — Short lessons](#principle-4--short-lessons)
- [Step 2 — Automaticity](#step-2--automaticity)
  - [Principle 5 — Test yourself](#principle-5--test-yourself)
  - [Principle 6 — Wait to review](#principle-6--wait-to-review)
  - [Principle 7 — Mix it up](#principle-7--mix-it-up)
  - [Principle 8 — Don't stop](#principle-8--dont-stop)
- [Appendix — Glossary](#appendix--glossary)

---

# Step 1 — Understanding

Goal: a correct, simple mental model. **ask-matt tells you *when* (which phase). The triad tells you *what* (which tool). They are perpendicular — you always use both.**

## Principle 1 — Map of the system

### Map A — ask-matt: the main flow (idea → ship)

| Stage | Skill | One line: what + when |
|---|---|---|
| 1. Sharpen | `/grill-with-docs` | Interview that sharpens the idea; **stateful**, writes `CONTEXT.md` + ADRs. Start here whenever you're in a working directory. |
| 2. Settle by code? | `/handoff` → `/prototype` → `/handoff` | If a question needs a runnable answer (state, logic, a UI you must see), detour: handoff out, prototype throwaway code, handoff back. |
| 3a. Multi-session? YES | `/to-spec` → `/to-tickets` | Turn thread into a spec, then split into tracer-bullet tickets with blocking edges. |
| 3b. Multi-session? NO | `/implement` | Build right here in the same window. |
| Build | `/implement` | Drives `/tdd` internally (one red-green slice at a time). |
| Test-first alone | `/tdd` | Reach for it alone to build one concrete behaviour test-first without a full spec. |
| Close out | `/code-review` | Two-axis review (Standards + Spec) of the diff before committing. Also usable alone against a branch/PR. |

### Map B — ask-matt: the two on-ramps (start elsewhere, merge into the main flow)

| On-ramp | Skill | What + when |
|---|---|---|
| Bugs/requests piling up | `/triage` | Moves raw issues **you didn't create** through triage roles → agent-ready issues that `/implement` later picks up. Never triage tickets `/to-tickets` already made. |
| Something's broken | `/diagnosing-bugs` | Hard bugs, flakes, regressions. Refuses to theorise until it has a **tight feedback loop** (one command already red on this bug); fixes with a regression test. Hands off to `/improve-codebase-architecture` when there's no clean seam. |

### Map C — ask-matt: the rest

| Group | Skill | What + when |
|---|---|---|
| Big foggy effort | `/wayfinder` | Greenfield/huge build too big for one session. Charts a **shared map of decision tickets**, resolves one at a time → **decisions, not deliverables**. Hands off (doesn't build) — merges back at `/to-spec`. |
| Codebase health | `/improve-codebase-architecture` | Upkeep survey; surfaces **deepening opportunities** → an idea you take to `/grill-with-docs`. |
| Codebase health | `/codebase-design` | The design bench: deep-module vocab (module, interface, depth, seam, adapter, leverage, locality). |
| Vocabulary layer | `/domain-modeling` | Sharpen *domain* language; resolve overloaded terms; record hard-to-reverse decisions as ADRs. Keeps `CONTEXT.md` a clean glossary. |
| Vocabulary layer | `/codebase-design` | Sharpen *module-shape* language (see above). |
| Interview primitive | `/grilling` | Rounds + frontier; facts are the agent's job, decisions are yours. Underlies grill-me, grill-with-docs, triage, wayfinder. |
| Standalone | `/grill-me` | Same interview, **stateless** — no `CONTEXT.md`. Use only when NOT in a working directory. |
| Standalone | `/resolving-merge-conflicts` | Works a mid-merge/rebase conflict hunk by hunk by **intent**; never runs `--abort`. |
| Standalone | `/research` | Background agent reads **primary sources**, leaves a cited Markdown file. Feed into `/grill-with-docs`. |
| Standalone | `/to-questionnaire` | Inverse of grill-me: writes a questionnaire for someone else to fill; answers feed grill-with-docs/to-spec. |
| Standalone | `/wizard` | Steps only a human can do (infra, credentials, dashboards, migrations). Generates an interactive bash script. |
| Standalone | `/wait-what` | Corrective when a message didn't land; re-pitches in plain English using `CONTEXT.md` vocab. |
| Standalone | `/teach` | Learn a concept over multiple sessions, current directory as stateful workspace. |
| Standalone | `/writing-for-agents` | Reference for writing docs agents consume (skills, AGENTS.md). |
| Precondition | `/setup-matt-pocock-skills` | Run once before first flow: configures issue tracker, triage labels, doc layout. |

### Map D — the triad: four tools, four questions

| Tool | Core question | Analogy | Role |
|---|---|---|---|
| **Spec-Kit** | "What rules govern the work?" | Building code manual | SDD (governance) |
| **OpenSpec** | "What changed?" | Change order | SDD (change tracking) |
| **Superpowers** | "How to execute?" | Crew work manual | Execution quality |
| **Caveman** | "How to talk?" | Terse voice | Token compression (optional) |

**Map takeaway:** ask-matt is a *router over phases*; the triad is a *toolset over layers*. Spec-Kit and OpenSpec are **competitors — pick exactly one per feature**. Superpowers is always-on. Caveman is optional voice only.

## Principle 2 — Clear explanations

**What is ask-matt?** A router skill: "you don't remember every skill, so ask." It names the whole flow — one main flow, two on-ramps merging in, plus standalone and vocabulary layers — so you can pick the right next skill without memorising all of them.

**What is a "flow"?** A path through the skills. Most work runs along the single **main flow** (idea → ship). On-ramps are alternate entry points that merge onto it.

**What is the main flow?** grill → (optional prototype) → branch on multi-session → to-spec/to-tickets or straight to implement → implement drives tdd → code-review before commit.

**What are the two on-ramps?** `/triage` (raw incoming bugs/requests) and `/diagnosing-bugs` (something is broken now). Both eventually feed `/implement`.

**What is `/wayfinder` for?** The idea too big to hold in one session. It produces *decisions* by resolving decision-tickets, then hands off to `/to-spec`. Never use it for a well-scoped feature — that's `/grill-with-docs`.

**What is the triad?** Three coding tools plus one voice layer. **Spec-Kit** = rules/governance. **OpenSpec** = change tracking. **Superpowers** = execution quality (TDD, debugging, review). **Caveman** = terse output.

**Why can't Spec-Kit and OpenSpec run together?** Both are Spec-Driven Development (SDD). They overlap. Running both on the same feature is redundant and conflicting — the rule is **pick one**.

**How do ask-matt and the triad relate?** Orthogonal. ask-matt sequences *phases*; the triad supplies the *tool* used inside a phase. Example: the "build" phase in ask-matt (`/implement` → `/tdd`) is exactly where Superpowers lives; the "sharpen/spec" phase is where Spec-Kit or OpenSpec lives.

**Explanation takeaway:** The single most important idea — **ask-matt = when, triad = what; and never combine Spec-Kit with OpenSpec.** Most common misconception: treating Superpowers as an SDD alternative. It isn't — it's the execution layer that runs *alongside* whichever SDD tool you picked.

## Principle 3 — Different media

**One-line summary:** ask-matt routes you through the phases of turning an idea into shipped code; the triad decides which spec tool (one of Spec-Kit *or* OpenSpec) plus always-on Superpowers (and optional Caveman voice) you use inside those phases.

**Diagram — the main flow:**

```
idea
 └─ /grill-with-docs            sharpen (stateful → CONTEXT.md + ADRs)
     └─ need a runnable answer? → /handoff → /prototype → /handoff back
         └─ multi-session build?
              YES → /to-spec → /to-tickets → (per ticket) /implement   [/clear between each]
              NO  → /implement   (same window)
                        └─ /implement drives /tdd (red→green slices)
                             └─ /code-review (Standards + Spec) → commit

 on-ramps:  /triage ─────────────┐
            /diagnosing-bugs ─────┤→ merge into /implement
 foggy:     /wayfinder → decisions → /to-spec (hands off, doesn't build)
```

**Diagram — the two axes:**

```
                 SPEC PHASE          BUILD PHASE         VOICE
ask-matt when:   grill/to-spec       implement/tdd       (any)
triad what:      Spec-Kit XOR        Superpowers         Caveman
                 OpenSpec            (always on)         (optional)
```

**Analogy:** ask-matt is the **GPS route** (turn here, merge there, this exit is for bugs). The triad is your **choice of vehicle and rulebook** for the drive: one navigation app (Spec-Kit *or* OpenSpec, not both), a professional driver (Superpowers) always at the wheel, and an optional radio that talks in shorthand (Caveman).

**Comparison table — often-confused pairs:**

| Pair | Difference |
|---|---|
| `/grill-with-docs` vs `/grill-me` | Same interview. `grill-with-docs` is stateful (writes `CONTEXT.md`) — use in a repo. `grill-me` is stateless — use with no working directory. |
| `/triage` vs `/diagnosing-bugs` | Triage sorts *raw incoming* issues into agent-ready ones. Diagnosing-bugs *fixes* a hard bug with a red repro + regression test. |
| Spec-Kit vs OpenSpec | Spec-Kit = "what rules govern" (governance, greenfield). OpenSpec = "what changed" (change tracking, post-MVP). Competitors — pick one. |
| Superpowers vs Caveman | Superpowers = *how to build* (quality). Caveman = *how to talk* (token-efficient voice). Complementary, both optional-to-combine but Superpowers is recommended always. |

**Media takeaway:** two axes, one picture — *phase* (ask-matt) crossed with *layer* (triad).

## Principle 4 — Short lessons

**Lesson 1 — The main flow is one line.** grill → prototype? → spec+tickets or implement → tdd → code-review. Memorise this and you know 80% of ask-matt.

**Lesson 2 — Two on-ramps.** Incoming raw issues → `/triage`. Something broke → `/diagnosing-bugs`. Both merge into `/implement`.

**Lesson 3 — Foggy and huge → `/wayfinder`.** It makes decisions, not deliverables, then hands off at `/to-spec`. Don't use it for scoped features.

**Lesson 4 — Pick ONE SDD tool.** Spec-Kit (governance/greenfield) XOR OpenSpec (change tracking/post-MVP). Never both on one feature.

**Lesson 5 — Superpowers always, Caveman optional.** Superpowers is the execution-quality layer for every build. Caveman only changes the voice.

**Lesson 6 — The two systems are perpendicular.** ask-matt = *when/which phase*; triad = *what tool in that phase*. You use both at once.

**Short-lessons takeaway:** If you remember only two sentences: *"grill → spec-or-implement → tdd → review"* and *"Spec-Kit XOR OpenSpec, Superpowers always."*

---

# Step 2 — Automaticity

Understanding fades. These four practices make it automatic.

## Principle 5 — Test yourself

**Quiz A (10 questions):**

1. In one line, what is the ask-matt main flow?
2. Which skill do you start with in a working directory, and what does it write?
3. Name the two on-ramps and what each is for.
4. What does `/diagnosing-bugs` refuse to do until it has a tight feedback loop?
5. Can Spec-Kit and OpenSpec run on the same feature? Why or why not?
6. Which triad tool answers "what changed?" and gives `/opsx:*` commands?
7. Is Caveman a replacement for SDD or Superpowers? What layer is it?
8. After `/to-spec`, what splits it into tickets, and what do you do to context between each `/implement`?
9. Greenfield MVP with strict governance — which triad combo?
10. `/wayfinder` produces what kind of output, and where does it merge back?

<details><summary><b>Answer key (Quiz A)</b></summary>

1. grill → (optional prototype) → to-spec/to-tickets or implement → implement drives tdd → code-review before commit.
2. `/grill-with-docs`; it's stateful and writes `CONTEXT.md` + ADRs.
3. `/triage` (raw incoming bugs/requests → agent-ready issues) and `/diagnosing-bugs` (fix a hard bug now).
4. Refuses to **theorise about the cause** until one command reliably goes **red** on this bug; then fixes with a regression test.
5. **No.** Both are SDD and overlap — pick exactly one per feature.
6. **OpenSpec** ("what changed?", `/opsx:*`).
7. Not a replacement. Caveman is the **voice / token-compression layer**; SDD + Superpowers still do the real work.
8. `/to-tickets` splits into tracer-bullet tickets; **`/clear` context between each `/implement`** (each ticket is self-contained).
9. **Spec-Kit + Superpowers** (+ Caveman optional).
10. **Decisions, not deliverables** (resolved decision-tickets); merges back at **`/to-spec`**.
</details>

**Flashcards** (front → back):

1. ask-matt in one phrase → *flow router: which skill to reach for at each phase.*
2. `/grill-with-docs` → *stateful sharpening interview; writes `CONTEXT.md` + ADRs.*
3. `/grill-me` → *same interview, stateless; use when no working directory.*
4. `/triage` → *sort raw incoming issues into agent-ready ones.*
5. `/diagnosing-bugs` → *red repro first, then regression-test fix; no theorising early.*
6. `/wayfinder` → *decision-tickets for huge foggy work; hands off at `/to-spec`.*
7. `/implement` → *drives `/tdd`, then `/code-review` before commit.*
8. `/code-review` → *two-axis review: Standards + Spec.*
9. Spec-Kit → *"what rules govern"; governance/greenfield SDD.*
10. OpenSpec → *"what changed"; change-tracking/post-MVP SDD.*
11. Superpowers → *"how to execute"; always-on quality layer.*
12. Caveman → *"how to talk"; optional terse voice.*
13. Spec-Kit + OpenSpec together → *never on the same feature — pick one.*
14. ask-matt vs triad → *when/phase vs what/layer; perpendicular.*
15. `/clear` between implements → *each ticket self-contained; fresh context each time.*

## Principle 6 — Wait to review

| When | Do | Done |
|---|---|---|
| Today | Read Step 1 fully; take Quiz A | ☐ |
| Day 1 | Redo Quiz A cold; flip all 15 flashcards | ☐ |
| Day 3 | Quiz B (interleaved); re-flip only missed cards | ☐ |
| Day 7 | Both quizzes cold; re-draw the main-flow diagram from memory | ☐ |
| Day 14 | Flashcards only; explain "phase vs layer" out loud | ☐ |
| Day 30 | Cold self-test both quizzes; teach it to a teammate | ☐ |

Spacing works because each retrieval just before you'd forget forces reconstruction, strengthening the memory more than re-reading.

## Principle 7 — Mix it up

**Quiz B (interleaved, 10 questions):**

1. You're mid-rebase with conflict hunks — which skill, and what does it never run?
2. Existing repo, fast iteration, change tracking → which triad combo?
3. A design question needs runnable code — which skill, and which bridges you out/back?
4. Which two ask-matt skills form the "vocabulary underneath" layer?
5. Post-MVP change in laravel13.x locked workflow → which SDD tool?
6. `/research` produces what artifact, and where do you take it next?
7. True/false: Superpowers can replace picking an SDD tool.
8. Which ask-matt skill is for steps only a human can do (infra, secrets)?
9. What's the difference between `/triage` and tickets from `/to-tickets`?
10. In the decision guide, when do you use Superpowers alone?

<details><summary><b>Answer key (Quiz B)</b></summary>

1. `/resolving-merge-conflicts`; it never runs `--abort`.
2. **OpenSpec + Superpowers** (+ Caveman optional).
3. `/prototype`; bridged out and back by `/handoff`.
4. `/domain-modeling` (domain language) and `/codebase-design` (module-shape language).
5. **OpenSpec** (laravel13.x: greenfield = Spec-Kit, post-MVP = OpenSpec).
6. A **cited Markdown file** from primary sources; take it into `/grill-with-docs`.
7. **False.** Superpowers is execution quality, not SDD — you still pick Spec-Kit or OpenSpec.
8. `/wizard`.
9. `/triage` is only for issues you **didn't** create (raw); `/to-tickets` output is already agent-ready — don't triage it.
10. Small task, no formal spec needed → **Superpowers alone** (+ Caveman optional).
</details>

Mixing forces you to *choose* the right skill, not just recall a sequence — the real skill in daily use.

## Principle 8 — Don't stop

| Stage | Signal | Do |
|---|---|---|
| First correct | You answered a quiz right once | Don't stop — re-test spaced (Principle 6). |
| Comfortable | You recall most flashcards | Redraw both diagrams from memory; explain "phase vs layer" unprompted. |
| Automatic | You route work without opening this file | Cold self-test monthly; teach it to a teammate. |

**Overlearning plan:**
- Flip all 15 flashcards until **three flawless rounds** in a row.
- Once a month, cold: name the main flow, the two on-ramps, and the Spec-Kit-XOR-OpenSpec rule without notes.
- Teach the "ask-matt = when, triad = what" model to one person — teaching exposes gaps.
- Each real task, say the phase and tool aloud before starting ("sharpen phase → grill-with-docs; build phase → Superpowers TDD").

**Final takeaway:** Understanding the two axes is Step 1; retrieving them spaced and interleaved (Step 2) is what makes routing automatic. Knowledge becomes permanent through practice **after** first success.

---

# Section C — Automation: chaining as slash-commands per phase

The point of the two axes: ask-matt gives the **phase sequence**, the triad supplies the **tool per phase**. Chain them as slash-commands.

**Greenfield (Spec-Kit path):**
```
/speckit.constitution → /speckit.specify → /speckit.plan → /speckit.tasks
   → (Superpowers: /tdd per task, subagents, /code-review, verification)
   → /speckit.implement
   [+ optional Caveman voice: "Use caveman:" or cavecrew subagents]
```

**Existing project (OpenSpec path):**
```
/opsx:new  →  (Superpowers: /tdd loop during apply)  →  /opsx:apply  →  /opsx:archive
   [+ optional Caveman voice]
```

**Any bug (ask-matt on-ramp, no SDD needed):**
```
/diagnosing-bugs  →  /tdd (regression test)  →  /code-review  →  commit
```

**ask-matt main-flow automation (feature, multi-session):**
```
/grill-with-docs  →  [/prototype via /handoff if needed]  →  /to-spec  →  /to-tickets
   →  per ticket: /implement (drives /tdd) → /code-review  [/clear between tickets]
```

**laravel13.x locked policy (from the router skill):**
- Greenfield → Spec-Kit + Superpowers (+ Caveman optional). **No OpenSpec at init.**
- Post-MVP → OpenSpec + Superpowers (+ Caveman optional).
- **Never** Spec-Kit + OpenSpec on the same feature.
- `continue` → read `docs/SESSION_STATE.md` first.

**Full-stack invocation (voice + triad router, no auto-SDD):**
```
Use caveman spec kit openspec superpower:
```
Loads the `caveman-spec-triad` skill (persistent caveman voice + triad router + laravel13.x policy); does **not** run `/speckit.*` or `/opsx:*` until you ask.

---

# Appendix — Glossary

| Term | Plain-language definition |
|---|---|
| ask-matt | Router skill that tells you which skill to reach for at each phase of work. |
| Flow | A path through the skills; most work runs the single main flow. |
| Main flow | idea → grill → (prototype?) → spec+tickets or implement → tdd → code-review. |
| On-ramp | Alternate entry point (`/triage`, `/diagnosing-bugs`) that merges into the main flow. |
| Tracer-bullet ticket | A small self-contained ticket with declared blocking edges, produced by `/to-tickets`. |
| Blocking edge | A dependency link saying a ticket can't start until another is done. |
| Smart zone | ~150k-token window where the model still reasons sharply; `/compact` before exceeding it. |
| Decision ticket | A `/wayfinder` unit that resolves to a decision (not code). |
| SDD | Spec-Driven Development — Spec-Kit and OpenSpec are two competing SDD tools. |
| Spec-Kit | SDD tool answering "what rules govern the work"; governance/greenfield; `/speckit.*`. |
| OpenSpec | SDD tool answering "what changed"; change tracking/post-MVP; `/opsx:*`. |
| Superpowers | Execution-quality layer (TDD, debugging, review); always-on; not an SDD alternative. |
| Caveman | Optional terse voice / token-compression layer; includes cavecrew subagents. |
| Phase | A chunk of work inside a session (grilling, implementation, QA). |
| Phase boundary | The point between two phases; choose Continue / `/clear` / `/handoff` / Subagent / `/compact`. |
| ADR | Architecture Decision Record — a logged hard-to-reverse decision. |
| `CONTEXT.md` | The stateful glossary/paper trail `/grill-with-docs` maintains. |

**Further study:** re-read the two source skills — `ask-matt/SKILL.md` and `spec-kit-openspec-superpowers/SKILL.md` — only if a term here feels thin; otherwise this packet is self-contained.
