# Specification: Company Task Hub (`examples/company-task-hub`)

## Problem Statement
Teams juggle internal company operations, daily urgent bug/incident resolutions, and client onboarding tasks across disparate systems or unstructured notes. Without clear segregation, daily critical issues get lost amidst long-term company administrative tasks, and customer follow-ups lack accountability.

## Solution
A unified, high-efficiency operational management application built on Laravel 13 and Filament v5 providing three dedicated operational pillars:
1. **Company General**: Internal operational & department workflows.
2. **Resolve Issue Daily Task**: Fast incident management with root cause and resolution tracking.
3. **Customer General**: Dedicated customer task management tied to client details.

## User Stories

1. As an Operations Manager, I want to create internal company tasks with department tagging and priority, so that organizational operations stay organized.
2. As an Operations Manager, I want to update company task status from pending to in-progress or completed, so that team workload is transparent.
3. As a Support/Dev Engineer, I want to log daily incident issues with severity levels (minor, major, critical), so that high-severity blockers are immediately prioritized.
4. As a Support/Dev Engineer, I want to record root cause analysis and resolution notes when resolving daily issues, so that recurring incidents have historical context.
5. As an Account Representative, I want to track customer action items with customer contact details, so that client commitments are fulfilled on time.
6. As a Team Lead, I want filtered Filament tables and quick-action status toggles across all three task categories, so that daily standups and triage take minimal time.
7. As an Administrator, I want realistic seed data populated for all three domains, so that local development and demonstrations work immediately out of the box.

## Implementation Decisions

- **Modular Domain Segregation (ADR 0001):** Three independent Eloquent models and database tables (`company_tasks`, `daily_issue_tasks`, `customer_tasks`) rather than a single polymorphic table.
- **Filament v5 Resource Structure:**
  - `CompanyTaskResource`: Form fields for title, department, priority enum, status enum, due date, description.
  - `DailyIssueTaskResource`: Form fields for title, incident type, severity enum, status enum, root cause text, resolution notes, resolved timestamp.
  - `CustomerTaskResource`: Form fields for title, customer name, customer email, task type, priority enum, status enum, due date, notes.
- **Table Controls & Ergonomics:** Status badge columns, priority indicator badges, date formatting, and search/filter panels for department, severity, and status.
- **Database Schema & Enums:** Native PHP 8.4 Backed Enums for Statuses and Priorities to ensure strict type safety across forms and models.

## Testing Decisions

- **Pest PHP Feature Tests:** Test full end-to-end behavior at the HTTP / Livewire Filament seam:
  - Database schema integrity and Model CRUD operations.
  - Filament Resource listing and filtering validation.
  - State transitions (e.g. marking daily issue as resolved automatically populates `resolved_at`).
  - Validation rules enforcement (e.g. required customer email format, required issue severity).

## Out of Scope

- External customer portal authentication / login (all operations managed via staff panel).
- Automated email/SMS notifications to customers.
- Complex third-party integrations (Jira, Slack, Webhooks).

## Further Notes

- Target directory: `D:\laravel13.x\examples\company-task-hub`
- Follows monorepo guidelines in `D:\laravel13.x\AGENTS.md`.
