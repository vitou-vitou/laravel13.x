# Change: Issue Kanban Board

## Why

The Jira clone defines project types (`scrum` / `kanban`) and issue statuses, but admins only see a flat table. A Kanban board is the most meaningful missing workflow view for sprint planning and status tracking.

## What

- Filament custom page: **Kanban Board** under Issues navigation
- Four columns: To Do → In Progress → In Review → Done
- Filter by project
- Move issue to another column (updates `issues.status`)
- Seed demo projects/sprints/issues so the board is usable on first visit

## Success criteria

- Authenticated admin can open `/admin/issue-kanban-board`
- Issues appear in the correct status column
- Moving an issue persists the new status
- Feature tests pass

## Out of scope

- Drag-and-drop (future enhancement)
- Per-sprint board filter (future enhancement)
