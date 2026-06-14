# Cursor Learn → our workflow

Official course: **[cursor.com/learn](https://cursor.com/learn)** (watch in browser; free).

This page maps Cursor’s lessons to the **[pocket card](ZERO-MISS-97-TASK-ROADMAP-PROMPT.md#pocket-card-remember-this)** — not a copy of the videos.

---

## Minimum path (≈1 hour — enough for daily dev)

Watch in order, then use the pocket card for real repos.

| # | Lesson | URL | You use it when… |
|---|--------|-----|------------------|
| 1 | Working with agents | [cursor.com/learn/working-with-agents](https://cursor.com/learn/working-with-agents) | Every chat — prompts, delegation, context |
| 2 | Understanding your codebase | [cursor.com/learn/understanding-your-codebase](https://cursor.com/learn/understanding-your-codebase) | New repo (PGI, Keycloakify, Next.js) — **audit first** |
| 3 | Finding and fixing bugs | [cursor.com/learn/finding-fixing-bugs](https://cursor.com/learn/finding-fixing-bugs) | Bug fixes — Debug Mode, verify with tests |
| 4 | Developing features | [cursor.com/learn/creating-features](https://cursor.com/learn/creating-features) | New feature — plan, TDD, small diffs |

**After #4:** paste the [any-stack contributor prompt](ZERO-MISS-97-TASK-ROADMAP-PROMPT.md#pocket-card-remember-this) with your project path.

---

## Full curriculum (optional depth)

### AI foundations

| Lesson | URL |
|--------|-----|
| How AI models work | [how-ai-models-work](https://cursor.com/learn/how-ai-models-work) |
| Hallucination & limitations | [hallucination-limitations](https://cursor.com/learn/hallucination-limitations) |
| Tokens & pricing | [tokens-pricing](https://cursor.com/learn/tokens-pricing) |
| Context | [context](https://cursor.com/learn/context) |
| Tool calling | [tool-calling](https://cursor.com/learn/tool-calling) |
| Agents | [agents](https://cursor.com/learn/agents) |

### Coding agents

| Lesson | URL |
|--------|-----|
| Working with agents | [working-with-agents](https://cursor.com/learn/working-with-agents) |
| Understanding your codebase | [understanding-your-codebase](https://cursor.com/learn/understanding-your-codebase) |
| Developing features | [creating-features](https://cursor.com/learn/creating-features) |
| Finding and fixing bugs | [finding-fixing-bugs](https://cursor.com/learn/finding-fixing-bugs) |
| Reviewing and testing code | [reviewing-testing](https://cursor.com/learn/reviewing-testing) |
| Customizing agents | [customizing-agents](https://cursor.com/learn/customizing-agents) |
| Putting it all together | [putting-it-together](https://cursor.com/learn/putting-it-together) |

---

## One picture: Learn + pocket card

```text
Cursor Learn (skills)          Our repo (when)
─────────────────────          ─────────────────
Understanding codebase    →    Audit D:\… or examples/* (no 97-task MD yet)
Finding/fixing bugs       →    Contributor bug prompt + project tests
Developing features       →    Contributor feature prompt / OpenSpec
Reviewing & testing       →    php artisan test | npm test | pytest
Putting it together       →    End-to-end feature on real Phillip repo
Customizing agents        →    .cursor/rules, skills, SESSION_STATE handoff
97-task ZERO-MISS         →    Rare — big hardening program only
```

---

## Cursor ideas → laravel13.x skills (optional)

| Cursor Learn topic | Extra skill in this repo (optional) |
|--------------------|-------------------------------------|
| Understanding codebase | `codebase-onboarding`, `cavecrew-investigator` |
| Developing features | `superpowers`, `test-driven-development` |
| Finding bugs | `systematic-debugging`, `bug-hunter` |
| Laravel PGI apps | `laravel-specialist`, `filament-pro` |
| Next/React/Keycloakify | `senior-frontend` + stack test command |

Skills **add** to Cursor’s course; they do not replace watching **context** and **hallucination** lessons.

---

## Remember

1. **Learn Cursor** = how to talk to the agent.  
2. **Pocket card** = what to ask for (new vs existing, which path).  
3. **ZERO-MISS long doc** = only for 97-task programs.

Bookmark: [pocket card](ZERO-MISS-97-TASK-ROADMAP-PROMPT.md#pocket-card-remember-this) · [Cursor Learn](https://cursor.com/learn)
