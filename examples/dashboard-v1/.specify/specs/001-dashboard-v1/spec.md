# Feature Specification: Analytics Dashboard (KPI Widgets)

**Feature Branch**: `001-dashboard-v1`

**Created**: 2026-06-06

**Status**: MVP complete (2026-06-06)

**Input**: Greenfield example under `examples/dashboard-v1` — authenticated analytics dashboard with KPI widgets and recent activity (study catalog #125-style admin dashboard).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - View KPI summary (Priority: P1)

An authenticated user opens the dashboard and sees four KPI cards with label, value, and trend indicator.

**Why this priority**: KPI widgets are the core dashboard value.

**Independent Test**: GET `/dashboard` as authenticated user returns 200 with Revenue, Users, Orders, and Conversion KPI labels.

**Acceptance Scenarios**:

1. **Given** a logged-in user, **When** they visit `/dashboard`, **Then** four KPI cards are visible with numeric values.
2. **Given** a guest, **When** they visit `/dashboard`, **Then** they are redirected to login.

---

### User Story 2 - Recent activity table (Priority: P1)

The dashboard shows a table of recent orders with customer, amount, status, and date.

**Acceptance Scenarios**:

1. **Given** a logged-in user on `/dashboard`, **When** the page loads, **Then** a recent orders table with at least one row is visible.
2. **Given** the table, **When** inspected, **Then** column headers include Customer, Amount, Status, and Date.

---

### User Story 3 - Branded dashboard header (Priority: P2)

The dashboard page title reads "Analytics Dashboard" and greets the user by name.

**Acceptance Scenarios**:

1. **Given** user "Alex", **When** on `/dashboard`, **Then** page contains "Analytics Dashboard" and a welcome with Alex's name.

---

## Functional Requirements

- FR-001: Session auth via Laravel Breeze (Blade stack).
- FR-002: Protected `/dashboard` route for authenticated users only.
- FR-003: `DashboardMetricsService` supplies KPI data (demo/seed values for MVP).
- FR-004: KPI cards: Total Revenue, Active Users, Orders Today, Conversion Rate — each with trend (up/down/neutral).
- FR-005: Recent orders table with demo rows from the same service.
- FR-006: Responsive layout using Tailwind (Breeze default).

## Out of Scope (MVP)

- Live charts / Chart.js
- Database-backed metrics
- Admin roles, Filament
- Real-time polling (Livewire)
