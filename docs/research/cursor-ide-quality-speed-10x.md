# Cursor IDE — quality + speed 10x (primary-source research)

Date: 2026-09-20  
Audience: power user on a large Laravel monorepo with heavy rules/skills (spec-first, caveman, ponytail, model routing)  
Method: official Cursor docs (`cursor.com/docs`, `docs.cursor.com`), Cursor blog/changelog, first-party product pages only

## Verdict

**10x is not “bigger context + more always-on rules.”** Official Cursor guidance and product design point the opposite way: **thin static context**, **dynamic discovery** (skills, MCP tools, search/subagents), **plan-before-code for hard work**, **parallel isolated agents**, and **model routing / Fast variants for throughput**. For a monorepo already drowning in always-apply rules, the highest-leverage move Cursor itself documents is: **migrate intelligent/dynamic rules → Skills**, keep Always/globs rules short, ignore noise with `.cursorignore`, and route routine work off frontier models.

---

## 1. Official speed features

### Tab (inline completions)

- Tab accepts the current inline suggestion; `Cmd/Ctrl→` accepts the next word ([Keyboard Shortcuts](https://cursor.com/docs/reference/keyboard-shortcuts)).
- Pro / Pro Plus / Ultra include **unlimited tab completions** ([Models & Pricing](https://cursor.com/docs/models-and-pricing)).
- Rules **do not** affect Tab ([Rules FAQ](https://cursor.com/docs/rules)).
- Tab has its own hook surface (`beforeTabFileRead`, `afterTabFileEdit`) distinct from Agent hooks ([Hooks](https://cursor.com/docs/hooks)).
- `.cursorignore` blocks Tab (and Agent / Inline Edit / `@` mentions) from ignored paths ([Ignore file](https://cursor.com/docs/reference/ignore-file)).

### Inline Edit (“Apply” / Cmd-K)

- Inline Edit: `Cmd/Ctrl+K`; accept/reject suggested changes via chat shortcuts (`Cmd/Ctrl+Return` accept all, reject all) ([Keyboard Shortcuts](https://cursor.com/docs/reference/keyboard-shortcuts)).
- User Rules apply to Agent (Chat) only — **not** Inline Edit ([Rules FAQ](https://cursor.com/docs/rules)).

### Agent modes

| Mode | Role | Official entry |
| --- | --- | --- |
| **Agent** | Full tools: search, edit, shell, browser, etc. | [Agent overview](https://cursor.com/docs/agent/overview) |
| **Plan** | Research → clarify → editable plan → build on approval | [Plan Mode](https://cursor.com/docs/agent/plan-mode) |
| **Ask** | Read-only exploration | [CLI using](https://cursor.com/docs/cli/using) |
| **Debug** | Hypotheses → instrument → reproduce → evidence-based fix | [Agent best practices](https://cursor.com/blog/agent-best-practices) |

- Switch modes: mode picker or `Shift+Tab` ([Plan Mode](https://cursor.com/docs/agent/plan-mode), [Keyboard Shortcuts](https://cursor.com/docs/reference/keyboard-shortcuts)).
- Agent components: Instructions (system + rules) · Tools · Model ([Agent overview](https://cursor.com/docs/agent/overview)).
- Speed-adjacent Agent UX: **checkpoints** (local rollback of agent edits), **message queue** / steer-at-next-tool-call, **side chats** (`/side`, `/btw`), **`/goal`** long-lived objectives ([Agent overview](https://cursor.com/docs/agent/overview), [Changelog 2026-08-19](https://cursor.com/changelog/08-19-26)).

### Max Mode

- Available on **legacy request-based plans** only; extends context beyond default; billed at **API rate + 20%** ([Models & Pricing](https://cursor.com/docs/models-and-pricing), [Request-based legacy](https://cursor.com/docs/account/pricing/request-based-legacy)).
- On legacy plans, Max Mode also gates **larger context, subagents, image generation, and some frontier models** ([Request-based legacy](https://cursor.com/docs/account/pricing/request-based-legacy)).
- Many current models note “Requires Max Mode on legacy request-based plans” in the model table ([Docs home / models table](https://cursor.com/docs)).
- CLI: `/max-mode` toggle on legacy plans ([CLI slash commands](https://cursor.com/docs/cli/reference/slash-commands)).

### Cloud Agents (formerly Background Agents)

- Isolated cloud VMs; parallel agents; laptop can go offline ([Cloud Agents](https://cursor.com/docs/cloud-agent)).
- Formerly named Background Agents ([Cloud Agents — Naming History](https://cursor.com/docs/cloud-agent)).
- Access: desktop Cloud dropdown, [cursor.com/agents](https://cursor.com/agents), Slack/GitHub/`@cursor`, Linear, API, iOS ([Cloud Agents](https://cursor.com/docs/cloud-agent)).
- Environment setup (`.cursor/environment.json`, snapshots, Dockerfile) is called out as **the most important** effectiveness lever ([Cloud Agents](https://cursor.com/docs/cloud-agent)).
- Supports MCP, repo hooks (not `~/.cursor/hooks.json`), computer use / remote desktop, multi-repo (with limits) ([Cloud Agents](https://cursor.com/docs/cloud-agent), [Capabilities](https://cursor.com/docs/cloud-agent/capabilities)).
- **Does not support multi-root workspaces** ([Search FAQ](https://cursor.com/docs/agent/tools/search)).
- Billed at selected model API pricing; larger context increases cost ([Cloud Agents](https://cursor.com/docs/cloud-agent)).

### Parallelism / worktrees / Projects

- Git **worktrees** for parallel local agents; Apply merges back ([Agent best practices](https://cursor.com/blog/agent-best-practices)).
- Run **same prompt on multiple models**, compare side-by-side ([Agent best practices](https://cursor.com/blog/agent-best-practices)).
- **Subagents** (Explore / Bash / Browser + custom) isolate noisy context; Explore uses a **faster model** and can run many parallel searches ([Subagents](https://cursor.com/docs/subagents), [Search](https://cursor.com/docs/agent/tools/search)).
- Cloud subagents on own VMs ([Changelog 2026-08-19](https://cursor.com/changelog/08-19-26)).
- **Projects**: coordinator plans and delegates to many agents; shared project context; cloud-backed ([Changelog 2026-09-10](https://cursor.com/changelog)).

### MCP

- Connect external tools/data; stdio / SSE / Streamable HTTP ([MCP](https://cursor.com/docs/mcp)).
- Project: `.cursor/mcp.json`; global: `~/.cursor/mcp.json` ([MCP](https://cursor.com/docs/mcp)).
- Auto-used when relevant (including Plan Mode); toggle servers in Customize to reduce clutter ([MCP](https://cursor.com/docs/mcp)).
- Dynamic MCP tool discovery: tool descriptions synced to folders; A/B showed **~46.9% fewer tokens** on runs that called MCP ([Dynamic context discovery](https://cursor.com/blog/dynamic-context-discovery)).

### Rules / Skills (speed via context)

- Rules = static prompt context when applied ([Rules](https://cursor.com/docs/rules)).
- Skills = progressive / on-demand packages; agent discovers or `/skill-name`; Custom Mode pins a skill for the session ([Skills](https://cursor.com/docs/skills), [Changelog Custom modes](https://cursor.com/changelog/08-19-26)).
- `/migrate-to-skills` converts “Apply Intelligently” rules and slash commands → skills; Always/globs rules are **not** migrated ([Skills](https://cursor.com/docs/skills)).

### Context / search / indexing hygiene

- Instant Grep: local fast search engine for Agent ([Search](https://cursor.com/docs/agent/tools/search)).
- Ignore Settings live under **Indexing → Ignore Files** (Hierarchical Cursor Ignore as of 3.11) ([Ignore file](https://cursor.com/docs/reference/ignore-file)).
- `.cursorignore`: security + **performance in large monorepos** — exclude irrelevant portions for more accurate discovery ([Ignore file](https://cursor.com/docs/reference/ignore-file)).
- Default ignores already cover `node_modules/`, lockfiles, `.env*`, media, caches, etc. ([Ignore file](https://cursor.com/docs/reference/ignore-file)).
- Caveat: terminal and MCP tools **cannot** be blocked by `.cursorignore` ([Ignore file](https://cursor.com/docs/reference/ignore-file)).

### Model selection / Fast modes / pools

- Two usage pools: **Cursor Models** (Grok / Composer) vs **Other Models** (third-party at API rates) ([Models & Pricing](https://cursor.com/docs/models-and-pricing)).
- Fast variants exist for several models (often **higher $/token**, lower latency) — e.g. GPT Fast “faster speed but 2x price”; Composer/Grok Fast rows in pricing table ([Models & Pricing](https://cursor.com/docs/models-and-pricing)).
- **Cursor Router** (Teams/Enterprise): Auto → Cost / Balance / Intelligence ([Cursor Router](https://cursor.com/docs/cursor-router), [Introducing Cursor Router](https://cursor.com/blog/router)).
- Power-user spend guidance: multiple agents/automation often **$200+/mo** total usage ([Models & Pricing](https://cursor.com/docs/models-and-pricing)).

---

## 2. Official quality guidance

### Rules

- Keep rules **under 500 lines**; split large rules; concrete examples; **reference files** instead of pasting contents ([Rules — Best practices](https://cursor.com/docs/rules)).
- Avoid: full style guides (use linters), documenting every CLI, rare edge cases, duplicating code already in repo ([Rules](https://cursor.com/docs/rules)).
- Start simple; add rules only when Agent **repeats the same mistake** ([Rules](https://cursor.com/docs/rules), [Agent best practices](https://cursor.com/blog/agent-best-practices)).
- Application types: Always / Intelligent (description) / Globs / Manual `@` ([Rules](https://cursor.com/docs/rules)).
- Precedence: Team → Project → User ([Rules](https://cursor.com/docs/rules)).
- Nested `AGENTS.md` in subdirectories; more specific wins when combined ([Rules](https://cursor.com/docs/rules)).

### Skills vs long rules

- Official framing: **Rules = static always-in context**; **Skills = dynamic when relevant** — keeps context window clean ([Agent best practices](https://cursor.com/blog/agent-best-practices), [Dynamic context discovery](https://cursor.com/blog/dynamic-context-discovery)).
- Skills can ship `scripts/`, `references/`, `assets/`; load references on demand ([Skills](https://cursor.com/docs/skills)).
- Prefer Skills for single-purpose workflows; prefer Subagents when you need **context isolation / parallel / multi-step expertise** ([Subagents](https://cursor.com/docs/subagents)).
- Pin a skill as Custom Mode (`Alt+Enter` / `⌥⏎`) when the whole session must follow a playbook ([Skills](https://cursor.com/docs/skills)).

### Plan Mode

- Best for multi-file / unclear / architectural work; skip for quick familiar edits ([Plan Mode](https://cursor.com/docs/agent/plan-mode)).
- If build diverges: **revert → refine plan → rebuild** often faster than chat-fixing ([Plan Mode](https://cursor.com/docs/agent/plan-mode), [Agent best practices](https://cursor.com/blog/agent-best-practices)).
- Save plans to workspace (`.cursor/plans/`) for team/docs/resume ([Agent best practices](https://cursor.com/blog/agent-best-practices)).

### Prompts / context hygiene (quality)

- Don’t `@`-spam irrelevant files — confuses what’s important ([Agent best practices](https://cursor.com/blog/agent-best-practices)).
- Let Agent search; tag a file only if you know it ([Agent best practices](https://cursor.com/blog/agent-best-practices)).
- New chat when task changes / agent confused / unit done; continue when iterating same feature ([Agent best practices](https://cursor.com/blog/agent-best-practices)).
- Long chats + summarization accumulate noise → effectiveness drops ([Agent best practices](https://cursor.com/blog/agent-best-practices)).
- Prefer `@Chats` over pasting whole prior transcripts ([Agent best practices](https://cursor.com/blog/agent-best-practices)).
- Specific prompts beat vague ones; give verifiable goals (types, linters, tests) ([Agent best practices](https://cursor.com/blog/agent-best-practices)).

### Review / Bugbot

- Local: Review → Find Issues; Source Control Agent Review vs main ([Agent best practices](https://cursor.com/blog/agent-best-practices)).
- Built-in skills: `/review`, `/review-bugbot`, `/review-security` ([Skills](https://cursor.com/docs/skills)).
- **Bugbot**: auto PR review on updates; manual `cursor review` / `bugbot run`; reads existing PR comments; Fix in Cursor / Web ([Bugbot](https://cursor.com/docs/bugbot)).
- Included on Pro+ plans (not Start) ([Models & Pricing](https://cursor.com/docs/models-and-pricing)).
- Bugbot + MCP for review tools: Team/Enterprise ([Bugbot](https://cursor.com/docs/bugbot)).

### Memories

- Documented primarily for **Automations**: persistent named entries (default `MEMORIES.md`) across runs; on by default; **caution with untrusted input** (poisoned memories) ([Automations](https://cursor.com/docs/cloud-agent/automations)).
- Rules explicitly: LLMs don’t retain memory between completions — rules supply persistence ([Rules](https://cursor.com/docs/rules)).
- Projects maintain shared evolving context files across agents ([Changelog Projects](https://cursor.com/changelog)).

### Autonomy without approval fatigue

- **Auto-review**: classifier reviews tool calls in context; blocks ~4% of reviewed actions; ~7% of chats get a user interrupt; default for new users ([Auto-review blog](https://cursor.com/blog/agent-autonomy-auto-review)).
- MCP follows same Run Modes as terminal (e.g. Auto-review allowlists) ([MCP](https://cursor.com/docs/mcp)).

---

## 3. Levers that multiply throughput without tanking quality

Ordered by how strongly official sources support them for a **heavy-rules monorepo**:

1. **Shrink always-on context; push playbooks into Skills**  
   Static rules burn tokens every turn; skills discover dynamically ([Dynamic context discovery](https://cursor.com/blog/dynamic-context-discovery), [Agent best practices](https://cursor.com/blog/agent-best-practices)). Use `/migrate-to-skills` for intelligent rules ([Skills](https://cursor.com/docs/skills)).

2. **`.cursorignore` the monorepo junk**  
   Explicit performance reason for large codebases ([Ignore file](https://cursor.com/docs/reference/ignore-file)). Keep secrets out of Agent/Tab/`@` (terminal/MCP still bypass).

3. **Plan Mode for multi-file Laravel features; Agent for 1-file Quick**  
   Official “when to use” matches Quick vs Standard triage ([Plan Mode](https://cursor.com/docs/agent/plan-mode)).

4. **Subagents for search / shell noise**  
   Explore keeps main thread lean; faster model + parallel searches ([Subagents](https://cursor.com/docs/subagents), [Search](https://cursor.com/docs/agent/tools/search)).

5. **Model routing instead of one frontier daily driver**  
   ~60% of users pin one model → overpay for routine work ([Introducing Cursor Router](https://cursor.com/blog/router)). Compass + task taxonomy route simple → efficient, hard → frontier ([How Cursor Router works](https://cursor.com/blog/how-cursor-router-works)).  
   *Note: Router is Teams/Enterprise* ([Cursor Router](https://cursor.com/docs/cursor-router)). Individuals: manual ladder (Composer/Grok for routine; Sonnet/Opus/Sol/Fable for hard; Fast when latency > cost).

6. **Parallel worktrees / multi-model / Cloud Agents for independent slices**  
   Best-of-N and isolated sandboxes improve hard-task quality ([Agent best practices](https://cursor.com/blog/agent-best-practices)); Cloud for async todo-list work ([Cloud Agents](https://cursor.com/docs/cloud-agent)).

7. **MCP: enable only what you need; trust dynamic discovery**  
   Many MCP tools bloated prompts; folder-based discovery cut tokens ~47% on MCP-using runs ([Dynamic context discovery](https://cursor.com/blog/dynamic-context-discovery)). Disable unused servers in Customize ([MCP FAQ](https://cursor.com/docs/mcp)).

8. **Fresh chats + checkpoints + plan restart**  
   Avoid lossy long-thread degradation ([Agent best practices](https://cursor.com/blog/agent-best-practices)); checkpoints for exploratory rollback ([Agent overview](https://cursor.com/docs/agent/overview)).

9. **Verifiable loops (TDD / linters / Bugbot)**  
   Agents iterate best against tests; review catches “looks right, subtly wrong” ([Agent best practices](https://cursor.com/blog/agent-best-practices)).

10. **Custom Mode = session-scoped “always-on skill”**  
    Spec-first / caveman / review playbooks without forever-polluting global Always rules ([Skills](https://cursor.com/docs/skills), [Changelog](https://cursor.com/changelog/08-19-26)).

---

## 4. Concrete “10x OS” concept stack (for this repo shape)

### Principles (map to Cursor first-party design)

| Principle | Cursor-native mechanism |
| --- | --- |
| Thin static, rich dynamic | Few Always rules + many Skills (`paths` / nested package skills) |
| Plan hard, ship small | Plan Mode → build; Agent for Quick |
| Context is a budget | New chats; `@` sparingly; Explore subagent; ignore vendor noise |
| Parallel without collision | Worktrees / Cloud Agents / cloud subagents |
| Right model per turn | Auto Balance/Intelligence (Teams) or explicit ladder |
| Quality gates outside the model | Linters, Pest, Bugbot, `/review*` |
| Autonomy with intent | Auto-review + allowlists; not “approve every shell” |

### Workflows (daily)

1. **Quick (1 file / typo / config)** → Agent + fast/cheap model → Tab polish → done.  
2. **Standard feature** → Plan Mode → edit plan → Build → Agent Review → PR → Bugbot.  
3. **Explore-only** → Ask mode or Explore subagent; no edits.  
4. **Hard / ambiguous bug** → Debug Mode or Plan; evidence before patch ([Agent best practices](https://cursor.com/blog/agent-best-practices)).  
5. **Backlog / while AFK** → Cloud Agent with good `environment.json` ([Cloud Agents](https://cursor.com/docs/cloud-agent)).  
6. **Playbook session** → `/skill` as Custom Mode (e.g. spec-first) for that chat only ([Skills](https://cursor.com/docs/skills)).  
7. **Long objective** → `/goal` + optional `/loop` ([Agent overview](https://cursor.com/docs/agent/overview), [Changelog](https://cursor.com/changelog/08-19-26)).  
8. **Large migration** → Projects coordinator (beta) ([Changelog](https://cursor.com/changelog)).

### Settings / repo layout checklist

- [ ] Audit Always-apply `.mdc` rules → keep only cross-cutting invariants; migrate playbooks via `/migrate-to-skills` ([Skills](https://cursor.com/docs/skills)).
- [ ] Prefer `globs` / skill `paths` / nested `.cursor/skills` under packages for monorepo scoping ([Rules](https://cursor.com/docs/rules), [Skills](https://cursor.com/docs/skills)).
- [ ] Keep `AGENTS.md` short; use nested `AGENTS.md` per app package if needed ([Rules](https://cursor.com/docs/rules)).
- [ ] Root `.cursorignore`: `vendor/` noise beyond defaults, generated assets, large evidence dumps, unrelated example apps if not in scope ([Ignore file](https://cursor.com/docs/reference/ignore-file)).
- [ ] Global ignore for `**/.env*` / secrets ([Ignore file](https://cursor.com/docs/reference/ignore-file)).
- [ ] MCP: disable unused; prefer HTTP for Cloud Agents ([MCP](https://cursor.com/docs/mcp), [Cloud capabilities](https://cursor.com/docs/cloud-agent/capabilities)).
- [ ] Cloud: invest in environment build/secrets before expecting quality AFK agents ([Cloud Agents](https://cursor.com/docs/cloud-agent)).
- [ ] Enable Bugbot on active repos; optional fail-on-unresolved for branch protection ([Bugbot](https://cursor.com/docs/bugbot)).
- [ ] Auto-review on for local agent autonomy ([Auto-review](https://cursor.com/blog/agent-autonomy-auto-review)).
- [ ] Model picker: Cursor Models pool for volume; frontier for architecture; avoid Max/1M unless needed (cost warnings on large context in model notes) ([Models & Pricing](https://cursor.com/docs/models-and-pricing)).
- [ ] Teams: enable Cursor Router; prefer Balance for daily, Intelligence for hard waves ([Cursor Router](https://cursor.com/docs/cursor-router)).

### Suggested model routing (aligned with Cursor’s published taxonomy)

From Cursor’s router research (when not pinning a model yourself) ([How Cursor Router works](https://cursor.com/blog/how-cursor-router-works)):

- Routine / git / broad ops → price-efficient (Grok called out)
- Planning / codebase comprehension → Sol strengths
- Execution-heavy / devops / perf → Opus strengths  
- Hard debug / visual → Fable strengths  

Local power-user mirror without Router: **Composer/Grok for grind**, **Sol/Sonnet for plan+impl**, **Opus/Fable for gnarly**, **Fast** only when wall-clock matters more than $/token ([Models & Pricing](https://cursor.com/docs/models-and-pricing)).

---

## 5. Pitfalls Cursor docs/blog warn about

| Pitfall | What official sources say |
| --- | --- |
| **Context bloat from Always rules / MCP** | Dynamic discovery preferred; unused MCP tools inflate prompts ([Dynamic context discovery](https://cursor.com/blog/dynamic-context-discovery)); keep rules short ([Rules](https://cursor.com/docs/rules)). |
| **Over-tagging files** | Irrelevant `@` confuses priority ([Agent best practices](https://cursor.com/blog/agent-best-practices)). |
| **Long chats** | Summarization is lossy; agent loses focus ([Agent best practices](https://cursor.com/blog/agent-best-practices), [Dynamic context discovery](https://cursor.com/blog/dynamic-context-discovery)). |
| **One frontier model for everything** | Overpays; quality/$ worse than routing ([Introducing Cursor Router](https://cursor.com/blog/router)). |
| **Huge context windows casually** | e.g. Claude 4 Sonnet 1M “can be very expensive”; cost 2x over 200k; GPT long context often 2x input ([Models table](https://cursor.com/docs)). |
| **Max Mode as default** | Legacy +20% surcharge; not the modern default path ([Models & Pricing](https://cursor.com/docs/models-and-pricing)). |
| **Cloud Agent without environment** | “Like not giving engineers a computer” ([Cloud Agents](https://cursor.com/docs/cloud-agent)). |
| **Expecting `.cursorignore` to secure shells/MCP** | Explicitly does **not** block terminal/MCP ([Ignore file](https://cursor.com/docs/reference/ignore-file)). |
| **Rules for Tab/Inline Edit** | Rules don’t apply to Tab; User Rules don’t apply to Cmd-K ([Rules FAQ](https://cursor.com/docs/rules)). |
| **Chat-fixing a bad build** | Prefer revert + better plan ([Plan Mode](https://cursor.com/docs/agent/plan-mode)). |
| **Approval spam** | Over-prompting trains users to click through; Auto-review is the intended dial ([Auto-review](https://cursor.com/blog/agent-autonomy-auto-review)). |
| **Automation memories + untrusted input** | Risk of malicious/misleading memories ([Automations](https://cursor.com/docs/cloud-agent/automations)). |
| **Subagent for trivial one-shots** | Use a Skill instead ([Subagents](https://cursor.com/docs/subagents)). |
| **Multi-root + Cloud / worktrees** | Cloud Agents lack multi-root; some single-git-root features disabled in multi-root ([Search FAQ](https://cursor.com/docs/agent/tools/search)). |
| **Skipping human review** | Faster agents make review *more* important ([Agent best practices](https://cursor.com/blog/agent-best-practices)). |

---

## Mapping to an existing heavy-rules Laravel monorepo

*(Synthesis of official guidance applied to this profile — not a Cursor claim.)*

- Your stack (spec-first, caveman, ponytail, model routing) already mirrors Cursor’s Plan / thin-context / Skills / Router story.
- Highest official-aligned win: **cut Always-apply surface area**; put phase playbooks in **Skills + Custom Modes**; keep Always for hard invariants only.
- Monorepo: **`.cursorignore` + nested skills/AGENTS.md** beat one mega rule file.
- Don’t expect Tab to honor AGENTS.md/rules — use Agent for policy-heavy work.
- Parallel Cloud/worktree agents only help if slices are independent and environments/tests exist.

---

## Sources

All URLs visited or used for claims in this note:

1. https://cursor.com/docs  
2. https://docs.cursor.com  
3. https://cursor.com/docs/agent/overview  
4. https://cursor.com/docs/agent/plan-mode  
5. https://cursor.com/docs/agent/tools/search  
6. https://cursor.com/docs/rules  
7. https://cursor.com/docs/skills  
8. https://cursor.com/docs/skills.md  
9. https://cursor.com/docs/models-and-pricing  
10. https://cursor.com/docs/models-and-pricing.md  
11. https://cursor.com/docs/cursor-router  
12. https://cursor.com/docs/mcp  
13. https://cursor.com/docs/mcp.md  
14. https://cursor.com/docs/bugbot  
15. https://cursor.com/docs/bugbot.md  
16. https://cursor.com/docs/cloud-agent  
17. https://cursor.com/docs/cloud-agent/capabilities  
18. https://cursor.com/docs/cloud-agent/automations  
19. https://cursor.com/docs/subagents  
20. https://cursor.com/docs/reference/ignore-file  
21. https://cursor.com/docs/reference/keyboard-shortcuts  
22. https://cursor.com/docs/reference/keyboard-shortcuts.md  
23. https://cursor.com/docs/hooks  
24. https://cursor.com/docs/cli/using  
25. https://cursor.com/docs/cli/overview  
26. https://cursor.com/docs/cli/reference/slash-commands  
27. https://cursor.com/docs/cli/reference/slash-commands.md  
28. https://cursor.com/docs/account/pricing/request-based-legacy  
29. https://cursor.com/docs/account/teams/pricing  
30. https://cursor.com/docs/sdk/typescript  
31. https://cursor.com/changelog  
32. https://cursor.com/changelog/08-19-26  
33. https://cursor.com/blog/agent-best-practices  
34. https://cursor.com/blog/dynamic-context-discovery  
35. https://cursor.com/blog/router  
36. https://cursor.com/blog/how-cursor-router-works  
37. https://cursor.com/blog/agent-autonomy-auto-review  
38. https://cursor.com/blog/self-hosted-cloud-agents  
39. https://cursor.com/agents (product surface referenced by docs)
