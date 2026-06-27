# Medium Developer Learning Guide
## Spec-Kit · OpenSpec · Superpowers (and optional Caveman)

**For:** You — a medium developer who wants to be ready for almost any incoming project.  
**Goal:** Read a little each day, practice a little each day, get stronger until new projects feel familiar instead of scary.

> You will never “know it all.” Even seniors and leads learn every week.  
> The win is: **recognize the situation fast → pick the right workflow → deliver safely.**

---

## Part 0 — Honest start (read this first)

### You are medium. That is enough to start.

As a medium dev you can:

- Build features in an existing codebase
- Fix bugs with tests (sometimes)
- Read requirements and ask good questions
- Use Git, PRs, and basic Laravel patterns

You probably **cannot yet** (and that is normal):

- See every architectural risk on day one
- Negotiate scope with the board or CEO
- Always pick the perfect SDD tool without a checklist
- Rescue a “useless” project politically *and* technically at once

**This guide does not ask you to become senior overnight.**  
It asks you to build **repeatable habits** so the next committee meeting, deadline, or messy repo feels manageable.

### What this guide assumes you already have

- Cursor with skills synced (`spec-kit`, `openspec`, `superpowers`, optional `caveman`)
- Optional CLIs: `specify` (Spec-Kit), `openspec` (OpenSpec)
- Basic Laravel / PHP / Git comfort

### What might be missing — tell your future self (or your lead)

Fill this in as you learn. Copy the block to a personal note:

```markdown
## My gaps (update monthly)

- [ ] I have never led a greenfield project from zero
- [ ] I am weak on: _______________________
- [ ] My team uses (Jira / Linear / Excel / verbal only): ___
- [ ] KPI projects I have seen: yes / no — example: ___
- [ ] I have access to staging: yes / no
- [ ] I can say “no” or “not this sprint”: yes / no
- [ ] Languages/stack beyond Laravel: _______________________
- [ ] Things this guide did NOT mention that hit me at work: ___
```

**If you want a mentor or AI to tailor the next weeks, answer those checkboxes honestly.**

---

## Part 1 — The four tools in one sentence each

| Tool | One sentence | You touch it when… |
|------|--------------|-------------------|
| **Spec-Kit** | Write the full plan *before* big build | New project, unclear scope, many rules |
| **OpenSpec** | Track *what changed* on code that already exists | Existing app, iterations, committee tweaks |
| **Superpowers** | Build correctly: TDD, debug, verify, review | **Always** when writing or fixing code |
| **Caveman** | Shorter AI replies, less noise | Long sessions, token limits, you want speed |

**Golden rule:** Spec-Kit **OR** OpenSpec — never both on the same feature.  
**Always add Superpowers** when implementing.

---

## Part 2 — Who is in the room (and what you need from each)

You do not need to *be* every role. You need to *know what they care about*.

| Role | What they usually care about | What a medium dev should do |
|------|------------------------------|-----------------------------|
| **Beginner dev** | Tasks, clarity, not breaking prod | Learn Superpowers TDD on small tickets |
| **Medium dev (you)** | Deliver assigned features safely | Pick SDD path + Superpowers every time |
| **Senior dev** | Design, edge cases, mentoring | Ask them early on architecture / risk |
| **Lead dev** | Timeline, quality, who does what | Give short status: scope, risk, ETA |
| **Manager / IT boss** | Dates, KPI, budget, reports | Translate tech blockers into business language |
| **CEO / board** | Value, reputation, strategy | You rarely talk to them — escalate via lead |
| **Freelancer** | Scope creep, payment, clarity | Same tools, but **write everything down** yourself |

### Your default sentence to senior/lead

> “I read the requirement as X. I plan OpenSpec + TDD unless you want full Spec-Kit. Biggest unknown: Y. ETA after spike: Z.”

That one sentence makes you sound prepared without pretending to be lead.

---

## Part 3 — Project types → which path to use

Use this **before** you type code.

### Quick decision (30 seconds)

```
Is the codebase empty or a greenfield MVP?
├── YES → Spec-Kit + Superpowers
└── NO  → OpenSpec + Superpowers

Is the deadline < 1 week AND scope is tiny?
└── MAYBE skip formal SDD → Superpowers only (+ tiny OpenSpec note if existing repo)

Is the project nearly dead / “useless” / archive candidate?
└── Minimal OpenSpec or one-page note → protect YOUR time → confirm with lead BEFORE deep work
```

### Full scenario matrix

| Scenario | SDD choice | Superpowers focus | Caveman? | Medium dev priority |
|----------|------------|-------------------|----------|---------------------|
| **New project after board meeting** | Spec-Kit | planning → TDD → verify | Optional | Clarify scope in writing first |
| **Existing Laravel app, new feature** | OpenSpec | TDD, review | Optional | Read old code paths first |
| **KPI / reward / visible value** | OpenSpec (usually) | verify-before-completion | No — be clear | Define how KPI is measured |
| **Fast deadline** | Light OpenSpec or none | TDD on critical paths only | Yes | Cut scope, not quality on auth/payments |
| **Long deadline** | Spec-Kit or full OpenSpec | full TDD, plans | No | Avoid gold-plating; weekly demos |
| **Useless / archive soon** | Minimal doc | don’t over-engineer | Yes | Confirm “good enough” bar with lead |
| **Freelance client** | Spec-Kit or OpenSpec | everything in writing | Optional | Scope doc = your shield |
| **You inherit half-done work** | OpenSpec | systematic-debugging | Optional | Spike 2–4 hours before promise |

---

## Part 4 — Day-by-day learning paths

Pick **one path**. Do not mix all paths in week one.

### Path A — 14 days: “Existing project muscle” (best first path for medium)

Most real jobs are existing repos. Start here if unsure.

| Day | Read (15–30 min) | Practice (45–90 min) | Cursor invoke example |
|-----|------------------|----------------------|------------------------|
| 1 | Part 1–3 of this doc | Pick one small bug in your repo | `Use superpowers: systematic-debugging` |
| 2 | Superpowers TDD skill intro | Write 1 failing test, fix bug | `Use superpowers: TDD for this bugfix` |
| 3 | OpenSpec README (GitHub) | `/opsx:new` for a fake tiny change | `Use openspec: /opsx:new fix-login-copy` |
| 4 | Same | Complete change doc only — no code | `/opsx:continue` |
| 5 | verification-before-completion | Implement with TDD | `Use superpowers: TDD` + `/opsx:apply` |
| 6 | Rest or review | `/opsx:archive` + short retro note | What felt slow? |
| 7 | **Weekly review** | Update “My gaps” checklist | |
| 8 | Fast deadline row (matrix above) | Same feature but time-box 2h | Practice cutting scope |
| 9 | KPI row | Define 1 measurable outcome | “User can export CSV in < 3 clicks” |
| 10 | Ask senior one architecture question | Document answer in OpenSpec | |
| 11 | Code review skill | Review your own diff before PR | `Use superpowers: verification-before-completion` |
| 12 | Caveman optional | Repeat day 5 flow with caveman voice | `Use caveman:` |
| 13 | Simulate committee change | Boss adds requirement mid-flight | New `/opsx:new` delta |
| 14 | **Demo to yourself** | 5-min walkthrough written | You’re “existing project ready” |

---

### Path B — 14 days: “Greenfield muscle” (after Path A or if next project is new)

| Day | Read | Practice | Invoke |
|-----|------|----------|--------|
| 1 | Spec-Kit GitHub overview | `/speckit.constitution` on toy project | `Use spec-kit: constitution` |
| 2 | — | `/speckit.specify` one feature | `Use spec-kit: specify` |
| 3 | — | `/speckit.plan` | `Use spec-kit: plan` |
| 4 | — | `/speckit.tasks` | `Use spec-kit: tasks` |
| 5–8 | Superpowers TDD | Implement **one** task per day with tests | `Use superpowers: TDD` |
| 9 | — | `/speckit.implement` remaining tasks | |
| 10 | Long deadline habits | Weekly scope check template | |
| 11 | Board-style change | Add requirement → new spec delta (not OpenSpec) | Spec-Kit update |
| 12 | verification | Full test run + README | |
| 13 | Write “handoff” note | Pretend you go on vacation | |
| 14 | Retro | Greenfield ready? | |

---

### Path C — 7 days: “Emergency deadline” (practice before you need it)

| Day | Focus |
|-----|--------|
| 1 | Matrix row: fast deadline — list what you will **not** build |
| 2 | TDD only on: auth, money, data loss paths |
| 3 | OpenSpec one-pager: goal, out-of-scope, done definition |
| 4 | Implement core path only |
| 5 | Verify + deploy checklist |
| 6 | Post-mortem: what saved you / what hurt |
| 7 | Rest |

---

### Path D — Ongoing (weeks 3–12 and forever)

After Path A or B, rotate weekly themes:

| Week theme | You get stronger at |
|------------|---------------------|
| Debugging week | `systematic-debugging`, logs, reproduce |
| Testing week | TDD, edge cases, factories |
| Communication week | status updates, scope questions |
| Politics week | useless projects, pushback scripts |
| Architecture week | 1 design doc, ask senior to rip it apart |
| Freelance week | written scope, change orders |

**Forever rule:** 1 hour reading + 1 hour deliberate practice beats 8 hours random coding with no method.

---

## Part 5 — Playbooks (copy when project lands)

### Playbook 1 — Board meeting Monday, new project assigned to you

1. **Do not code for the first hour.**
2. Ask lead/senior: greenfield or existing? deadline? KPI?
3. If **new** → `Use spec-kit: /speckit.specify` with requirement text pasted in.
4. If **existing** → `Use openspec: /opsx:new <short-name>`.
5. List **3 unknowns** in writing. Send before EOD.
6. Next day → tasks + Superpowers TDD.

### Playbook 2 — Existing app, senior gives you a function

1. Pull latest, run tests locally.
2. `Use openspec: /opsx:new feature-<name>`.
3. Read related files (30 min max), note in change doc.
4. `Use superpowers: TDD for the next task`.
5. PR + `verification-before-completion`.

### Playbook 3 — KPI / reward project (your name is on it)

1. Write KPI in numbers: conversion, time saved, error rate, revenue.
2. OpenSpec change must link **task → KPI**.
3. Tests on the KPI path (not only happy path).
4. Weekly: one-line metric update to lead.
5. Before launch: verify checklist + screenshot proof.

### Playbook 4 — Fast deadline (this week)

1. Lead agrees **out of scope** list in writing (Slack/email enough).
2. OpenSpec minimal: goal, non-goals, done.
3. TDD: critical paths only.
4. No refactors “while you’re here.”
5. Ship → retro in 30 min.

### Playbook 5 — Long deadline (months)

1. Spec-Kit or full OpenSpec at start.
2. Weekly demo (even to yourself).
3. Fight scope creep with change docs, not memory.
4. Superpowers: plans + sub-tasks, avoid hero weeks.

### Playbook 6 — Useless / nearly archived

1. Ask lead: **minimum bar** for success? Still deployed?
2. Do not Spec-Kit a cathedral for a shed.
3. One-page OpenSpec: fix only what breaks compliance/security.
4. Time-box; document for whoever inherits.
5. Protect your morale — this is practice in **professional restraint**.

### Playbook 7 — Freelance

1. Spec-Kit constitution = contract appendix.
2. Every client change = new OpenSpec or signed change order.
3. Superpowers always; Caveman for long Cursor sessions.
4. Payment milestone ↔ verified deliverable.

---

## Part 6 — What to say when you don’t know

Scripts for a medium dev (not embarrassing, actually respected):

| Situation | Say |
|-----------|-----|
| Requirement unclear | “I can build A or B; which matches the KPI? I’ll document in OpenSpec.” |
| Deadline impossible | “I can deliver X by Friday; Y needs +1 week or we cut Z.” |
| Useless project | “What’s the minimum shippable so we can archive cleanly?” |
| New stack | “I’ll spike 4 hours and report; no ETA until then.” |
| Senior unavailable | “I’ll proceed with OpenSpec + TDD on the happy path; flagging risks R1 R2.” |

---

## Part 7 — How strong is “strong enough”?

You are **ready for incoming work** when you can:

- [ ] Pick Spec-Kit vs OpenSpec in under 1 minute
- [ ] Run one full OpenSpec cycle alone (new → apply → archive)
- [ ] Run one small Spec-Kit cycle (specify → tasks → one task implemented with TDD)
- [ ] Use Superpowers without being reminded (test first, verify before done)
- [ ] Write a 5-line status update lead understands
- [ ] Recognize fast-deadline vs KPI vs archive scenarios

You are **growing toward senior** when you also:

- [ ] Design a module others can extend
- [ ] Mentor a beginner on TDD
- [ ] Push back on scope with data
- [ ] Leave docs the next dev thanks you for

Nobody stops learning unless the industry stops moving. **“No need to learn” means:** patterns feel familiar, you reach for the right playbook automatically, and new surprises become smaller.

---

## Part 8 — Suggested calendar (first 90 days)

| Month | Focus | Success signal |
|-------|--------|----------------|
| **Month 1** | Path A (existing) + daily 1h | 1 archived OpenSpec change with tests |
| **Month 2** | Path B (greenfield) OR Path C (deadline drill) | 1 small greenfield or 1 timed delivery |
| **Month 3** | Rotate Path D themes + real work tickets | Lead says “you handle this module” |

Repeat Month 3 patterns with harder tickets until boring.

---

## Part 9 — Invoke cheat sheet (pin in Cursor)

```text
# Decide
Use spec-kit-openspec-superpowers: which path for [describe project]?

# Existing repo
Use openspec: /opsx:new <name>
Use openspec: /opsx:apply <name>
Use superpowers: TDD for the next task

# New repo
Use spec-kit: /speckit.specify
Use spec-kit: /speckit.tasks
Use superpowers: TDD for task 1

# Quality
Use superpowers: verification-before-completion
Use superpowers: systematic-debugging

# Short sessions
Use caveman:
```

---

## Part 10 — Things this guide might not cover (add yours)

Common gaps at medium level — **check what applies to you:**

- DevOps / CI/CD ownership (GitHub Actions, Forge, Envoyer)
- Database migrations at scale, zero-downtime deploys
- Security reviews, OWASP, pen tests
- Multi-team coordination (API contracts between squads)
- Legacy PHP without tests
- Mobile / frontend (Livewire, Inertia, Vue)
- On-call and production incidents
- Legal / compliance (GDPR, PCI)
- English for stakeholder emails
- Burnout on “useless” projects

**Your turn:** When something hits you at work and this doc had no row for it, add a line to Part 3 matrix and Part 10. That is how the guide becomes *yours* over years.

---

## Part 11 — One page daily habit (print this)

```
Morning (10 min)
  □ What project type is today? (matrix Part 3)
  □ Which SDD? (Spec-Kit OR OpenSpec OR neither)
  □ One Superpowers skill for today

Before coding
  □ Requirement in writing?
  □ Unknowns listed?

While coding
  □ TDD on behavior that matters
  □ Small commits

Before “done”
  □ Tests pass
  □ verification-before-completion
  □ Update OpenSpec / tasks

Evening (5 min)
  □ One sentence: what I learned
  □ Update “My gaps” if needed
```

---

## Closing

You are medium **with a system now**.  
Board meetings, KPI projects, dead lines, and dead repos all map to the same question:

> **New blueprint or track a change? Then build with Superpowers.**

Start **Path A, Day 1** tomorrow.  
In two weeks you will not know everything — but you will be **ready** in a way most medium devs never practice on purpose.

When you hit a scenario this doc misses, write it down and ask:

`Use spec-kit-openspec-superpowers: I had [situation] — which path and playbook?`

That question never gets old.

---

*Sources: spec-kit-openspec-superpowers router skill; [Spec-Kit](https://github.com/github/spec-kit); [OpenSpec](https://github.com/Fission-AI/OpenSpec); [Superpowers](https://github.com/obra/superpowers); optional [Caveman](https://github.com/JuliusBrussee/caveman).*
