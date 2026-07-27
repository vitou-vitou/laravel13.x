---
name: /super-spec-reset
id: super-spec-reset
category: Workflow
description: Reset spec mode selection — deletes .spec-mode for auto-detection
---

Run `/super-spec` with variant `reset`.

1. Delete project-root `.spec-mode` if it exists
2. Report that mode selection was cleared
3. On next `/super-spec` (without variant), auto-detect mode from project signals:
   - `openspec/` → OpenSpec
   - `.specify/` → Spec-Kit
   - default → OpenSpec

Do not start a new spec workflow unless the user asks to continue with `/super-spec` and a task description.
