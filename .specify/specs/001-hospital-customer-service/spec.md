# Feature Specification: Hospital Customer Service & General Inquiries (CareDesk MVP)

**Feature**: `001-hospital-customer-service`  
**Target Location**: `apps/app-01` (or standalone in monorepo apps)  
**Created**: 2026-10-07  
**Status**: Draft (Awaiting G1 Approval)  
**Input**: "create an web app for hospital , solve issue customer service, customer general."

---

## 1. Executive Summary & Problem Statement

Hospitals experience high volumes of patient and visitor inquiries regarding outpatient appointments, doctor schedules, insurance coverage, billing, and general services. Front-desk phone lines are frequently backlogged, causing long wait times and patient frustration.

This web application provides:
1. **Self-Service General Portal**: Quick answers, FAQ, department directories, and appointment inquiry triage for visitors and patients.
2. **Patient Inquiry & Ticket Tracker**: Visitors can submit inquiries or service complaints and track real-time resolution status via a public tracking code.
3. **Staff Customer Service Desk**: Hospital support agents triage incoming tickets, respond to patients, route to specific departments, and close tickets with audit history.

---

## 2. User Scenarios & Testing

### User Story 1 - Public Inquiry & Support Ticket Submission (Priority: P1)
As a patient or visitor, I want to submit a question, appointment inquiry, or service issue online without needing to create an account, so that I can get assistance quickly without waiting on phone lines.

**Independent Test**: A visitor visits `/inquiry/new`, submits contact info, department category, subject, and description. Receives a unique reference code (e.g. `HOSP-2026-ABCD`) and confirmation message.

**Acceptance Scenarios**:
1. **Given** a visitor on the inquiry submission page, **When** they fill valid details (name, phone/email, category, message) and submit, **Then** a ticket is created with status "Open" and a reference code is returned.
2. **Given** invalid or missing required fields, **When** they submit, **Then** clear validation errors highlight required inputs.

---

### User Story 2 - Ticket Status Tracker (Priority: P1)
As a patient, I want to check the status and staff response to my inquiry using my reference code and phone/email, so that I can stay updated on my request.

**Independent Test**: Patient navigates to `/track`, enters ticket code `HOSP-XXXX-XXXX`, and views ticket status (Open, Under Review, Resolved) along with staff replies.

**Acceptance Scenarios**:
1. **Given** an existing ticket reference code, **When** patient enters the code, **Then** current status, submission timestamp, assigned department, and staff response notes are displayed.
2. **Given** an invalid reference code, **When** patient submits the search, **Then** an informative "Inquiry not found" alert is shown.

---

### User Story 3 - Staff Customer Service Dashboard (Priority: P1)
As a hospital customer service representative, I want a triage dashboard where I can view all pending inquiries, filter by department/urgency, reply, and update ticket statuses.

**Independent Test**: Staff logs in, views inquiry list, opens ticket `HOSP-XXXX-XXXX`, posts an official reply, changes status to "Resolved", and saves.

**Acceptance Scenarios**:
1. **Given** logged-in staff at `/admin/inquiries`, **When** viewing the list, **Then** tickets can be filtered by status (Open, In Progress, Resolved) and department (Emergency, Outpatient, Billing, General).
2. **Given** an open ticket, **When** staff submits an answer and changes status to "Resolved", **Then** the ticket record updates with response text, agent ID, and resolved timestamp.

---

### User Story 4 - Department & Knowledge Base Directory (Priority: P2)
As a visitor, I want to browse hospital departments, visiting hours, doctor specializations, and frequently asked questions (FAQs), so that I can get instant answers without opening a support ticket.

**Independent Test**: Visitor navigates to `/departments` and `/faq` to view operating hours, phone hotlines, and standard hospital policies.

**Acceptance Scenarios**:
1. **Given** a visitor browsing `/departments`, **When** selecting a department, **Then** visiting hours, location building/floor, and emergency contact details are shown.
2. **Given** a visitor on `/faq`, **When** searching for a topic (e.g., "insurance", "visiting hours"), **Then** matching instant answers are displayed.

---

## 3. Logic & Data Architecture (LDA-PO)

### Logic
- **Reference Code Generation**: Cryptographically secure random uppercase string prefixed with `HOSP-`.
- **Status Lifecycle**: `Open` -> `In Progress` -> `Resolved` -> `Closed`.
- **Urgency Levels**: `Routine`, `Urgent`, `Critical` (with visual badge).
- **Public vs Internal Notes**: Staff can post public replies (visible to patient via tracker) and internal notes (staff-only).

### Data Structure
- `Inquiry` (`id`, `ticket_code`, `patient_name`, `email`, `phone`, `category_id`, `department_id`, `urgency`, `subject`, `message`, `status`, `resolved_at`, `timestamps`)
- `InquiryResponse` (`id`, `inquiry_id`, `user_id`, `response_text`, `is_internal`, `timestamps`)
- `Department` (`id`, `name`, `slug`, `location`, `phone`, `operating_hours`, `timestamps`)
- `FaqItem` (`id`, `category`, `question`, `answer`, `order`, `timestamps`)

### Architecture
- Application base: `apps/app-01`
- Framework: Laravel 13 (PHP 8.3+)
- Frontend: Tailwind CSS responsive layout (patient portal + desk workspace)
- Database: SQLite / MySQL compatible Eloquent models & migrations

---

## 4. Quality Gates Checklist

- [x] Scope: Self-contained MVP addressing customer service & patient inquiries.
- [x] Testability: Every user story has independent test & G/W/T acceptance scenarios.
- [x] Separation: Requirements specify what the system does without premature internal wiring details.
