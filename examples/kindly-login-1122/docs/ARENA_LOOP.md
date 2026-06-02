# Arena.ai ↔ Spec-Kit loop (kindly-login-1122)

**Status (2026-06-01):** Prompts A and B complete — https://arena.ai/c/019e83a3-aa87-7557-bbe4-face33a778ca → `ARENA_REVIEW_SPEC.md`, `ARENA_REVIEW_PLAN.md`. Re-run only if auth/session routes change.

**Policy:** This project uses **Arena.ai only** for external AI review. Do not use Grok, ChatGPT web, or other review bots for spec/plan loops.

**Site:** https://arena.ai/  
**Browser:** Cursor **cursor-ide-browser** (user must click **Agree** on Terms once per profile).

## Mode selection

| Mode | Use for kindly-login-1122 |
|------|---------------------------|
| **Direct / single model** | Spec review, plan review, task audit |
| **Battle Mode** | Avoid — two blind replies, not suitable for SDD |

**Session defaults:** https://arena.ai/text/direct → mode **Direct** → model **Anthropic claude-sonnet-4-6** (not **Max** auto-route).

## Output loop

| Step | Action |
|------|--------|
| A | Paste **Prompt A** → save reply to `docs/ARENA_REVIEW_SPEC.md` |
| B | Paste **Prompt B** → save reply to `docs/ARENA_REVIEW_PLAN.md` |
| C | Merge valid items into `.specify/specs/001-kindly-login/*.md` |
| D | Implement per `tasks.md` with Superpowers (TDD) |
| E | `php artisan test` is source of truth |

### Prompt A — Spec review

```text
You are a senior security-focused engineer reviewing a login app spec.
Return ONLY:
1. Missing security invariants (max 5)
2. Ambiguous acceptance criteria (max 5)
3. One P1 story to defer for 1-week MVP

SPEC:
[paste spec.md]
```

### Prompt B — Plan review

```text
Review this Laravel Breeze login plan.
Return ONLY:
(1) security holes (max 5)
(2) test gaps (max 5)
(3) one simplification

PLAN:
[paste plan.md]
```

## Automation notes

- Terms modal: user clicks **Agree** once; then agent can fill "Ask anything…" and send.
- Chat URL pattern: `https://arena.ai/c/<uuid>`
- Authority: Spec-Kit files + green tests beat Arena suggestions unless explicitly merged.
