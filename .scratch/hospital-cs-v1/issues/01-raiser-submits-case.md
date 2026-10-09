# 01: Raiser submits a Case and gets a Reference

**What to build:** A new standalone Laravel 13 app with a public page where a Raiser submits a Case (Complaint, Enquiry or Feedback) with a Topic, name, phone, optional email and patient reference, and a description, and is shown their Reference.

**Blocked by:** None (can start immediately)

**Status:** ready-for-agent

- [ ] App skeleton exists at the agreed location and boots, with SQLite for local dev
- [ ] Topic list is seeded (Billing, Waiting time, Staff attitude, Facilities, Appointment, Other)
- [ ] Submitting a valid form saves a Case with Status Open, or Closed for Feedback
- [ ] Reference in the format HCS-YYMM-XXXX is generated once, unique, with no 0/O/1/I
- [ ] Name, phone, type, Topic and description are required, and errors are shown on the form
- [ ] A filled honeypot field is rejected, and repeat submits are rate-limited
- [ ] Feature tests through public HTTP, plus a unit test for the Reference generator

