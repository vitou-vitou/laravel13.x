# 02: Digital Queue Token & Live Counter Caller

**What to build:** An end-to-end digital queue ticketing system allowing arriving patients to pull a token on mobile and track their live queue position, while reception and triage staff call numbers from a dedicated counter console.

**Blocked by:** 01: Hospital Foundation & Guest FAQ Decision Tree

**Status:** done

- [x] Patient can request a digital queue token by selecting a department and providing an optional mobile phone number.
- [x] Patient receives a unique token code (e.g. `GEN-102` or `CARD-045`) and sees their live position in line and estimated wait time.
- [x] Front desk / counter staff can view the queue list for their assigned counter or department.
- [x] Staff can call "Next Patient", recall a number, or mark a token as "Serving", "Completed", or "No-Show".
- [x] Real-time updates push status changes to the patient's mobile screen when their number is called.
