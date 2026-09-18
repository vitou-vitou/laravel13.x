---
name: fast-track
description: Universal high-velocity task execution pipeline with scope gating, 8 parallel verification subagents, 100% verification (build + unit tests), and automated git combine push. Use when the user runs /fast-track, asks for fast-track execution, requests 8 parallel subagents for verification and quality, or specifies a task with scope boundaries and ship: combine.
---

# Fast-Track: Universal High-Velocity Execution Pipeline

Universal high-speed execution pipeline designed to take any feature, refactoring, permission alignment, or bugfix from bounded scope definition to production ship with strict scope isolation, 8 parallel verification subagents, 100% verification gating, and automated git combine push.

---

## Trigger Commands & Invocations

- `/fast-track <task> [scope: <boundaries>] [agents: 8] [ship: combine]`
- `/fast-track <task>`
- `fast-track <feature / bugfix / refactor>`
- `run 8 parallel subagents for verification and ship`

---

## 1. Scope & Constraints Lock

Before writing or modifying any code, lock the operational scope to avoid scope creep and regressions:

- **Target Boundary**: Identify exact modules, products, components, and files to be modified.
- **Out-of-Scope Rules**:
  - Direct Book PL boundaries: Quote (`QUOTATION`), Policy (`POLICY`), Endorsement (`ENDORSEMENT`). Never pollute unrelated claim, payment, or legacy namespaces unless explicitly requested.
  - Zero drive-by refactoring of untouched or legacy code (e.g. `0121`–`0125`).
- **Minimal Diff Principle**: Only touch lines directly necessary to fulfill the task. Follow simple code, short names, zero superfluous comments.

---

## 2. Implementation & Targeted Fix

Execute the required changes following project conventions:

1. **Architecture & Contracts**: Adhere to existing patterns (driver patterns, store helpers, permissions, backend profiles).
2. **Clean Code Voice**:
   - Short functions, intention-revealing names.
   - Zero AI block comments (`/** ... */`) or narrative commentary.
   - Explicit top-level imports (`use` / `import`).
3. **Reuse First**: Check existing utilities, constants, and vendor packages before writing custom logic.

---

## 3. 8-Subagent Parallel Verification Grid

Once implementation is in place, dispatch 8 specialized verification checks concurrently in parallel subagents or a unified multi-axis verification pass:

| Subagent / Axis | Focus Area | Verification Criteria |
|---|---|---|
| **1. Architecture & Contract** | Codebase design, standards, interface contracts | Zero contract violations; matches project patterns (e.g. `product-driver`, SM constants). |
| **2. Router & Guards** | Route definitions, navigation guards, slug/code mapping | Correct permission codes resolved; routes accessible for authorized roles. |
| **3. UI & Detail Rendering** | Views, action buttons, forms, visibility gates | Action buttons (`canEdit`, `canApprove`, `canAccept`, `canPrint`) and fields render accurately. |
| **4. Services & Adapters** | Frontend services, API clients, driver helpers | Payload serialization, parameter handling, and data transformations are correct. |
| **5. Backend & APIs** | Controller endpoints, profile classes, database constants | Enums, constants, and backend permission helpers align with frontend keys. |
| **6. Unit & Integration Tests** | PHPUnit / Pest / JS test suites | All related unit/integration tests pass with 0 failures (`vendor/bin/phpunit`). |
| **7. Frontend Asset Build** | Vite / Webpack compilation | `npm run build` exits 0 with no syntax, module resolution, or bundling errors. |
| **8. Git Status & Humanizer** | Diff inspection, commit format, trailer check | Working tree clean of unwanted files; commit message follows Adjective Noun standard; no AI trailers. |

---

## 4. 100% Quality & Verification Gate

All code must clear the 100% verification gate prior to shipping:

```bash
# 1. PHPUnit test suite verification
php vendor/bin/phpunit tests/Unit/PL/

# 2. Frontend compilation verification
npm run build
```

- **Zero Tolerance**: 0 test failures, 0 build errors, 0 unresolved linter diagnostics on edited files.
- If any check fails, immediately fix the root cause and re-verify before proceeding.

---

## 5. Ship / Git Combine Push

When all 8 verification dimensions pass:

1. **Inspect Working Tree**:
   ```bash
   git status --short
   git diff
   ```
2. **Craft Humanizer Commit Message**:
   - Format: **Adjective Noun** (e.g., `Proper Permissions`, `Sharper Sizing`, `Aligned Codes`).
   - Strict block on AI trailers (`Co-Authored-By`, `Generated with`, etc.).
   - Short, high-level summary of what changed (no internal file narration or mechanism explanations).
3. **Push to Remote**:
   ```bash
   git add <modified-files>
   git commit -m "<Adjective Noun>"
   git push origin HEAD
   ```
