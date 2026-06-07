# 01 — Mindset & Principles

> The senior is not the one who knows the most. The senior is the one who chooses what NOT to do.

---

## The 9 Mental Models That Separate Senior from Mid

### 1. The "Boring Tech" Filter

Every new shiny tool is rejected by default. It earns its way in by solving a *measured* problem the current stack cannot solve. Hype is not a measurement.

**Junior:** "We should rewrite this in NestJS."
**Mid:** "We should add GraphQL."
**Senior:** "What's the actual problem? Let me see the queries."

### 2. The "5-Year Code" Test

Before writing a line, ask: "Will I (or someone) curse me for this in 5 years?"

- Magic methods → curse in 5 years
- Overly clever traits → curse in 5 years
- Service classes with clear names → praised in 5 years
- Comments explaining WHY → praised in 5 years

### 3. The "Boring Path" Bias

When two options have similar tradeoffs, pick the one closer to Laravel conventions. Reasons:
- Easier hiring (any Laravel dev gets it)
- Easier upgrades (Laravel patches it for you)
- Easier debugging (Stack Overflow has answers)
- Easier handoff (you can quit and someone takes over)

### 4. The "Ship in 3 Hours" Discipline

For any feature, ask: "What's the dumbest version that ships in 3 hours?"

Ship that. Iterate from real usage. 80% of "necessary" features die after 2 weeks of real data.

### 5. The "Read the Damn Log" Reflex

Bug report comes in. Junior debug = guess. Mid debug = console.log. Senior debug:
1. Read the log
2. Reproduce locally
3. Read the relevant code
4. *Then* form a hypothesis

90% of bugs are solved at step 1.

### 6. The "It's Just a Function" Lens

Object-oriented Laravel is fine, but most senior code is just well-named functions:
- A Job is a function with retries
- A Command is a function with a CLI
- An Action is a function with validation
- A Service is functions grouped by purpose

If you can't explain a class as "a bag of related functions," you over-engineered it.

### 7. The "Money Lens"

Every architectural decision has a dollar value.

- Use Redis for session: $0/mo (it's already in cache)
- Use SQS instead of Redis queue: +$10-50/mo
- Move to microservices: +$500-5000/mo + 2 engineers
- Add observability tier: +$100/mo + faster debugging

Senior devs justify cost. Mid devs justify "best practices."

### 8. The "User Doesn't Care" Filter

User cares about: speed, reliability, feature works.
User does NOT care about: clean architecture, test coverage, design patterns.

Use clean architecture as a *means* to the user-facing goal. Never as a goal itself.

### 9. The "Sleep Test"

Before deploying on Friday, ask: "If this breaks at 11pm and I'm asleep, what happens?"

- Worst case: data corruption, lost money, customer departure → DO NOT DEPLOY
- Bad case: feature broken, easy rollback → maybe deploy
- OK case: feature broken, falls back to old behavior → deploy

Senior devs deploy on Friday only when option 3 is true.

---

## The Senior's Daily Discipline

### Morning (15 min)
- Check Render dashboard: any failed deploys, alerts, error spikes?
- Check Sentry: any new error types overnight?
- Check email/Slack: blockers, requests, decisions needed
- Pick *one* hard problem to solve before noon

### Deep Work Block (3-4 hours)
- One task. No notifications. No Slack.
- TDD: write the test, watch it fail, write the code, watch it pass, refactor.
- Commit small, commit often (atomic commits make `git bisect` trivial).
- Push when feature is *complete*, not when you got tired.

### Afternoon (PM)
- Code reviews (yours and others)
- Documentation updates
- One technical-debt task per day, max
- Plan tomorrow's deep work

### End of Day Discipline
- Close all PRs you started today
- Commit WIP to branch (never lose code)
- Write one line of "what I learned today"
- Walk away from the screen

---

## The Senior's Communication Style

### In Standup
**Bad:** "I worked on the dashboard."
**Good:** "Fixed N+1 in user list (PR #142), blocked on Render auto-scale config for the queue worker — need access from devops."

State: what shipped, what's next, what's blocking.

### In Slack
- Don't say "Hi" then wait. Say "Hi, can you check the staging deploy at 2pm? It's failing on the migration."
- Threads, not channels.
- Code blocks for code, screenshots for UI.

### In Code Review
- Ask, don't decree: "Is there a reason this isn't a service class?"
- Praise the good before fixing the bad.
- Approve with comments if the issues are minor.
- Block only for: security, data loss, broken tests.

### In Architecture Discussions
- Ask "what problem are we solving?" 3 times before proposing solutions.
- Whiteboard before code.
- Propose 2 options with tradeoffs, let the team choose.
- Disagree and commit. Then re-evaluate in 3 months with data.

---

## The Senior's Refusals

Senior devs refuse to:

1. **Write code without a ticket.** No ticket = no work = no audit trail.
2. **Deploy on Friday after 4pm** (unless it's a critical fix and you'll be on-call).
3. **Skip tests because of deadlines.** The deadline didn't write the test, you did.
4. **Accept "do X by Friday" without scope conversation.** "Yes" is the most expensive word.
5. **Optimize prematurely.** Wait for the slow query, then fix it.
6. **Use new framework features in production within 30 days of release.** Let others find the bugs.
7. **Build features that aren't validated.** "Maybe we'll need it" = trash.
8. **Work for free.** Free tier, free code, free advice — all have limits.

---

## The Imposter Syndrome Antidote

You will feel like a fraud. Every senior dev does. The antidote:

1. **Make a list of every problem you solved this year.** Update it monthly.
2. **Teach one thing per week.** Write a blog post, answer a Stack Overflow question, mentor a junior.
3. **Document your decisions.** "I chose X because Y" — when you re-read it in 6 months, you'll realize you were right.
4. **Compare to past you.** Not to senior devs at FAANG. To you 2 years ago.

The day you stop feeling like a fraud is the day you've stopped growing. Welcome it.

---

## The Long Game

The goal is not to be the best Laravel dev. The goal is to:

1. Build apps that pay your bills
2. Build skills that compound across decades
3. Build relationships that outlast jobs
4. Build a life where work is one part, not the whole

A senior who burns out at 35 is not a senior. A junior who codes for 40 years and retires at 55 is the real winner.

---

## The Daily Reminder

Before opening the laptop:

> I will ship working code today.
> I will write one test before I write the code it tests.
> I will refactor only what the next feature requires.
> I will close the laptop at the end of the day.
> Tomorrow's problems are tomorrow's. Today, I focus on one thing.

Print it. Tape it to your monitor. Read it for 90 days. It will rewire how you work.
