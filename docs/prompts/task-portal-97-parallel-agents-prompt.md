# Task Portal — 97-Task Parallel Agent Build Prompt

**Goal:** Build a complete internal Task/Project Management Portal (Laravel 13) — normally scoped as a 3-month roadmap — compressed into a single ~10-hour run using up to 97 parallel agents.

**How to use:** Paste the "Master Orchestrator Prompt" below into your main agent session. It will read the task list, group tasks into independent batches, and dispatch parallel agents per batch.

---

## Master Orchestrator Prompt

```
You are the orchestrator for building a Laravel 13 "Task Portal" — an internal
task/project management app (think mini Asana/Jira): teams, projects, tasks,
assignments, statuses, comments, attachments, notifications, dashboards, and
reporting.

Project path: examples/task-portal-1122 (create via ./bin/new-example if missing)

Use the dispatching-parallel-agents skill (.agents/skills/dispatching-parallel-agents/SKILL.md):
dispatch one agent per INDEPENDENT task slice below, run batches concurrently,
and integrate + run `php artisan test` after each batch.

CONSTRAINTS:
- Total wall-clock budget: 10 hours. Prioritize ruthlessly — if a batch risks
  blowing the budget, cut scope (stub UI, skip polish) rather than skip tests.
- TDD: every feature slice ships with feature tests (Spec-Kit + Superpowers TDD).
- No shared-file edits within the same batch — if two tasks touch the same file,
  put them in different batches (sequential), not the same batch (parallel).
- After each batch: run full test suite, fix conflicts, commit.

97 TASK SLICES (grouped into sequential batches of parallel, independent tasks):

BATCH 1 — Foundation (sequential, 1 agent — others depend on this)
1. Spec-Kit init + Breeze scaffold + base schema (users, teams, team_members)

BATCH 2 — Core domain models (parallel, independent migrations/models)
2. Project model + migration + factory + policy
3. Task model + migration + factory + policy
4. TaskStatus enum/lookup + seeder
5. TaskPriority enum/lookup + seeder
6. Label/Tag model + migration + pivot
7. Comment model + migration + policy
8. Attachment model + migration (file upload)
9. Notification model + migration
10. ActivityLog model + migration

BATCH 3 — Core CRUD features (parallel, separate controllers/routes)
11. Team CRUD (create/edit/invite members)
12. Project CRUD (create/edit/archive)
13. Task CRUD (create/edit/delete)
14. Task assignment (assign/reassign to team member)
15. Task status transitions + validation
16. Task priority + due date handling
17. Comment create/edit/delete on tasks
18. Attachment upload/download on tasks
19. Label create/assign/remove on tasks
20. Subtask / checklist items on tasks

BATCH 4 — Views & UI (parallel, separate Blade views)
21. Dashboard view (my tasks, overdue, recent activity)
22. Project list + detail view
23. Task list view (filters: status/assignee/priority/label)
24. Task detail view (comments, attachments, activity)
25. Kanban board view (drag-drop status columns)
26. Calendar view (tasks by due date)
27. Team management view
28. User profile + settings view
29. Notification center view
30. Global search UI

BATCH 5 — Search & filtering (parallel)
31. Full-text search backend (tasks/projects/comments)
32. Saved filters / views
33. Sort + pagination on task lists
34. Advanced filter combos (assignee + status + label)
35. Search index/scout integration (optional)

BATCH 6 — Notifications & real-time (parallel)
36. In-app notification on assignment
37. In-app notification on comment/mention
38. In-app notification on due-date approaching
39. Email notification digest (daily)
40. Reverb/Echo real-time task updates
41. Real-time presence (who's viewing a task)
42. @mention parsing + notification trigger

BATCH 7 — Permissions & roles (parallel)
43. Spatie roles/permissions setup
44. Admin role (full access)
45. Manager role (team/project scoped)
46. Member role (assigned tasks only)
47. Guest/viewer role (read-only)
48. Policy tests for all roles x all CRUD actions

BATCH 8 — Reporting & analytics (parallel)
49. Project progress report (% complete, burndown)
50. Team workload report (tasks per member)
51. Overdue tasks report
52. Time-to-completion analytics
53. Activity timeline report
54. CSV/PDF export of reports

BATCH 9 — Automation & workflow (parallel)
55. Auto-assign rules (round robin / least busy)
56. Recurring tasks (daily/weekly templates)
57. Due-date reminder scheduler (artisan command + cron)
58. Auto-archive completed projects after N days
59. Task dependency chains (blocked-by)
60. Status automation (auto-close subtasks when parent closes)

BATCH 10 — Integrations (parallel)
61. Slack/webhook notification integration
62. Email-to-task (create task via inbound email)
63. Calendar export (.ics feed)
64. REST API (Sanctum) — projects/tasks CRUD endpoints
65. API rate limiting + throttling
66. Webhook outbound on task events

BATCH 11 — Admin & settings (parallel)
67. Admin: user management (CRUD, deactivate)
68. Admin: team management
69. Admin: custom fields builder
70. Admin: workflow/status customization per project
71. Admin: audit log viewer
72. Org-level settings (timezone, working hours, branding)

BATCH 12 — UX polish & accessibility (parallel)
73. Responsive layout pass (mobile/tablet)
74. Dark mode
75. Accessibility audit fixes (design:accessibility-review skill)
76. Empty states + onboarding tooltips
77. Loading states / skeleton screens
78. Toast notifications for actions

BATCH 13 — Performance & hardening (parallel)
79. N+1 query audit + eager loading fixes
80. DB indexing pass on hot queries
81. Caching layer (project/task counts, dashboards)
82. Queue offloading for heavy jobs (exports, emails)
83. Rate limiting on public-facing routes
84. File upload size/type validation hardening

BATCH 14 — Security review (parallel)
85. Authorization re-audit (all policies)
86. CSRF/XSS spot checks on dynamic content (comments, labels)
87. Mass-assignment guard review on all models
88. Sensitive data review (PII in logs/exports)
89. security-review skill pass on full diff

BATCH 15 — Testing & QA (parallel)
90. Feature test coverage gap-fill (target 90%+)
91. Browser/E2E smoke test (login → create project → create task → assign → comment → complete)
92. Load test critical endpoints (task list, dashboard)
93. Cross-role permission test matrix

BATCH 16 — Docs & handoff (parallel)
94. README + setup docs
95. API docs (if Sanctum API built)
96. docs/NEXT_SESSION.md handoff doc
97. Final ROADMAP.md update marking phases complete

---
EXECUTION NOTES:
- Batch 1 is sequential and blocking — everything else depends on it.
- Batches 2–16 can each be split across multiple agents (don't dispatch more
  than ~10 agents per wave even if you have 97 tasks — reuse agent slots across
  waves).
- If 10-hour budget is at risk by Batch 10, defer Batches 12 (UX polish) and
  10 (integrations) to a follow-up session — core CRUD + permissions + tests
  (Batches 1-9, 14-15) are the must-ship MVP.
- After all batches: run `php artisan test`, then update docs/SESSION_STATE.md
  with the new project entry following the existing table format.
```

---

## Notes

- Adjust the project slug (`examples/task-portal-1122`) to match your naming convention.
- "97 agents" here means 97 task slices dispatched across ~10-16 sequential batches of parallel agents (not literally 97 concurrent agents — concurrency limits and shared-file conflicts make true 97-way parallelism impractical).
- If 10 hours proves too tight, the MVP cut-line is marked in the execution notes (Batches 1-9 + 14-15).
