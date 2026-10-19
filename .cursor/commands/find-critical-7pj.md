---
name: /find-critical-7pj
id: find-critical-7pj
category: Workflow
description: Hunt critical correctness bugs on uat for 7pj Direct Book only; dedupe via MEMORIES.md
---

# /find-critical-7pj

Deep bug-finding focused on **high-severity** issues only.

**Input** (optional focus after the command):

```text
$ARGUMENTS
```

## Scope (hard)

- Branch: **`uat`** (or recent commits merged into `uat`)
- Products: **7pj / Direct Book only** — Marine `0189`+`0206`, Burglary `0191`, Money `0192`, Plate Glass `0193`, CAR `0194`, Bond `0195`, PI `0196`
- Out of scope: legacy `0121`–`0125`, style, minor UX, theoretical issues without a concrete trigger
- No Playwright / agent-browser unless user says `enable playwright`
- No commit / push / PR unless highly confident the bug is real and the fix is correct (still ask before push if repo rules require)

## Before anything else

1. Read **`MEMORIES.md`** from this agent’s **persistent memory** (agent store).
2. It tracks bugs already reported: one-line (location + root cause), PR URL, status (`open` | `rejected`), date.
3. Do **not** investigate or re-report a bug that already has an **open** PR.

## Goal

Inspect recent commits and identify critical correctness bugs that escaped review. Only surface issues that would cause **data loss**, **crashes**, **security holes**, or **significant user-facing breakage**.

## Investigation strategy

- Focus on behavioral changes with meaningful blast radius.
- Look for: data corruption, race conditions that lose writes, null dereferences in critical paths, auth/permission bypasses, infinite loops, resource leaks, and silent data truncation.
- Trace the full code path — caller chain and downstream effects. Do not pattern-match the diff alone.
- Ignore: style, minor edge cases, theoretical concerns without a concrete trigger, low-severity UX-only issues.

## Confidence bar

- You must describe a **concrete scenario** that triggers the bug.
- If you cannot construct a plausible trigger, do **not** open a PR.
- When in doubt: report findings in chat (or Slack if configured) **without** opening a PR.

## Fix strategy

- Critical bug found → minimal, high-confidence fix only.
- Add or update tests when possible.
- Avoid broad refactors in the same PR.

## Avoiding duplicate PRs (MEMORIES.md)

For each candidate that matches a tracked entry:

| PR state | Action |
|----------|--------|
| Still **open** | Do **not** open another PR. Summarize + link existing PR. |
| **Merged** | Delete the MEMORIES.md entry. |
| **Closed without merge** | Keep entry; set status `rejected`. Reopen only if code materially changed. |
| Bug gone (fixed another way) | Delete the entry. |

Also delete any `rejected` entry older than **30 days**.

Keep `MEMORIES.md` small: only `open` or `rejected` rows with dates. No run history or scan notes.

## Safety

- Do not open a PR unless highly confident the bug is real and the fix is correct.
- If none found: short **“no critical bugs found”** summary (expected most days).

## Output

If fixed:

- Bug and impact
- Root cause
- Fix and validation performed

If you opened a PR: append to `MEMORIES.md` (one-line, PR URL, status `open`, today’s date) and apply any cleanup from the rules above in the same update.

End with **Next 5** concrete follow-ups.
