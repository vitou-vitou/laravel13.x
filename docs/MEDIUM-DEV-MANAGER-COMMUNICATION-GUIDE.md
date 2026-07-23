# When You Code Well But Freeze in Meetings
## For medium devs who handle multiple systems and struggle with manager questions

**You are not useless.**  
Last year you learned Spec-Kit, OpenSpec, Superpowers — that is real growth.  
What hurts now is a **different skill**: speaking under pressure about systems you touch but never wrote down for *yourself*.

Managers rarely test “can you code?” in those moments. They test:

- Do you know **what this system does for the business**?
- Can you say **status and risk** without panic?
- Will you **follow up** instead of guessing?

This guide is for that gap — not to replace technical learning, but to stop meetings from destroying your confidence.

---

## Part 1 — What managers actually mean (decoder)

When they say… | They often mean… | You reply with…
---|---|---
“How is feature X?” | Is it done? Any blocker? | Status + one risk + ETA
“Explain in detail” | I need to trust you / report upward | User → action → result (not code)
“Why is it slow / broken?” | Who owns fix? When? | What you know + what you’ll verify + when
“Do you know this system?” | Can I send users to you? | One-sentence purpose + where doc lives
“Why didn’t you know?” | I’m stressed / someone blamed me | No defending — facts + fix plan + follow-up time
Silence after your answer | They didn’t get it OR they’re thinking | “Want the 30-second version or step-by-step?”
Same question 3 times | You’re not answering *their* question | “Are you asking about timeline, users, or technical cause?”

**They speak outcomes. You speak implementation.**  
Bridge: **Who uses it → what they click → what happens → what breaks if it fails.**

---

## Part 2 — Scripts when you don’t know (use these word-for-word)

These are **professional**, not weak. Seniors use them too.

### During live questions (buy 24 hours)

> “I don’t have that number in front of me. I’ll confirm in the system and reply by **[today 4pm / tomorrow morning]**.”

> “I know the flow for **[part A]**; I need to verify **[part B]** before I give you a correct answer.”

> “Can you help me narrow it — are you asking about **users**, **data**, or **deployment**?”

> “I’ll document this in a one-page note and share after the meeting.”

**Never guess** in front of the boss. One wrong number hurts more than “I’ll verify.”

### After you couldn’t answer (repair reputation)

Send email or chat within 24 hours:

```text
Subject: Follow-up — your questions on [System name]

Hi [Name],

You asked about [topic] in today’s meeting. I verified:

1. [Question 1] → [Short answer]
2. [Question 2] → [Short answer]
…

If anything should be documented for the team, I can add it to [wiki/README].

Thanks,
[You]
```

**People who follow up look responsible.** People who guess look careless. You choose follow-up.

### When someone talks over you or mocks you

Stay flat tone — no joke back, no long explanation:

> “I’m answering the question. One moment.”

> “I’ll send the details in writing after this.”

> “That’s not accurate — I’ll share the facts after I verify.”

If it repeats: note date, quote, witness. Tell your lead **once**, calmly:  
“I need technical questions in writing or with time to verify — public pressure without prep makes wrong answers.”

Bullying is not feedback. Document it.

---

## Part 3 — Five systems: your private “boss notebook”

You handle ~5 admin web apps. Boss asks 10 detail questions. You blank because **nothing is in one place**.

Spend **15 minutes per system per week** (not all at once). After 5 weeks, you’re the person who “knows the systems.”

### One page per system (copy this template)

```markdown
# System: [Name]

## One sentence (say this first)
This app lets [who] do [what] so that [business result].

## Who uses it
- Role:
- How many users (approx):
- Internal / external:

## Top 5 features (boss cares about these)
1. Feature → user action → result
2. …
3. …
4. …
5. …

## Where data lives
- Main tables / models:
- Integrations (email, payment, API):

## What breaks often
- Symptom → usual cause → who to ask

## Deploy / access
- URL staging / prod:
- Last deploy (you or team):

## Questions I couldn’t answer yet
- [ ] …
- [ ] …
```

Keep this in Notion, Obsidian, or `docs/systems/` — **your** copy, updated every Friday.

### Daily 10-minute drill (while learning)

Pick **one** question bosses often ask:

- What does this screen do?
- Who can access it?
- What happens if this job fails?
- Where is this setting?

Find the answer in code or DB **once**, write one line in the notebook.  
Next time = you answer in 5 seconds.

---

## Part 4 — Learn during the hard period (minimum plan)

You’re stressed. Don’t add 3-hour study blocks.

### Every workday (25 min total)

| Minutes | Action |
|--------|--------|
| 10 | Update one section of one system notebook |
| 10 | Re-read yesterday’s “questions I couldn’t answer” — find one answer |
| 5 | One sentence: “Tomorrow I want to know ___ about system ___” |

### Every week

| When | Action |
|------|--------|
| Monday | Pick which system is “focus system” this week |
| After any bad meeting | Write questions you failed — **no shame**, just list |
| Friday | 5-line summary per system: status, one risk, one win |
| Once | Ask senior: “If boss asked you about [system], what 5 things would you say?” |

### Connect to your triad skills (you already know these)

| Situation | Tool |
|-----------|------|
| Boss adds vague requirement | OpenSpec or short written spec — “So we agree X is in, Y is out” |
| You need to explore code safely | Superpowers systematic-debugging — **for learning**, not only bugs |
| New feature on your app | OpenSpec + TDD — notebook updated when done |
| Long session mapping 5 apps | Optional Caveman — terse notes |

**Coding skills + notebook = you can answer in meetings.**

---

## Part 5 — Meeting prep (30 min before boss calls)

If you know the topic (or even if you don’t):

1. Open notebook for that system.
2. Read **one sentence + top 5 features** out loud once.
3. Prepare 3 lines: **Done / In progress / Blocked**.
4. Prepare one line: “I’ll verify details I’m unsure about after this call.”

If surprise meeting: use scripts from Part 2. Follow up after.

---

## Part 6 — Why you feel “looked down on”

Often it’s a mix of:

1. **Recall under stress** — you know more than you can say when anxious.
2. **No shared map** — team seniors hold context in their heads; you weren’t given the map.
3. **Bad culture** — public quizzing as power play (bullying).
4. **Language gap** — manager uses business words; you think in code words.

Fix what you control: **notebook + follow-up emails + scripts**.  
Escalate what you don’t: **repeated mockery, insults, setup-to-fail quizzes without notice**.

You are not competing to be senior overnight. You are competing to be **reliable and prepared** — that is enough to earn respect over 2–3 months.

---

## Part 7 — 30-day “unstick” calendar

| Week | Focus | Win by Friday |
|------|--------|----------------|
| 1 | System 1 notebook — one sentence + top 5 features | Answer 1 boss-style question aloud alone |
| 2 | System 2 + follow-up email after any hard meeting | Send 1 follow-up doc |
| 3 | Systems 3–4 — “what breaks often” | Reduce “I don’t know” from 10/10 to 6/10 |
| 4 | System 5 + review all one-liners | 5-minute verbal tour of all apps |

Repeat until boring. That boredom = confidence.

---

## Part 8 — What to tell your lead (optional, one time)

If you have a lead you trust:

> “I’m solid on delivery with our dev workflow, but I’m weak when [boss/name] asks detailed system questions without notice. I’m building a one-pager per app and will follow up in writing when I need to verify. Can we do 20 minutes once so you tell me what **they** usually care about for each system?”

That is mature, not weak.

---

## Part 9 — You are not starting from zero

| Last year / before | Now |
|--------------------|-----|
| No structured build workflow | Spec-Kit / OpenSpec / Superpowers |
| Random coding | TDD, verify, change docs |
| This year’s gap | Manager language + system memory under pressure |

**Next layer:** boss notebook + meeting scripts + follow-up habit.

Same person who thanked themselves for skill growth can grow this layer too — smaller steps, same daily discipline.

---

## Part 10 — Emergency card (screenshot this)

```
DON'T: guess, argue, long silence, self-attack
DO:    "I'll verify and reply by [time]"
       write question down
       follow up in writing
       15 min/day on one system page
       document bullying once if repeated

ONE SENTENCE FORMULA:
Who uses it → what they do → what result → one risk
```

---

*Companion to `docs/MEDIUM-DEV-TRIAD-LEARNING-GUIDE.md` — technical workflow + human workflow together.*
