# CareDesk: Hospital Customer Service & Inquiries Implementation Plan

Change: `.specify/specs/001-hospital-customer-service/spec.md`

## LDA-PO Delivery Plan

1. **Logic & Data Structure (L & D)**
   - [x] Migrations: `hospital_departments`, `hospital_inquiries`, `hospital_inquiry_responses`, `hospital_faqs`
   - [x] Models: `HospitalDepartment`, `HospitalInquiry`, `HospitalInquiryResponse`, `HospitalFaq`
   - [x] Enums/Constants: Status (`open`, `in_progress`, `resolved`, `closed`), Urgency (`routine`, `urgent`, `critical`), Categories (`general`, `appointment`, `billing`, `complaint`, `records`, `pharmacy`)
   - [x] Database Seeder: `HospitalCustomerServiceSeeder` with realistic hospital departments, FAQs, and demo tickets

2. **Architecture & Business Logic (A)**
   - [x] Ticket code generator helper (`HOSP-YYYY-XXXXX`)
   - [x] `HospitalInquiryController` for patient-facing routes:
     - Portal home (`/hospital`)
     - Submit inquiry (`/hospital/inquiry/new`, `/hospital/inquiry`)
     - Track ticket status & responses (`/hospital/track`)
     - Department directory (`/hospital/departments`)
     - FAQs (`/hospital/faq`)
   - [x] `HospitalDeskController` for staff customer service desk:
     - Queue & triage dashboard (`/hospital/desk`)
     - Ticket view & reply conversation thread (`/hospital/desk/ticket/{ticket_code}`)
     - Internal notes and status transitions (`/hospital/desk/ticket/{ticket_code}/respond`)

3. **Portal & UI Design (P)**
   - [x] Hospital CareDesk layout with emergency hotline, navigation, and flash notifications (`resources/views/hospital/layout.blade.php`)
   - [x] Public patient portal views (landing, submit inquiry, tracking status timeline, departments, FAQ)
   - [x] Staff customer service workspace (metrics bar, filterable tickets table, conversation view, quick reply drawer)

4. **Others & Verification (O)**
   - [x] Feature tests for public inquiry submission & tracking (`tests/Feature/HospitalInquiryTest.php`)
   - [x] Feature tests for customer service desk triage & response (`tests/Feature/HospitalDeskTest.php`)
   - [x] Run test suite and verify end-to-end flow: 10 tests, 43 assertions passed