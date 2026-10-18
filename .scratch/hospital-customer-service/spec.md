# Hospital Customer Service Portal Spec

## Problem Statement

Hospital patients face severe customer service friction: long wait times, anxiety over physical queue positions, repetitive telephone inquiries about doctor availability and visiting hours, and uncertainty about whether lab/test reports are ready for pickup. Concurrently, hospital desk staff are overwhelmed with duplicate general inquiries, lack a unified triage system, and cannot efficiently route urgent patient questions to the appropriate departments.

## Solution

A responsive, two-sided web portal built with Laravel and Inertia.js (Vue 3 + Tailwind CSS):
1. **Patient Portal (Mobile-First Web)**: Allows patients and guests to instantly resolve FAQs via an automated triage menu or AI assistant fallback, take and monitor real-time queue tokens, request outpatient appointment slots, and check medical report readiness using a tracking reference.
2. **Staff Triage Desk (Backoffice)**: Enables customer support agents and department nurses to monitor live inquiry queues, chat with patients in real time via WebSockets, answer or reassign tickets, advance physical waiting queue numbers, and mark report pickup statuses.

---

## User Stories

1. As a prospective patient, I want to view hospital visiting hours, department locations, and general policies without logging in, so that I can get immediate answers without calling the desk.
2. As a patient, I want to use an interactive symptom/inquiry triage assistant, so that I am guided to the correct medical department.
3. As a patient, I want to ask natural-language questions to an automated hospital assistant, so that I get fast answers from verified hospital guidelines when the standard FAQ menu does not cover my question.
4. As a patient, I want an automated conversation to offer a seamless escalation to a human support agent, so that complex or sensitive inquiries are not blocked by a bot.
5. As an arriving patient, I want to generate a digital queue token on my mobile phone, so that I do not need to stand in physical ticket lines.
6. As a patient waiting in the hospital lobby or cafeteria, I want to see live updates of my queue position, so that I know exactly when to walk to the consultation or triage room.
7. As a patient, I want to submit an appointment booking request specifying preferred department, doctor, date, and time slot, so that hospital staff can confirm my visit.
8. As a patient, I want to receive live updates or notifications when my appointment request is confirmed, rescheduled, or cancelled, so that I can plan my visit accurately.
9. As a patient who completed laboratory or diagnostic tests, I want to enter my tracking number to check if my results are ready for pickup, so that I avoid unnecessary trips to the hospital.
10. As a patient in an active support conversation, I want to send and receive real-time messages with customer service staff, so that my queries are resolved without telephone wait times.
11. As a hospital customer service agent, I want a unified dashboard showing all pending patient inquiries and active chats, so that I can manage incoming support volume.
12. As a hospital support agent, I want incoming inquiries automatically categorized and prioritized by department, so that urgent concerns are addressed first.
13. As a hospital support agent, I want to reassign an inquiry to another specialized department desk, so that the right department handles the patient.
14. As a triage nurse or front desk clerk, I want to call the next patient token and update counter numbers in real time, so that physical queue displays and patient mobile screens synchronize instantly.
15. As a records room clerk, I want to update test report readiness statuses by patient reference code, so that patients see instant pickup readiness without accessing private clinical records.
16. As a hospital support supervisor, I want to review conversation histories and ticket resolution times, so that customer service quality and response SLAs can be audited.

---

## Implementation Decisions

### Modules & Architecture
- **Patient Self-Service Module**: Public landing, interactive FAQ decision tree, AI assistant fallback interface, guest queue ticket generation, appointment slot request wizard, and report status lookup.
- **Staff Customer Service Desk**: Authenticated Inertia.js dashboard with live ticket queue, multi-agent chat interface, counter token caller, and status dispatchers.
- **Real-Time Communication Module**: First-party WebSocket broadcasting (Laravel Reverb) for queue position progression, new patient message broadcasting, and agent typing/presence indicators.
- **Automated Triage & Assistant Engine**: Two-stage triage service. Stage 1 executes deterministic category routing; Stage 2 provides bounded context-grounded AI answering with strict medical disclaimers and human handoff triggers.

### Architectural Decisions
- **Monolith with Inertia.js**: Laravel backend with Inertia.js (Vue 3) frontend ensures zero API synchronization drift while delivering SPA reactivity.
- **Non-Clinical Boundary**: The application strictly isolates customer service data (inquiries, queue tokens, appointment booking times, pickup readiness flags). It does not store medical records, diagnoses, or clinical test values, ensuring strict data protection and HIPAA/GDPR surface minimization.
- **Fallback Resilience**: Polling fallback mechanism ensures queue tracking and chat remain fully functional if WebSocket connections degrade on mobile networks.

### Schema & Data Models
- `inquiries` / `support_tickets`: Tracks patient session, contact info, department ID, category, status (bot, queued, open, escalated, resolved), and priority.
- `messages`: Stores chat exchanges between patient, bot, and staff agents with timestamps and read receipts.
- `queue_tokens`: Tracks token number (e.g., A-104), department/counter ID, issuance timestamp, estimated wait time, and state (waiting, called, serving, completed, no-show).
- `appointment_requests`: Captures patient contact, requested specialty/doctor, preferred time slots, status (pending, confirmed, rejected), and staff notes.
- `report_statuses`: Lightweight reference model storing tracking code, patient phone last 4 digits, report department, and readiness state (in-progress, ready-for-pickup, collected).
- `departments` & `staff_profiles`: Department taxonomy, operating hours, counter definitions, and support staff assignments.

### Interfaces & Contracts
- Single testable boundary at the HTTP/Inertia controller layer.
- WebSocket broadcast events: `QueueTokenUpdated`, `NewInquiryMessage`, `InquiryAssigned`.

---

## Testing Decisions

- **Testing Seams**: The single primary seam is the HTTP controller layer using Laravel Feature Tests (`Pest` / `PHPUnit`). Every patient journey (ticket creation, queue pulling, appointment submission, triage resolution) and staff action (token calling, message reply, ticket closing) is verified end-to-end through HTTP assertions.
- **Realtime Verification**: Event broadcasting verified via Laravel's `Event::fake()` and `Broadcast::fake()` to guarantee correct event payloads without requiring external daemon infrastructure during CI.
- **Deterministic Triage Verification**: Unit and feature tests ensure triage trees route consistently and that non-clinical compliance disclaimers are always attached to responses.

---

## Out of Scope

- Electronic Health Record (EHR / EMR) integration or diagnosis storage.
- Telemedicine video streaming consultations.
- Billing, payment gateway, and insurance claims processing.
- Inpatient bed management and operating room scheduling.

---

## Further Notes

- Designed to run cleanly inside `laravel13.x` using standard Laravel conventions.
- Mobile responsiveness on patient view is paramount (optimized for smartphones in hospital waiting rooms).
