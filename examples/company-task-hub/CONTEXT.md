# Glossary: Company Task Hub

## Context
`examples/company-task-hub` is a specialized operational task management application built with Laravel 13 and Filament v5. It unifies internal operational tasks, daily incident/issue tracking, and customer-related support actions into one workspace.

## Canonical Terms

### Company Task (Company General)
- **Definition:** An internal organizational or operational task (e.g. quarterly audit, office management, system maintenance).
- **Attributes:** Title, description, department, priority (low, medium, high, urgent), status (pending, in_progress, completed, cancelled), assignee_id, due_date.

### Issue Resolution Task (Resolve Issue Daily Task)
- **Definition:** A daily operational incident or bug task requiring rapid investigation and resolution.
- **Attributes:** Title, incident_type, severity (minor, major, critical), resolution_notes, root_cause, status (open, investigating, resolved, closed), resolver_id, resolved_at.

### Customer Task (Customer General)
- **Definition:** An action item or request tied to an external client or customer profile (e.g. onboarding followup, account review, custom setup).
- **Attributes:** Title, customer_name, customer_email, task_type, priority, status (open, in_progress, completed), handler_id, due_date.

### User / Operator
- **Definition:** Internal staff member who creates, resolves, or oversees tasks across all three operational pillars.
