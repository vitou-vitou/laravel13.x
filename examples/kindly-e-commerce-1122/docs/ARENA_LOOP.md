# Arena.ai ↔ Spec-Kit loop (kindly-e-commerce-1122)

**Policy:** **Arena.ai only** for external AI review. No Grok.

**Site:** https://arena.ai/text/direct  
**Mode:** **Direct** → **Anthropic claude-sonnet-4-6** (not Battle, not Max)

## Output loop

| Step | Action |
|------|--------|
| A | Paste **Prompt A** → `docs/ARENA_REVIEW_SPEC.md` |
| B | Paste **Prompt B** → `docs/ARENA_REVIEW_PLAN.md` |
| C | Merge valid items into `.specify/specs/001-kindly-ecommerce/*.md` |
| D | Implement per `tasks.md` with Superpowers (TDD) |
| E | `php artisan test` is source of truth |

### Prompt A — Spec review

```text
You are a senior engineer reviewing an e-commerce MVP spec (catalog, session cart, checkout, stub payment).
Return ONLY:
1. Missing commerce/security invariants (max 5)
2. Ambiguous acceptance criteria (max 5)
3. One P1 story to defer for 1-week MVP

SPEC:
[paste spec.md]
```

### Prompt B — Plan review

```text
Review this Laravel Breeze e-commerce plan.
Return ONLY:
(1) security holes (max 5)
(2) test gaps (max 5)
(3) one simplification

PLAN:
[paste plan.md]
```

### Prompt C — Deep roadmap (Phase 3+)

Use after MVP + Phase 2 complete. Save to `docs/ARENA_DEEP_REVIEW_PHASE3.md`.

```text
Deep review for Laravel 13 Breeze e-commerce (catalog, session cart, coupons, stub pay pending→paid, admin CRUD).
Phase 3 options: (A) Stripe Checkout test mode (B) order confirmation email + shipped status (C) pessimistic stock locks (D) Sanctum read API (E) multi-vendor.
Return ONLY:
1) Top 3 security/commerce gaps in current build (max 5 bullets)
2) Rank A-E for 1-week sprint with 1-line rationale each
3) Recommended Phase 3 spec stories (P1/P2)
4) Test gaps to add before coding
5) One thing to NOT build yet
```

**Live run (2026-06-01):** https://arena.ai/c/019e83c1-90b9-73a6-813a-9189c0c80322
