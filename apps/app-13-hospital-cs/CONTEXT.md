# Hospital Customer Service

Where patients, families and hospital staff record and resolve non-clinical issues with the hospital. It holds no medical data.

## Language

### Issues

**Case**:
One issue raised with the hospital, which is a Complaint, an Enquiry or Feedback. Each one has a Reference number.
_Avoid_: ticket, issue, request

**Complaint**:
A Case saying something went wrong, which needs an owner and a resolution.
_Avoid_: grievance, problem, incident

**Enquiry**:
A Case asking a general question, such as opening hours, prices or required documents.
_Avoid_: inquiry, question, request

**Feedback**:
A Case that is logged for the record only, such as praise or a suggestion, with no follow-up owed.
_Avoid_: comment, review, survey

**Reference**:
The number a Raiser uses to check a Case's status without an account.
_Avoid_: ticket number, case ID, tracking code

### People

**Raiser**:
The person a Case is about and who hears back, usually a patient or family member, who may never log in.
_Avoid_: customer, user, complainant, client

**Agent**:
A hospital staff member on the customer-service desk who logs and handles Cases.
_Avoid_: CS staff, operator, officer

**Department**:
A hospital unit (such as billing, OPD, pharmacy or lab) that a Case can be reassigned to.
_Avoid_: unit, team, ward

### Boundaries

**Non-clinical**:
A Case holds only a free-text description and a patient reference, never a diagnosis, results or treatment details.
_Avoid_: medical, clinical record

### Lifecycle

**Status**:
The stage a Case is at, which is one of Open, Assigned, In progress, Resolved or Closed.
_Avoid_: state, phase, step

**Resolved**:
The Agent has answered or fixed the Case, but the Raiser can still Reopen it.
_Avoid_: done, completed, fixed

**Closed**:
The final Status, after which a Case can no longer change. Feedback goes straight here.
_Avoid_: archived, finished

**Reopen**:
Moving a Resolved Case back to In progress because the Raiser isn't satisfied.
_Avoid_: re-raise, escalate

**Response target**:
The deadlines a Complaint must meet, a first reply within 24h and a resolution within 7 days.
_Avoid_: SLA, KPI, deadline

**Overdue**:
A Complaint that has missed a Response target.
_Avoid_: late, breached, escalated

### Staff and routing

**Supervisor**:
A staff member who sees every Case, reassigns Cases between Agents and watches Overdue ones.
_Avoid_: manager, admin, lead

**Owner**:
The one Agent accountable for a Case, who stays the Owner even when a Department is involved.
_Avoid_: assignee, handler

**Internal note**:
A remark on a Case that only staff can see, used for chasing a Department.
_Avoid_: comment, private message

**Topic**:
What a Case is about, picked by the Raiser from an admin-editable list (such as Billing, Waiting time or Staff attitude).
_Avoid_: category, tag, type

**Attachment**:
An image or PDF a Raiser adds to a Case, such as a photo or receipt, which must also be non-clinical.
_Avoid_: file, upload, document

### Raiser follow-up

**Status check**:
A Raiser looking up their Case by entering its Reference together with the phone number they gave.
_Avoid_: tracking, login, portal

**Rating**:
The 1–5 score a Raiser gives once their Case is Resolved.
_Avoid_: review, CSAT, survey

**Anonymise**:
Removing a Raiser's contact details from a Case 2 years after it is Closed, while keeping the Case text for reports.
_Avoid_: delete, purge, archive
