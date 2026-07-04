# n8n — Workflow Automation

**A Structured Study Packet**  
Built with an 8-principle learning method

---

## How to use this packet

This packet teaches **n8n**: what it is, how workflows run, self-hosted vs Cloud pricing, core concepts (nodes, triggers, credentials, executions), and a practical pattern for **Telegram bug reports → structured tickets** across many projects (Laravel, Next.js, Vue3).

Content is drawn from [n8n official docs](https://docs.n8n.io/), the [Sustainable Use License](https://docs.n8n.io/sustainable-use-license/), and common 2026 pricing references for Cloud vs self-hosted.

**Audience:** developer new to n8n — student / general learner level.

**Step 1 — Understanding** (Principles 1–4): build a correct mental model.  
**Step 2 — Automaticity** (Principles 5–8): quizzes, spacing, mixing, and overlearning.

### The 8 principles

| # | Principle | What you do |
|---|-----------|-------------|
| 1 | Map of the system | See how parts connect |
| 2 | Clear explanations | Learn core ideas in plain language |
| 3 | Different media | Same ideas as summary, diagram, analogy, table |
| 4 | Short lessons | Bite-sized micro-lessons |
| 5 | Test yourself | Quiz + flashcards + answer key |
| 6 | Wait to review | Spaced repetition schedule |
| 7 | Mix it up | Interleaved quiz |
| 8 | Don't stop | Overlearning plan |

### Table of contents

- [Step 1 — Understanding](#step-1--understanding)
  - [Principle 1 — Map of the system](#principle-1--map-of-the-system)
  - [Principle 2 — Clear explanations](#principle-2--clear-explanations)
  - [Principle 3 — Different media](#principle-3--different-media)
  - [Principle 4 — Short lessons](#principle-4--short-lessons)
- [Step 2 — Automaticity](#step-2--automaticity)
  - [Principle 5 — Test yourself](#principle-5--test-yourself)
  - [Principle 6 — Wait to review](#principle-6--wait-to-review)
  - [Principle 7 — Mix it up](#principle-7--mix-it-up)
  - [Principle 8 — Don't stop](#principle-8--dont-stop)
- [Appendix — Glossary](#appendix--glossary)
- [Example workflow — Telegram bug triage](#example-workflow--telegram-bug-triage)

---

# Step 1 — Understanding

Your goal: see n8n as a **visual program** that connects apps (Telegram, GitHub, Slack, HTTP APIs) with **nodes on a canvas**, runs on **your server or n8n Cloud**, and charges you **nothing for software** if you self-host for internal use.

---

## Principle 1 — Map of the system

### What n8n is (one sentence)

**n8n** is a workflow automation tool: you draw a flowchart; each box is a **node** that reads data, transforms it, or calls an external service; the engine runs that graph when a **trigger** fires.

### Ecosystem components

| Component | Responsibility | Example |
|-----------|----------------|---------|
| **n8n instance** | Runs workflows (self-hosted or Cloud) | Docker on VPS, or n8n.io account |
| **Workflow** | Saved graph of nodes + connections | "Telegram → GitHub Issue" |
| **Trigger node** | Starts a run (schedule, webhook, Telegram message) | Telegram Trigger |
| **Action node** | Does one step (HTTP, GitHub, Slack, Code) | Create Issue |
| **Connection** | Passes output of node A as input to node B | Arrow on canvas |
| **Item** | One JSON object flowing through the workflow | `{ "text": "bug in shop" }` |
| **Execution** | One full run from trigger to end | Counted on Cloud; unlimited self-host CE |
| **Credential** | Encrypted secret store (API keys, bot tokens) | Telegram Bot Token |
| **Expression** | `{{ $json.field }}` — read data from previous nodes | `{{ $json.message.text }}` |

### Self-hosted vs Cloud map

| Path | Software cost | Infra | Best for |
|------|---------------|-------|----------|
| **Community (self-host)** | **$0** license | Your PC or ~$5–15/mo VPS | Internal automation, unlimited runs |
| **n8n Cloud** | **~$20–24/mo+** (Starter tier) | Managed by n8n | No server maintenance |
| **Enterprise self-host** | Paid license | Your infra | SSO, Git sync, support |

License: **Sustainable Use License** — free for **your own internal business**; not for reselling n8n as a product to customers. See [docs](https://docs.n8n.io/sustainable-use-license/).

### Execution flow (how a run works)

| Stage | What happens |
|-------|----------------|
| 1 | **Trigger** fires (new Telegram message, cron tick, webhook POST) |
| 2 | n8n creates an **execution** and loads workflow JSON |
| 3 | Trigger outputs **items** (usually JSON array) |
| 4 | Each connected node receives items, processes, outputs new items |
| 5 | Branches (**IF**, **Switch**) route items different ways |
| 6 | Run ends; logs + data visible in **Executions** tab |
| 7 | On failure: error on node; optional **Error Workflow** |

**Map takeaway:** n8n = trigger → chain of nodes → external APIs. You host it or pay Cloud. Data moves as JSON **items** along arrows.

---

## Principle 2 — Clear explanations

### What problem does n8n solve?

You repeat the same glue work: "When X happens in app A, do Y in app B." n8n replaces custom scripts and Zapier-style integrations with a **visual workflow** you can change without redeploying an app.

### What is a node?

A **node** is one step. Types include:

- **Triggers** — start the workflow (Telegram, Schedule, Webhook)
- **Actions** — call a service (GitHub, Slack, Gmail)
- **Logic** — IF, Switch, Merge, Loop
- **Data** — Set, Code (JavaScript/Python), HTML extract
- **Core** — HTTP Request (call any API)

Each node has **parameters** (form fields) and **input/output** ports.

### What flows between nodes?

**Items** — typically JSON objects. Example after Telegram trigger:

```json
{
  "message": {
    "text": "#shop payment 500 on checkout",
    "chat": { "id": -1001234567890 }
  }
}
```

Next node reads fields with expressions: `{{ $json.message.text }}`.

### What is an execution?

One **execution** = one run of the workflow from trigger to finish. n8n Cloud bills by execution count on paid tiers. Self-hosted Community Edition: **no execution cap** (you pay only server).

### Self-hosted vs Cloud — which is "free"?

| | Self-host CE | n8n Cloud |
|---|--------------|-----------|
| Software | Free (internal use) | Paid subscription |
| Runs | Unlimited (your hardware) | Tier limits (e.g. 2,500/mo Starter) |
| Setup | Docker/npm on your machine | Sign up, done |
| Data | Stays on your server | On n8n infrastructure |

For Telegram bug routing across **your** 10 projects: **self-host on PC or cheap VPS** = $0 software.

### How is n8n different from Zapier?

| | n8n | Zapier |
|---|-----|--------|
| Self-host | Yes | No |
| Code step | JS/Python in workflow | Limited |
| Pricing | Free self-host; Cloud cheaper at scale | Per-task pricing |
| Fair-code license | Sustainable Use | Proprietary SaaS |

### How does n8n fit your 10-project bug case?

Telegram message → n8n parses `#shop` hashtag → looks up project in a **table node** or Google Sheet → creates **GitHub Issue** in correct repo → replies in Telegram "Logged #042". You fix in git; optional second workflow posts "✅ merged" on deploy webhook.

**Explanation takeaway:** n8n is a node graph + JSON data + credentials. Self-host = free for internal ops. One workflow = one automation recipe.

**Common misconception:** "n8n is open source like MIT." It is **fair-code** (Sustainable Use License) — free for internal use, restrictions on commercial resale as a hosted product.

---

## Principle 3 — Different media

### One-line summary

> n8n is a self-hostable visual workflow engine that moves JSON between app connectors when a trigger fires — free software for internal use, optional paid Cloud hosting.

### Diagram — Telegram bug workflow

```
  [Telegram Trigger]     new message in group
         │
         ▼
  [Code or IF node]      parse #shop #marketing hashtag
         │
         ▼
  [Switch node]          route by project tag
    ├─► shop ──► [GitHub: Create Issue] repo=laravel-shop
    ├─► marketing ──► [GitHub: Create Issue] repo=next-marketing
    └─► default ──► [Telegram: Send Reply] "Which project? Use #tag"
         │
         ▼
  [Telegram: Send Reply]  "Logged issue #123 — laravel-shop"
```

### Analogy

n8n is a **airport baggage system**: each bag is an **item** (JSON). **Triggers** drop bags on the belt. **Nodes** are stations that scan, relabel, or send bags to different airlines (APIs). You design the route on a map (canvas), not by rewriting the whole airport.

### Comparison table

| Tool | Visual | Self-host | Code | Free tier |
|------|--------|-----------|------|-----------|
| **n8n CE** | Yes | Yes | JS/Python nodes | Software free |
| **Zapier** | Yes | No | Limited | Very limited |
| **Make** | Yes | No | Limited | Limited ops |
| **GitHub Actions** | YAML | N/A (GitHub) | Yes | Free limits |
| **Custom Laravel bot** | No | Yes | Full | Dev time |

**Media takeaway:** Canvas + JSON bags + triggers. Best when you want visual glue without building a full bot app.

---

## Principle 4 — Short lessons

### Lesson 1 — Install self-hosted (Docker, quickest)

```bash
docker volume create n8n_data
docker run -d --name n8n \
  -p 5678:5678 \
  -e GENERIC_TIMEZONE="Asia/Phnom_Penh" \
  -v n8n_data:/home/node/.n8n \
  docker.n8n.io/n8nio/n8n
```

Open `http://localhost:5678`, create owner account. Software: **$0**.

### Lesson 2 — Build your first workflow

1. **New workflow** → add **Manual Trigger** (for testing)
2. Add **Set** node → define fields `hello: world`
3. Add **HTTP Request** → GET `https://httpbin.org/get`
4. Connect: Trigger → Set → HTTP Request
5. Click **Execute workflow** → inspect each node's output panel

You passed JSON from node to node.

### Lesson 3 — Add a real trigger (Telegram)

1. Create bot via [@BotFather](https://t.me/BotFather) → copy token
2. In n8n: **Credentials** → Telegram → paste token
3. Replace Manual Trigger with **Telegram Trigger** ("On message")
4. Activate workflow (toggle **Active**)
5. Message your bot — execution appears in **Executions**

### Lesson 4 — Expressions

In any text field, use:

```
{{ $json.message.text }}
{{ $json.message.chat.id }}
{{ $('Telegram Trigger').item.json.message.text }}
```

**$json** = current item. **$('Node Name')** = output from named node.

### Lesson 5 — Branch with IF

```
Telegram Trigger → IF (text contains "#shop") 
                    ├─ true  → GitHub Create Issue (shop repo)
                    └─ false → Telegram Reply "Use #shop #marketing …"
```

### Lesson 6 — Credentials and security

- Tokens live in **Credentials**, encrypted at rest on your instance
- Never hard-code API keys in Code node if credential type exists
- Production: HTTPS, strong password, firewall port 5678

### Lesson 7 — Self-host vs Cloud decision

| Choose self-host if… | Choose Cloud if… |
|----------------------|------------------|
| Internal bugs/automation | No time for Docker |
| Many executions | Want managed updates |
| Data must stay local | Small run volume, budget OK |

**Short-lessons takeaway:** Install → Manual test → Real trigger → Expressions → IF branches → Credentials. Then activate.

---

# Step 2 — Automaticity

Goal: recall how n8n works without reopening docs.

---

## Principle 5 — Test yourself

### Quiz

1. What starts an n8n workflow run?
2. What is an "item" in n8n?
3. How do you read the Telegram message text in a later node?
4. Is self-hosted Community Edition free for your internal bug triage?
5. What do you pay when self-hosting on a $5 VPS?
6. What is an execution?
7. Name two node types (trigger vs action).
8. Where are API tokens stored?
9. What license does n8n use (not OSI open source)?
10. How is n8n different from writing a Laravel Telegram bot?

### Answer key

1. A **trigger node** (Telegram, Schedule, Webhook, etc.)
2. A **JSON object** (or array of objects) passed between nodes
3. Expression: `{{ $json.message.text }}` (field names may vary by trigger output)
4. **Yes** — internal business use under Sustainable Use License
5. **VPS cost only** (~$5/mo) — no n8n software fee for CE
6. One **full run** of a workflow from trigger to end
7. Any two: e.g. **Telegram Trigger** (trigger), **GitHub** (action), **IF** (logic)
8. **Credentials** store (encrypted on the instance)
9. **Sustainable Use License** (fair-code)
10. n8n = visual, less code, faster to change; Laravel bot = full control, one codebase, more dev time

### Flashcards

| Front | Back |
|-------|------|
| n8n | Visual workflow automation; nodes + JSON items |
| Node | One step on the canvas (trigger, action, logic) |
| Item | JSON object flowing between nodes |
| Execution | One complete workflow run |
| Trigger | Node that starts a workflow |
| Expression | `{{ $json.field }}` — dynamic value |
| Credential | Encrypted API token storage |
| Self-host CE cost | $0 software; pay server only |
| n8n Cloud | Paid hosted; execution limits by tier |
| Sustainable Use License | Fair-code; free internal use |
| Activate workflow | Toggle ON — triggers listen in production |
| Default port | 5678 |

---

## Principle 6 — Wait to review

| When | What to do | ☐ |
|------|------------|---|
| **Today** | Draw trigger → 2 nodes → API on paper | ☐ |
| **Day 1** | Run Docker n8n; build Manual → Set → HTTP | ☐ |
| **Day 3** | Quiz Q1–Q5 from memory | ☐ |
| **Day 7** | Connect Telegram test bot; one execution | ☐ |
| **Day 14** | Flashcards — 3 flawless rounds | ☐ |
| **Day 30** | Interleaved quiz cold | ☐ |

---

## Principle 7 — Mix it up

1. Cloud vs self-host — which has execution limits?
2. Item vs execution — difference?
3. Bug message has no `#tag` — which node type handles it?
4. You want to call a Laravel API not in node library — which node?
5. Can you sell access to your self-hosted n8n as a SaaS to clients?
6. Workflow inactive — do Telegram triggers fire?
7. `$json` in a Set node after Telegram — what does it refer to?
8. 10 projects — Switch node or 10 workflows?
9. n8n vs GitHub Actions for Telegram → Issue?
10. Where to debug a failed node?

### Interleaved answer key

1. **Cloud** (tier limits); self-host CE typically unlimited
2. **Item** = data unit; **execution** = whole run
3. **IF** or **Switch** → false branch → Telegram reply asking for tag
4. **HTTP Request** node
5. **No** — requires commercial/embed license (Sustainable Use restriction)
6. **No** — must **Activate** workflow
7. Current item from immediate previous node output
8. **One workflow + Switch** (or Code lookup table) — easier to maintain
9. n8n = faster visual glue; Actions = git-native, YAML, no extra service
10. **Executions** tab → open run → click red node → error message + input data

---

## Principle 8 — Don't stop

### Stages

| Stage | Sign | Action |
|-------|------|--------|
| First correct | Ran Manual → HTTP once | Add Telegram trigger |
| Comfortable | Telegram → IF → reply works | Add GitHub Create Issue |
| Automatic | Build bug triage without tutorial | Add deploy webhook → Telegram ✅ |

### Overlearning plan

- Build **2 workflows**: test ping + real triage
- Rebuild from blank canvas once per week (no import)
- Document your 10 project hashtags in a **Set** or Google Sheet node
- Pair with git worktree flow: n8n creates issue → you fix in worktree → merge closes loop

**Final takeaway:** n8n = triggers + nodes + JSON + credentials. Self-host for free internal automation. Practice until you can sketch Telegram → GitHub in 5 minutes.

---

# Appendix — Glossary

| Term | Definition |
|------|------------|
| **n8n** | Fair-code workflow automation platform |
| **Workflow** | Saved directed graph of nodes |
| **Node** | Single step (trigger, action, or logic) |
| **Item** | JSON data unit passed between nodes |
| **Execution** | One run of a workflow |
| **Trigger** | Entry node that starts executions |
| **Expression** | `{{ }}` template reading prior node data |
| **Credential** | Stored secret for a service connection |
| **Activate** | Enable workflow so triggers run in background |
| **Community Edition** | Self-hosted tier with full core features |
| **Sustainable Use License** | n8n license allowing free internal business use |
| **Webhook** | HTTP endpoint that triggers a workflow |

**Official docs:** [docs.n8n.io](https://docs.n8n.io/) · [Self-host guide](https://docs.n8n.io/hosting/) · [License FAQ](https://docs.n8n.io/sustainable-use-license/)

**Related repo packets:** `cursor-worktree-8-principle-study.md`, `claude-skills-8-principle-study.md`, `docs/multi-project-workflow.md`

---

## Example workflow — Telegram bug triage

Minimal recipe for 10 projects (Laravel / Next / Vue):

### Nodes (in order)

1. **Telegram Trigger** — On message (group or DM)
2. **Code** — Parse hashtag and priority:

```javascript
const text = $input.item.json.message.text || '';
const tags = text.match(/#(\w+)/g) || [];
const project = tags[0]?.replace('#','') || 'unknown';
const priority = text.includes('P0') ? 'P0' : 'P1';
return [{ json: { project, priority, raw: text, chatId: $json.message.chat.id } }];
```

3. **Switch** — Route on `{{ $json.project }}` (`shop`, `marketing`, `admin`, …)
4. **GitHub** (per branch) — Create Issue in mapped repo
5. **Telegram** — Send message: `Logged #{{ $json.issueNumber }} for {{ $json.project }}`

### Project registry (optional)

Store in **Google Sheets** or **n8n Data table**:

| hashtag | repo | stack |
|---------|------|-------|
| shop | vitou/laravel-shop | laravel |
| marketing | vitou/next-marketing | nextjs |
| admin | vitou/vue-admin | vue3 |

**Code** node does lookup: hashtag → repo name → pass to GitHub node.

### Cost for this pattern

| Piece | Cost |
|-------|------|
| n8n CE on your PC | $0 |
| n8n CE on VPS | ~$5/mo |
| Telegram Bot API | $0 |
| GitHub Issues API | $0 |
| n8n Cloud | ~$20+/mo (optional) |

---

*End of packet.*
