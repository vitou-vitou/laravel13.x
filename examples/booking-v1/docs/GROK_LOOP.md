# Grok ↔ Spec-Kit loop (booking-v1)

Grok cannot be automated without an **xAI account session**. After you sign in once in the same browser profile `agent-browser` uses, you can run this loop manually or ask Cursor to drive `agent-browser` again.

## 1. Sign in (one time)

1. Open: https://grok.com/
2. Click **Sign in** and complete login.
3. Tell Cursor: *"Grok is signed in — continue the Grok loop."*

**Done (2026-06-01):** Session worked in Cursor browser. Reviews saved to `GROK_REVIEW_SPEC.md` and `GROK_REVIEW_PLAN.md`. Chat: `https://grok.com/c/3f0a7a99-d5e1-435b-95b6-8081adc734b4`. Prompt C not run yet.

## Arena.ai (separate product)

- URL: https://arena.ai/ — use **cursor-ide-browser** when signed in in Glass.
- **Battle Mode:** two anonymous assistants; vote A/B. Not for spec review loops.
- Terms modal: user must click **Agree** once (automation unreliable).
- Sample "hi" chat: `https://arena.ai/c/019e8393-c0b0-7aad-a92e-39b07ebbee5a`

## 2. Output loop (paste → review → implement)

| Step | Who | Action |
|------|-----|--------|
| A | You / Cursor | Paste **Prompt A** into Grok |
| B | Grok | Returns critique or additions |
| C | Cursor | Merges valid feedback into `.specify/specs/001-appointment-booking/*.md` or code |
| D | Repeat | **Prompt B** after plan exists, **Prompt C** after tasks |

### Prompt A — Spec review (after `spec.md` exists)

```text
You are a senior product engineer reviewing a booking system spec.

Read this spec and return ONLY:
1. Missing invariants (max 5)
2. Ambiguous acceptance criteria (max 5)
3. One P1 user story we should cut for a 1-week MVP

SPEC:
[paste contents of .specify/specs/001-appointment-booking/spec.md]
```

### Prompt B — Plan review

```text
Review this Laravel implementation plan for appointment booking.
Return: (1) security holes (2) concurrency risks (3) simplified schema if overbuilt.

PLAN:
[paste plan.md]
```

### Prompt C — Task audit

```text
Audit this tasks.md for TDD order. Return reordered task IDs if any test should move earlier.

TASKS:
[paste tasks.md]
```

## 3. Agent-browser commands (after login)

```bash
agent-browser open "https://grok.com/"
agent-browser snapshot -i
agent-browser fill @e10 "PASTE_PROMPT_HERE"
agent-browser press Enter
agent-browser wait --load networkidle
agent-browser snapshot -i   # read Grok reply from tree or screenshot
```

## 4. Authority

**Spec-Kit files in this repo + Pest green tests** are source of truth. Grok output is advisory unless you explicitly ask Cursor to merge it.
