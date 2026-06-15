# Feature Specification: Dynamic Warm View 1906

**Feature Branch**: `001-dynamic_warm_view_1906`  
**Created**: 2026-06-13  
**Status**: MVP complete

## User Scenarios

### P1 — Health check for Render
- GET `/api/healthz` returns 200 when SQLite is reachable.

### P1 — API authentication
- Register, login, logout, and fetch current user via Sanctum tokens.

### P1 — User-owned tasks
- Authenticated users CRUD their own tasks; cannot access others' tasks.

## Functional Requirements

- FR-001: `/api/healthz` reports app and database status.
- FR-002: Sanctum token auth for register, login, logout, user profile.
- FR-003: Task CRUD scoped to authenticated user.
- FR-004: Docker image based on `serversideup/php:8.4-fpm-nginx`.
- FR-005: `render.yaml` for Render free-tier web service.

## Out of Scope

- Queues, Redis, Postgres, OAuth, SPA frontend.
