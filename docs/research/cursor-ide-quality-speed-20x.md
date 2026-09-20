# Cursor IDE — quality + speed 20x (primary-source research)

Date: 2026-09-20  
Audience: power user on a large Laravel monorepo; heavy skills; Always rules already thinned; intelligent rules → Skills migration done  
Builds on: [`docs/research/cursor-ide-quality-speed-10x.md`](./cursor-ide-quality-speed-10x.md) (do not redo that baseline — this note stacks **compound** levers on top)  
Method: official Cursor docs (`cursor.com/docs`, `docs.cursor.com`), changelog, blog, learn — primary sources only

## Verdict

**20x is not a toggle.** Cursor’s own research and product surface say the jump from “good solo agent” to “power-user OS” is **compound**: thin static context × dynamic Skills/MCP × Plan (or Projects coordinator) × parallel isolated workers (worktrees / cloud subagents / `/multitask`) × planner≠worker model mix × verifiable loops (tests + Bugbot + review lenses) × Auto-review autonomy. Official swarm economics: same quality, **~8× cost swing** between “frontier everywhere” and “frontier plans, cheap workers” ([Agent swarm model economics](https://cursor.com/blog/agent-swarm-model-economics)). Router: frontier-like satisfaction at **~60–68% lower cost** vs pinning Opus ([Introducing Cursor Router](https://cursor.com/blog/router), [How Cursor Router works](https://cursor.com/blog/how-cursor-router-works)). MCP dynamic discovery: **~46.9% fewer tokens** on MCP-using runs ([Dynamic context discovery](https://cursor.com/blog/dynamic-context-discovery)). Multiply those with parallel lanes and fewer wasted long chats — that is the honest “20x” story: **shipped green PRs per week**, not bigger Max Mode.

---

## Relationship to the 10x note

| 10x (already covered) | 20x (this note) |
| --- | --- |
| Thin Always rules; Skills; Plan for hard; Explore; `.cursorignore`; basic worktrees/Cloud; Router ladder; Bugbot; Auto-review intro | **Stacks** those into explicit multiplier recipes |
| Single-agent best practices | **Projects coordinator**, `/multitask`, cloud-VM subagents, swarm planner/worker economics |
| “Don’t bloat context” | Extreme-scale layout: nested Skills/`paths`, nested `AGENTS.md`, Field Guide–style shared project context, dynamic tools |
| Pitfalls table | Harder “does not scale” list + measurable proxies |

Read the 10x note first for feature inventory; use this note for **operating system** design.

---

## 1. Compound multipliers (official stacks that multiply)

Cursor does not publish a single “20×” product claim. The multipliers below are **composable product facts** and **published A/B / research numbers**. Stack only independent axes (context × parallelism × model mix × verification × autonomy).

### Multiplier table (order of ops for a monorepo power user)

| Stack | What multiplies | Primary evidence |
| --- | --- | --- |
| **A. Thin static × dynamic discovery** | Tokens per turn; less contradictory guidance → better decisions | Skills + MCP folder discovery; ~46.9% token cut on MCP runs ([Dynamic context discovery](https://cursor.com/blog/dynamic-context-discovery), [Skills](https://cursor.com/docs/skills)) |
| **B. Plan / Projects coordinator × cheap workers** | Wall-clock on large work; $/task | Planner never implements; workers grind ([Projects](https://cursor.com/changelog/projects), [Agent swarm model economics](https://cursor.com/blog/agent-swarm-model-economics), [Scaling agents](https://cursor.com/blog/scaling-agents)) |
| **C. Parallel isolation × independent slices** | Concurrent lanes without merge thrash | Worktrees; `/multitask`; cloud subagents on own VMs ([Best practices](https://cursor.com/blog/agent-best-practices), [Changelog 04-24-26](https://cursor.com/changelog/04-24-26), [Changelog 08-19-26](https://cursor.com/changelog/08-19-26), [Subagents](https://cursor.com/docs/subagents)) |
| **D. Explore (fast model) × main thread lean** | Search throughput without context death | Explore: faster model, many parallel searches; parent only gets summary ([Subagents](https://cursor.com/docs/subagents), [Search](https://cursor.com/docs/agent/tools/search)) |
| **E. Router / hybrid model mix** | Quality/$ and quality/latency | Auto Intelligence ~Fable satisfaction @ ~60–68% lower cost; hybrid Opus planner + Composer workers ~$1.3k vs ~$10.5k all-frontier ([Router](https://cursor.com/blog/router), [How Router works](https://cursor.com/blog/how-cursor-router-works), [Swarm economics](https://cursor.com/blog/agent-swarm-model-economics)) |
| **F. Verifiable goals × agent loop** | Fewer “looks right” retries | TDD / linters / tests as iteration target ([Best practices](https://cursor.com/blog/agent-best-practices)); Bugbot on PRs ([Bugbot](https://cursor.com/docs/bugbot)); stacked review lenses in swarm research ([Swarm economics](https://cursor.com/blog/agent-swarm-model-economics)) |
| **G. Auto-review × fewer approval stops** | Effective agent minutes / hour | ~4% of reviewed actions blocked; ~7% of chats interrupted; vs ~40% action blocks in some enterprises ([Auto-review](https://cursor.com/blog/agent-autonomy-auto-review)) |
| **H. Custom Mode skill pin × session** | Playbook fidelity without Always-rule bloat | Skill as Custom Mode = “always on” for that chat ([Changelog 08-19-26](https://cursor.com/changelog/08-19-26), [Skills](https://cursor.com/docs/skills)) |
| **I. `/goal` + subscriptions + CI autofix** | Async close rate | Long-lived goals; PR/Slack/timer subscriptions; Cloud Agents auto-fix CI (Teams) ([Agent overview](https://cursor.com/docs/agent/overview), [Capabilities](https://cursor.com/docs/cloud-agent/capabilities), [Changelog 08-19-26](https://cursor.com/changelog/08-19-26)) |

### Example compound recipes (Laravel monorepo)

1. **Standard feature (local):** Plan Mode → Build on Balance/Intelligence Auto → Explore for repo map → Agent + Pest/linter loop → Review → PR → Bugbot.  
2. **Independent backlog burst:** Cloud Agents with solid `.cursor/environment.json` × N parallel todos × PR subscriptions ([Cloud Agents](https://cursor.com/docs/cloud-agent)).  
3. **Hard multi-file migration:** Projects coordinator (beta) + shared project context files + parallel implementers + human check ([Projects](https://cursor.com/changelog/projects)).  
4. **Same hard bug, quality boost:** Multi-model / multi-worktree best-of-N, pick winner ([Best practices](https://cursor.com/blog/agent-best-practices)).  
5. **Spec session without Always pollution:** `/skill` as Custom Mode (spec-first) for that chat only ([Skills](https://cursor.com/docs/skills)).

**Honest math:** if A saves ~2× tokens, E saves ~2× $/quality-equivalent, C runs 3–5 independent lanes, F cuts redo cycles ~2× — product can land in “~20× effective output” territory for a disciplined power user. Cursor’s published numbers support the **factors**, not a guaranteed product of all of them on every day.

---

## 2. Projects / multi-agent / worktrees / cloud — limits and best setup

### Projects (beta, Sep 2026)

- Coordinator **does not write code**; plans, delegates to many agents, returns finished work for human check ([Projects](https://cursor.com/changelog/projects)).
- Marketing/docs language: delegates to **thousands** of subagents; runs as many in parallel as the work needs ([Projects](https://cursor.com/changelog/projects)).
- Runs on **cloud computer**; laptop can close; can spin **local** agent when machine-local testing needed ([Projects](https://cursor.com/changelog/projects)).
- **Shared context files** sync across cloud/local agents; agents add research/artifacts/preferences; grows over months ([Projects](https://cursor.com/changelog/projects)).
- **Subscriptions:** Slack channel, schedule, follow all PRs — act without new prompts ([Projects](https://cursor.com/changelog/projects)).
- Agent overview points large bodies of work (feature/migration) at Projects ([Agent overview](https://cursor.com/docs/agent/overview)).

**Best setup for this repo shape:** one Project per long-lived initiative (e.g. Direct Book wave, migration), not per tiny ticket. Seed shared context with how to run Pest, Herd/Git Bash, product scope — same spirit as Cursor’s Field Guide (agent-curated shared notes with a line budget) ([Swarm economics](https://cursor.com/blog/agent-swarm-model-economics)).

### Worktrees / `/multitask` / multi-root

- Native worktrees: isolated files/changes; Apply merges back ([Best practices](https://cursor.com/blog/agent-best-practices)).
- Agents Window: worktrees for background branches; bring to foreground to test ([Changelog 04-24-26](https://cursor.com/changelog/04-24-26)).
- `/multitask`: async subagents parallelize instead of queue; can break large tasks into fleet chunks ([Changelog 04-24-26](https://cursor.com/changelog/04-24-26)).
- Multi-root workspaces: one agent session across folders (FE/BE/libs) ([Changelog 04-24-26](https://cursor.com/changelog/04-24-26)).
- **Limit:** multi-root disables some single-git-root features (e.g. worktrees); **Cloud Agents do not support multi-root** ([Search FAQ](https://cursor.com/docs/agent/tools/search)).

### Subagents + cloud subagents

- Built-ins: Explore / Bash / Browser — context isolation + specialized models/tools ([Subagents](https://cursor.com/docs/subagents)).
- Explore default: **faster model**; “10 parallel searches” in time of one main-agent search ([Subagents](https://cursor.com/docs/subagents)).
- Custom: `.cursor/agents/`; `model`, `readonly`, `is_background`; model params e.g. `composer-2.5[fast=false]`, `claude-opus-5[effort=high,context=300k]` ([Subagents](https://cursor.com/docs/subagents)).
- Isolation: ask for own environment → worktree or dedicated cloud VM/branch ([Subagents](https://cursor.com/docs/subagents)).
- Cloud: `/in-cloud`, `/autopilot` for PR remote grind; MCP from team `cursor.com/agents` config, not local ([Subagents](https://cursor.com/docs/subagents)).
- Cloud VM subagents (Aug 2026): swarm fixes/tests without collisions ([Changelog 08-19-26](https://cursor.com/changelog/08-19-26)).
- **Cost warning:** N parallel subagents ≈ N× tokens; overhead for trivial work — use a Skill instead ([Subagents](https://cursor.com/docs/subagents)).

### Cloud Agents (product)

- “Run as many as you want in parallel”; offline laptop OK ([Cloud Agents](https://cursor.com/docs/cloud-agent)).
- Environment setup = **most important** effectiveness lever ([Cloud Agents](https://cursor.com/docs/cloud-agent)).
- Multi-repo OK with limits; **long-running not available for multi-repo yet** ([Cloud Agents](https://cursor.com/docs/cloud-agent)).
- Repo hooks (`.cursor/hooks.json`) yes; `~/.cursor/hooks.json` **no** ([Cloud Agents](https://cursor.com/docs/cloud-agent), [Hooks](https://cursor.com/docs/hooks)).
- Artifacts + remote desktop for verify without local checkout ([Capabilities](https://cursor.com/docs/cloud-agent/capabilities)).
- CI autofix on agent-created PRs (GitHub Actions; Teams; max 10 follow-ups; skip if human pushed) ([Capabilities](https://cursor.com/docs/cloud-agent/capabilities)).
- Subscriptions last ≤ **180 days**; coalesce bursts ([Capabilities](https://cursor.com/docs/cloud-agent/capabilities)).
- Billing: model API rates; larger context → more cost; spend limit on first use ([Cloud Agents](https://cursor.com/docs/cloud-agent)).
- Power-user spend guidance: multiple agents/automation often **$200+/mo** ([Models & Pricing](https://cursor.com/docs/models-and-pricing)).

### Research → product mapping (coordinator pattern)

Cursor’s internal swarm work (planners / workers / judge, shared design docs, review lenses, Field Guide) is research that **informs** product ([Scaling agents](https://cursor.com/blog/scaling-agents), [Self-driving codebases](https://cursor.com/blog/self-driving-codebases), [Swarm economics](https://cursor.com/blog/agent-swarm-model-economics)). Projects + cloud subagents + shared project files are the shipping surface that most closely matches that architecture for end users ([Projects](https://cursor.com/changelog/projects)).

**Failure modes to design around (from Cursor research, not just folklore):** split-brain design, planner contention, merge conflicts, megafiles, ossification ([Swarm economics](https://cursor.com/blog/agent-swarm-model-economics)). Flat self-coordinating agents with locks → throughput collapse ([Scaling agents](https://cursor.com/blog/scaling-agents)). Prefer hierarchy + isolation + human check over “20 agents one branch.”

---

## 3. Hooks, Auto-review, Bugbot, verifiable loops — quality at speed

### Hooks (policy without chat friction)

- Observe/block/modify agent loop via `hooks.json` (project or user); JSON over stdio ([Hooks](https://cursor.com/docs/hooks)).
- Speed/quality uses: format after edits; gate risky shell/SQL; inject session context; control Task/subagent start/stop; `preCompact` observe ([Hooks](https://cursor.com/docs/hooks)).
- Cloud: command-based repo hooks only; no user-home hooks; no Tab/workspaceOpen; deferred sessionStart/MCP hooks in early read-only turns ([Hooks](https://cursor.com/docs/hooks)).
- Pair with Skills: `/create-hook`; partner integrations for secrets/security ([Skills](https://cursor.com/docs/skills), [Hooks](https://cursor.com/docs/hooks)).

### Auto-review (autonomy dial)

- Classifier reviews tool calls in context; lenient when stakes low ([Auto-review](https://cursor.com/blog/agent-autonomy-auto-review)).
- Blocks ~**4%** of reviewed actions; parent often recovers without user; ~**7%** of chats get ≥1 interrupt ([Auto-review](https://cursor.com/blog/agent-autonomy-auto-review)).
- Default for new users; enable under Settings → Agents ([Auto-review](https://cursor.com/blog/agent-autonomy-auto-review)).
- Local desktop focus today; approval spam trains click-through — Auto-review is the intended alternative ([Auto-review](https://cursor.com/blog/agent-autonomy-auto-review)).

### Bugbot + local review

- Auto PR review; `cursor review` / `bugbot run`; Fix in Cursor / Web ([Bugbot](https://cursor.com/docs/bugbot)).
- Branch protection: require check; findings often `neutral` unless fail-on-unresolved enabled ([Bugbot](https://cursor.com/docs/bugbot)).
- Local: Review → Find Issues; Source Control Agent Review vs main ([Best practices](https://cursor.com/blog/agent-best-practices)).
- Built-ins: `/review`, `/review-bugbot`, `/review-security` ([Skills](https://cursor.com/docs/skills)).
- Swarm research: **decorrelated review lenses stack**; review compute ≪ redo cost ([Swarm economics](https://cursor.com/blog/agent-swarm-model-economics)).

### Verifiable loops

- Agents iterate best against tests/types/linters; give explicit TDD stages ([Best practices](https://cursor.com/blog/agent-best-practices)).
- Custom **verifier** subagent pattern: skeptical, run tests, report incomplete claims ([Subagents](https://cursor.com/docs/subagents)).
- Orchestrator pattern: Planner → Implementer → Verifier handoffs ([Subagents](https://cursor.com/docs/subagents)).
- Cloud: computer use + artifacts close the “did it actually work?” loop ([Capabilities](https://cursor.com/docs/cloud-agent/capabilities)).
- Faster agents make human review **more** important ([Best practices](https://cursor.com/blog/agent-best-practices)).

### Long-running quality harnesses

- `/goal` until complete; Custom Mode playbook; `/loop` recurring ([Agent overview](https://cursor.com/docs/agent/overview), [Changelog 08-19-26](https://cursor.com/changelog/08-19-26)).
- `/autopilot` cloud PR grind ([Subagents](https://cursor.com/docs/subagents), [Skills](https://cursor.com/docs/skills)).
- Plan diverge → **revert + refine plan + rebuild** beats chat-fixing ([Plan Mode](https://cursor.com/docs/agent/plan-mode)).

---

## 4. Cursor Router / model pools / Fast — throughput without quality cliff

### Two pools

- **Cursor Models:** Grok 4.6 / 4.5, Composer 2.5 (and Fast variants) — high included usage ([Models & Pricing](https://cursor.com/docs/models-and-pricing)).
- **Other Models:** third-party at API rates (+ Teams/Enterprise Cursor Token Rate $0.25/MTok on third-party) ([Models & Pricing](https://cursor.com/docs/models-and-pricing)).

### Fast variants (latency vs $)

| Example | Tradeoff (official notes) |
| --- | --- |
| Composer 2.5 Fast | Higher $/token than Composer 2.5 ([Models & Pricing](https://cursor.com/docs/models-and-pricing)) |
| Grok Fast rows | ~2× input vs non-Fast ([Models & Pricing](https://cursor.com/docs/models-and-pricing)) |
| GPT-5 Fast | “Faster speed but 2x price” ([Models & Pricing](https://cursor.com/docs/models-and-pricing)) |
| GPT-5.4 Fast | “15% faster with 2x pricing” ([Models & Pricing](https://cursor.com/docs/models-and-pricing)) |
| Opus 4.8 Fast | Exists; legacy Max gate; pricing vs Opus 4.7 Fast called out ([Models & Pricing](https://cursor.com/docs/models-and-pricing)) |

**Rule:** Fast for wall-clock-critical interactive turns; Composer/Grok non-Fast for volume grind; frontier for ambiguity.

### Router (Teams/Enterprise)

- Modes: **Cost / Balance / Intelligence** under Auto ([Cursor Router](https://cursor.com/docs/cursor-router)).
- Classifier on query, context, complexity, domain; you don’t pick the model per turn ([Cursor Router](https://cursor.com/docs/cursor-router), [Router blog](https://cursor.com/blog/router)).
- Published: ~60% of users pin one model → overpay ([Router blog](https://cursor.com/blog/router)); early access 30–50% savings vs all-Opus; online A/B frontier quality @ ~60% savings ([Router blog](https://cursor.com/blog/router)).
- Later: Auto Intelligence above Fable satisfaction @ **68%** lower cost; Auto Balance beats Opus satisfaction @ **41%** lower cost ([How Router works](https://cursor.com/blog/how-cursor-router-works)).
- Compass complexity predictor + taxonomy (domains/tasks/modifiers); Grok for efficient; Sol plan/comprehension; Opus execution-heavy; Fable debug/visual ([How Router works](https://cursor.com/blog/how-cursor-router-works)).
- Enterprise: enable Grok for router to work; blocking too many models hurts routing ([Cursor Router](https://cursor.com/docs/cursor-router)).
- Dynamic tool calling (native tools looked up on demand) pairs with Router for lean prompts ([Router blog](https://cursor.com/blog/router)).

### Individuals without Router

Mirror taxonomy manually ([How Router works](https://cursor.com/blog/how-cursor-router-works) + [Swarm economics](https://cursor.com/blog/agent-swarm-model-economics)):

- Grind / git / broad ops → Composer or Grok (Cursor pool).
- Plan + codebase comprehension → Sol-class.
- Execution-heavy / devops / perf → Opus-class.
- Gnarly debug / visual → Fable-class.
- Subagent Explore → leave on faster default ([Subagents](https://cursor.com/docs/subagents)).
- Custom subagent `model:` pin worker to Composer; parent on frontier for Plan ([Subagents](https://cursor.com/docs/subagents)).

### Hybrid economics (research, maps to product)

- Workers ≥69% of tokens (often >90%); planner dollars can still dominate if frontier ([Swarm economics](https://cursor.com/blog/agent-swarm-model-economics)).
- Opus planner + Composer workers: **$1,339** total vs GPT-5.5 everywhere **$10,565**; worker fleet $411 vs $9,373 ([Swarm economics](https://cursor.com/blog/agent-swarm-model-economics)).
- Fable planner can use fewer planner tokens but inflate worker tokens → worse total ([Swarm economics](https://cursor.com/blog/agent-swarm-model-economics)).
- Takeaway: **collapse ambiguity once** with a strong planner; don’t pay frontier for every leaf edit.

---

## 5. Context architecture at extreme scale

### Skills layout (monorepo)

- Discover roots: `.cursor/skills/`, `.agents/skills/`, user dirs; also `.claude`/`.codex` compat ([Skills](https://cursor.com/docs/skills)).
- Nested category folders OK; identity = folder with `SKILL.md` ([Skills](https://cursor.com/docs/skills)).
- **Nested package skills:** `apps/web/.cursor/skills/` auto-scoped to that tree (like `paths`) ([Skills](https://cursor.com/docs/skills)).
- Frontmatter: `paths`, `disable-model-invocation`, Custom Mode `icon`/`color` ([Skills](https://cursor.com/docs/skills)).
- Progressive: `scripts/`, `references/`, `assets/` load on demand ([Skills](https://cursor.com/docs/skills)).
- Cloud: Sync Skills for Cloud Agents copies `~/.cursor/skills/` only; project skills via repo ([Skills](https://cursor.com/docs/skills)).

### Nested AGENTS.md + thin Always rules

- Nested `AGENTS.md`; more specific wins when combined ([Rules](https://cursor.com/docs/rules)).
- Keep rules **&lt;500 lines**; reference files; don’t paste style guides ([Rules](https://cursor.com/docs/rules)).
- Always = invariants only; playbooks = Skills / Custom Modes ([Rules](https://cursor.com/docs/rules), [Best practices](https://cursor.com/blog/agent-best-practices)).
- Precedence: Team → Project → User ([Rules](https://cursor.com/docs/rules)).

### `.cursorignore`

- Performance reason for large monorepos: exclude irrelevant portions ([Ignore file](https://cursor.com/docs/reference/ignore-file)).
- Blocks Agent/Tab/Inline Edit/`@` — **not** terminal or MCP ([Ignore file](https://cursor.com/docs/reference/ignore-file)).
- Hierarchical ignore optional under Indexing → Ignore Files ([Ignore file](https://cursor.com/docs/reference/ignore-file)).
- Defaults already cover lockfiles, `node_modules/`, `.env*`, media, caches ([Ignore file](https://cursor.com/docs/reference/ignore-file)).

### Dynamic MCP + tools

- Tool descriptions synced to folders; names in static prompt; lookup on need; **~46.9%** token reduction when MCP used ([Dynamic context discovery](https://cursor.com/blog/dynamic-context-discovery)).
- Disable unused servers in Customize ([MCP](https://cursor.com/docs/mcp) — see 10x note).
- Cloud: prefer HTTP MCP (creds not in VM); stdio runs in VM ([Capabilities](https://cursor.com/docs/cloud-agent/capabilities)).

### Chat hygiene (still the #1 soft limit)

- New chat when task changes / agent confused / unit done ([Best practices](https://cursor.com/blog/agent-best-practices)).
- Long chats + summarization = lossy; effectiveness drops ([Best practices](https://cursor.com/blog/agent-best-practices), [Dynamic context discovery](https://cursor.com/blog/dynamic-context-discovery)).
- History-as-files helps recover after compact — still prefer fresh chats for new jobs ([Dynamic context discovery](https://cursor.com/blog/dynamic-context-discovery)).
- Don’t `@`-spam; let Agent search ([Best practices](https://cursor.com/blog/agent-best-practices)).
- Side chats `/side` `/btw` for tangents ([Agent overview](https://cursor.com/docs/agent/overview)).
- Projects shared files / Field Guide pattern = durable learning without stuffing every Always rule ([Projects](https://cursor.com/changelog/projects), [Swarm economics](https://cursor.com/blog/agent-swarm-model-economics)).

### Instant Grep + Explore

- Local Instant Grep for large codebases ([Search](https://cursor.com/docs/agent/tools/search)).
- Explore subagent keeps main context clean ([Search](https://cursor.com/docs/agent/tools/search)).

---

## 6. What Cursor explicitly says does NOT scale

| Anti-pattern | Official signal |
| --- | --- |
| **Max / casual 1M context as default** | Max = legacy +20%; Claude 4 Sonnet 1M “can be very expensive”; many long-context paths 2× over threshold ([Models & Pricing](https://cursor.com/docs/models-and-pricing), [Request-based legacy](https://cursor.com/docs/account/pricing/request-based-legacy)) |
| **Always-on rule / MCP bloat** | Dynamic discovery preferred; unused MCP tools inflate prompts ([Dynamic context discovery](https://cursor.com/blog/dynamic-context-discovery)); rules &lt;500 lines ([Rules](https://cursor.com/docs/rules)) |
| **Long chats as memory** | Summarization lossy; agent loses focus ([Best practices](https://cursor.com/blog/agent-best-practices)) |
| **One frontier model for everything** | ~60% pin one model; overpay; Router exists to fix ([Router blog](https://cursor.com/blog/router)) |
| **Frontier workers for every leaf** | Similar quality, huge $ swing; workers eat most tokens ([Swarm economics](https://cursor.com/blog/agent-swarm-model-economics)) |
| **Cloud Agent without environment** | “Like not giving engineers a computer” ([Cloud Agents](https://cursor.com/docs/cloud-agent)) |
| **Multi-root + Cloud / worktrees** | Cloud no multi-root; worktrees disabled in multi-root ([Search FAQ](https://cursor.com/docs/agent/tools/search)) |
| **Flat multi-agent on one checkout** | Collisions; thrash; locks kill throughput ([Scaling agents](https://cursor.com/blog/scaling-agents), [Subagents](https://cursor.com/docs/subagents)) |
| **Subagent for one-shot chores** | Use Skill ([Subagents](https://cursor.com/docs/subagents)) |
| **Chat-fixing a bad Plan build** | Revert → refine plan → rebuild ([Plan Mode](https://cursor.com/docs/agent/plan-mode)) |
| **Approval spam** | Trains click-through ([Auto-review](https://cursor.com/blog/agent-autonomy-auto-review)) |
| **Expecting `.cursorignore` to secure shell/MCP** | Explicitly does not ([Ignore file](https://cursor.com/docs/reference/ignore-file)) |
| **Rules on Tab / User Rules on Cmd-K** | Don’t apply ([Rules FAQ](https://cursor.com/docs/rules)) |
| **Skipping review because agents are fast** | Review becomes *more* important ([Best practices](https://cursor.com/blog/agent-best-practices)) |
| **Commit thrash as productivity** | Old swarm 68k commits / 70k conflicts vs new harness fewer commits, better grade ([Swarm economics](https://cursor.com/blog/agent-swarm-model-economics)) |
| **User hooks on Cloud** | Only repo/team/enterprise hooks ([Hooks](https://cursor.com/docs/hooks)) |

---

## 7. Concrete “20x OS” operating model

### Daily ritual

| When | Do |
| --- | --- |
| Start | One chat per job; pick mode (Ask / Plan / Agent / Debug); pin Custom Mode skill only if session needs playbook |
| Quick | Agent + Cursor-pool model; Tab polish; no Plan |
| Standard | Plan → edit plan → Build → Pest/linter → Agent Review → PR |
| Parallel | Independent slices only: worktrees or `/multitask` or Cloud; never 5 agents one file |
| Explore | Ask or Explore subagent; keep main thread for decisions |
| AFK / interrupt | Cloud Agent or `/in-cloud` / `/autopilot`; subscriptions for CI/review |
| End unit | New chat or checkpoint; don’t carry noise into next feature |

### Weekly ritual

1. Audit Always-apply surface (should still be thin after Skills migration).  
2. Prune MCP servers; confirm dynamic discovery still wins.  
3. Refresh Cloud `environment.json` / snapshot if CI/bootstrap drifted.  
4. Review Bugbot false-positive noise; tune fail-on-unresolved if org supports.  
5. Check usage: Cursor pool vs Other; Router Balance vs Intelligence (Teams).  
6. Promote repeated mistakes → Skill or nested `AGENTS.md`, not Always essay.  
7. For long initiatives: curate Project shared context (test recipes, Herd notes).

### Settings / repo checklist (20x layer)

- [ ] Always rules = hard invariants only; Skills + `paths` + nested package skills for the rest ([Skills](https://cursor.com/docs/skills), [Rules](https://cursor.com/docs/rules)).  
- [ ] Nested `AGENTS.md` per app package if monorepo dialects differ ([Rules](https://cursor.com/docs/rules)).  
- [ ] `.cursorignore` vendor/generated/out-of-scope examples; global `**/.env*` ([Ignore file](https://cursor.com/docs/reference/ignore-file)).  
- [ ] Auto-review on ([Auto-review](https://cursor.com/blog/agent-autonomy-auto-review)).  
- [ ] Repo `.cursor/hooks.json` for formatters / risky gates (Cloud-visible) ([Hooks](https://cursor.com/docs/hooks)).  
- [ ] Cloud environment + secrets before AFK agents ([Cloud Agents](https://cursor.com/docs/cloud-agent)).  
- [ ] Sync personal Skills for Cloud if needed ([Skills](https://cursor.com/docs/skills)).  
- [ ] Bugbot enabled; optional branch protection ([Bugbot](https://cursor.com/docs/bugbot)).  
- [ ] Teams: Router Auto Balance daily; Intelligence for hard waves; don’t block Grok ([Cursor Router](https://cursor.com/docs/cursor-router)).  
- [ ] Individuals: explicit planner≠worker ladder; Fast only when latency > $ ([Models & Pricing](https://cursor.com/docs/models-and-pricing)).  
- [ ] Prefer single-root + worktrees for parallel local; Cloud for true multi-agent AFK ([Search FAQ](https://cursor.com/docs/agent/tools/search)).  
- [ ] Custom verifier / security subagents for merge gates ([Subagents](https://cursor.com/docs/subagents)).  
- [ ] Projects for migrations/features spanning weeks ([Projects](https://cursor.com/changelog/projects)).

### Measurable proxies for “20x” (process metrics, not marketing)

Track week-over-week; aim for **compound improvement**, not one KPI spike.

| Proxy | What “better” looks like | Notes |
| --- | --- | --- |
| **Parallel green lanes** | Avg concurrent independent agents/worktrees finishing without collision | Cap when conflicts rise ([Swarm economics](https://cursor.com/blog/agent-swarm-model-economics)) |
| **Time-to-green PR** | Median hours from first agent turn → CI green | Include Bugbot + autofix ([Capabilities](https://cursor.com/docs/cloud-agent/capabilities)) |
| **Tokens / merged PR** (or $ / commit) | Down for same scope | Router reports cost/commit in research ([Router blog](https://cursor.com/blog/router)) |
| **Redo rate** | ↓ chats that abandon → new Plan rebuild | Aligns with Plan Mode guidance ([Plan Mode](https://cursor.com/docs/agent/plan-mode)) |
| **Always-rule lines in prompt** | Flat or down | Dynamic discovery thesis ([Dynamic context discovery](https://cursor.com/blog/dynamic-context-discovery)) |
| **Human interrupt rate** | Low under Auto-review (~7% chats) without unsafe actions | ([Auto-review](https://cursor.com/blog/agent-autonomy-auto-review)) |
| **Keep rate of agent code** | Higher fraction surviving in main | Cursor’s own Router eval metric ([Router blog](https://cursor.com/blog/router)) |
| **Cloud env boot success** | Agents start with deps/tests ready | Environment = effectiveness ([Cloud Agents](https://cursor.com/docs/cloud-agent)) |

**Not proxies:** raw commit count, Max Mode hours, Always-rule file count, tokens burned without merges.

### Mental model (Cursor Learn + research)

Cursor Learn frames AI tooling as a **time / money / reliability** tradeoff you control ([Cursor Learn](https://cursor.com/learn)). Swarm research adds: with parallel agents, the scarce input becomes **good specs of intent** ([Swarm economics](https://cursor.com/blog/agent-swarm-model-economics)). For this monorepo: invest in Plan/Projects specs + thin Always + Skills — then parallelize execution.

---

## Mapping to this Laravel monorepo profile

*(Synthesis of official guidance applied to an already-migrated Skills + thin Always setup — not a Cursor claim.)*

- 10x baseline already done: don’t re-migrate; **operate** the compound stack.  
- Next leaps: Projects for multi-week Direct Book / migration work; Cloud env quality; `/multitask` + worktrees for independent product slices; verifier subagent + Bugbot as default merge gate; Router or manual planner≠worker mix.  
- Keep Always thin forever — Custom Modes for session playbooks.  
- Megafile / collision risk is real on shared Vue/PHP hubs — isolate or serialize those files ([Swarm economics](https://cursor.com/blog/agent-swarm-model-economics)).  
- Spend expectation: power user multi-agent ≈ **$200+/mo** usage often ([Models & Pricing](https://cursor.com/docs/models-and-pricing)) — 20x is output per dollar/hour, not free.

---

## Sources

1. https://cursor.com/docs  
2. https://docs.cursor.com  
3. https://cursor.com/docs/agent/overview  
4. https://cursor.com/docs/agent/plan-mode  
5. https://cursor.com/docs/agent/tools/search  
6. https://cursor.com/docs/rules  
7. https://cursor.com/docs/skills  
8. https://cursor.com/docs/subagents  
9. https://cursor.com/docs/hooks  
10. https://cursor.com/docs/mcp  
11. https://cursor.com/docs/bugbot  
12. https://cursor.com/docs/cloud-agent  
13. https://cursor.com/docs/cloud-agent/capabilities  
14. https://cursor.com/docs/models-and-pricing  
15. https://cursor.com/docs/cursor-router  
16. https://cursor.com/docs/reference/ignore-file  
17. https://cursor.com/docs/account/pricing/request-based-legacy  
18. https://cursor.com/learn  
19. https://cursor.com/changelog  
20. https://cursor.com/changelog/projects  
21. https://cursor.com/changelog/08-19-26  
22. https://cursor.com/changelog/04-24-26  
23. https://cursor.com/blog/agent-best-practices  
24. https://cursor.com/blog/dynamic-context-discovery  
25. https://cursor.com/blog/router  
26. https://cursor.com/blog/how-cursor-router-works  
27. https://cursor.com/blog/agent-autonomy-auto-review  
28. https://cursor.com/blog/agent-swarm-model-economics  
29. https://cursor.com/blog/scaling-agents  
30. https://cursor.com/blog/self-driving-codebases  
31. https://cursor.com/agents (product surface referenced by docs)  
32. Local baseline: `docs/research/cursor-ide-quality-speed-10x.md`
