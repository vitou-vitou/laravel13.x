# Spec: Hospital Customer Service v1 (Cases, Complaints, Enquiries)

Label: ready-for-agent

## Problem Statement

Patients and their families have no single place to raise a complaint, ask a general question, or leave feedback with the hospital, and no way to see what happened afterwards. Customer-service staff record issues in scattered places, nobody clearly owns each one, and supervisors can't see which complaints are late. So issues get lost, Raisers repeat themselves, and the hospital can't measure service quality.

## Solution

Hospital Customer Service is a standalone Laravel 13 app. Raisers (patients or family) use a public Khmer/English page, with no account needed, to submit a **Case**: a **Complaint**, an **Enquiry** or **Feedback**. They get a readable **Reference** and can run a **Status check** with that Reference plus their phone. **Agents** work in a Filament panel, where they log phone and walk-in Cases, own Cases, move them through Statuses, and keep **Internal notes** while chasing a **Department**. **Supervisors** see every Case, reassign Cases, watch **Overdue** Complaints, and use one dashboard. Cases are strictly **Non-clinical** (ADR 0001). Glossary: `apps/app-13-hospital-cs/CONTEXT.md`.

## User Stories

1. As a Raiser, I want to submit a Case from a public page without an account, so that I can raise an issue quickly.
2. As a Raiser, I want to choose whether my Case is a Complaint, an Enquiry or Feedback, so that it is handled the right way.
3. As a Raiser, I want to pick a Topic, so that my Case reaches the right people.
4. As a Raiser, I want to give my name and phone (required) and an email and patient reference (optional), so that the hospital can reach me.
5. As a Raiser, I want to attach up to 3 images or PDFs (max 5MB each), so that I can show receipts or photos.
6. As a Raiser, I want to get a Reference like `HCS-2610-4K7Q` after submitting, so that I can follow up by phone or online.
7. As a Raiser, I want to use the page in Khmer or English, so that I can understand it.
8. As a Raiser, I want to check my Case's Status with my Reference and phone, so that nobody else can see my Case.
9. As a Raiser, I want to see the public updates on my Case, so that I know what is happening.
10. As a Raiser who gave an email, I want an email when my Case's Status changes, so that I don't have to keep checking.
11. As a Raiser, I want to rate my Case from 1 to 5 once it is Resolved, so that the hospital hears how it went.
12. As a Raiser, I want to Reopen a Resolved Case when I'm not satisfied, so that my problem isn't dropped.
13. As a Raiser, I want clear errors on the form, so that I can fix mistakes before submitting.
14. As an Agent, I want to log in to the Filament panel, so that I can handle Cases.
15. As an Agent, I want to log a Case for a phone or walk-in Raiser, so that every issue is recorded in one place.
16. As an Agent, I want to see a list of Cases filtered by Status, type, Topic and Owner, so that I can find my work.
17. As an Agent, I want to take ownership of an Open Case, so that it is clear who is responsible.
18. As an Agent, I want to move a Case through Assigned, In progress and Resolved, so that the Raiser sees progress.
19. As an Agent, I want to resolve an Enquiry directly from Open, so that simple questions are quick.
20. As an Agent, I want Feedback to close straight away, so that it is logged without needing follow-up.
21. As an Agent, I want to add a public update the Raiser can see, so that I can answer them.
22. As an Agent, I want to add Internal notes that only staff can see, so that I can record how I'm chasing a Department.
23. As an Agent, I want to tag a Case with a Department while I stay its Owner, so that it's clear who I'm waiting on.
24. As an Agent, I want to see Attachments and remove any that show medical documents, so that the app stays Non-clinical.
25. As an Agent, I want to see an Overdue badge on Complaints that missed a Response target, so that I can prioritise.
26. As a Supervisor, I want to see every Case across all Agents, so that I have full oversight.
27. As a Supervisor, I want to reassign a Case to another Agent, so that I can balance work and cover absences.
28. As a Supervisor, I want a list of Overdue Complaints, so that I can step in.
29. As a Supervisor, I want a dashboard showing Case counts by type, Topic and Status, so that I can see trends.
30. As a Supervisor, I want the dashboard to show Overdue counts, average resolution time and average Rating, so that I can measure service.
31. As a Supervisor, I want to manage the Topic list, so that it fits our hospital.
32. As a Supervisor, I want to manage staff accounts and their role (Agent or Supervisor), so that access stays controlled.
33. As the hospital, I want a Raiser's contact details Anonymised 2 years after their Case is Closed, so that we don't keep personal data longer than needed.
34. As the hospital, I want the public form rate-limited and protected by a honeypot, so that spam doesn't flood the desk.
35. As the hospital, I want Cases to never hold clinical details, so that this app does not become a medical record.

## Implementation Decisions

- **Standalone app** at `apps/app-13-hospital-cs`, running Laravel 13 and the latest Filament. Staff log in through Filament's own auth, with no Passport. Local dev uses SQLite and production uses MySQL.
- **Roles**: Agent and Supervisor are a role on the staff user. A Supervisor can do everything an Agent can, and also reassign Cases, manage Topics, manage staff and see the dashboard.
- **Case** fields: Reference (unique), type (Complaint, Enquiry or Feedback), Topic, Status, Owner (nullable), Department (nullable), Raiser name, phone, email (nullable), patient reference (nullable), description, Rating (nullable), first-replied-at, resolved-at, closed-at, anonymised-at. Related records are Attachments (max 3), public updates, and Internal notes.
- **Status machine**, enforced in one place:
  - Open to Assigned, to In progress, to Resolved, to Closed.
  - An Enquiry can go from Open straight to Resolved. Feedback goes straight to Closed when it's created.
  - Reopen moves a Resolved Case back to In progress. A Closed Case can't change.
- **Reference**: `HCS-YYMM-XXXX` with 4 random characters, leaving out ones that are easy to confuse (0/O, 1/I). It's generated once and must be unique.
- **Status check**: Reference plus phone must both match, compared after normalising the phone number. It's rate-limited.
- **Response targets** (Complaints only): first reply within 24h and resolution within 7 days. Overdue is worked out at read time, with no escalation job.
- **Notifications**: an email to the Raiser on each Status change and public update, only if they gave an email. Emails are queued.
- **Anonymise**: a scheduled daily command blanks name, phone and email and sets anonymised-at on Cases Closed more than 2 years ago. The description and Rating are kept.
- **Spam**: a rate limit on the public submit and Status check routes, plus a honeypot field.
- **i18n**: Khmer and English using Laravel's translation files, switched by a locale toggle on the public pages.
- **Non-clinical** (ADR 0001): there are no clinical fields. Attachments are images or PDFs, max 3 per Case, max 5MB each.

## Testing Decisions

- Test external behaviour only, through two seams:
  1. **Public HTTP** feature tests: submitting a Case and getting a Reference, form validation, honeypot and rate limits, Status check (both match, and either one wrong), Rating, and Reopen.
  2. **Filament panel** feature tests, acting as an Agent or Supervisor: logging a Case, every allowed Status move and refusing illegal ones, public updates versus Internal notes, ownership and reassignment rules, the Overdue badge, and the dashboard numbers.
- Small unit tests for the Reference generator (format and uniqueness), plus running the Anonymise command directly.
- Use mail fakes for notifications. Use time travel for Response targets and the Anonymise window.
- Prior art is the Laravel feature tests in `apps/app-11-basic-filament/tests/Feature`.

## Out of Scope

Appointment booking, Department staff logins, SMS and Telegram, live chat, a patient account portal, any clinical data, and auto-escalation of Overdue Cases.

## Further Notes

- Glossary: `apps/app-13-hospital-cs/CONTEXT.md`. Context map: `CONTEXT-MAP.md`. Decision: `apps/app-13-hospital-cs/docs/adr/0001-non-clinical-cases.md`.
- A separate draft plan in `.scratch/hospital-customer-service/` (queue tokens, booking, chat, AI) isn't part of this spec.

