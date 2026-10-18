# 04: Working a Case: ownership, Status machine, updates, notes

**What to build:** An Agent takes ownership of a Case, moves it through its Statuses, adds public updates and Internal notes, and tags a Department while staying Owner.

**Blocked by:** 03

**Status:** ready-for-agent

- [ ] An Agent can take ownership of an Open Case, which sets Owner and Assigned
- [ ] Allowed moves are Assigned, In progress, Resolved, Closed, plus Enquiry from Open straight to Resolved
- [ ] Illegal moves are refused, and a Closed Case can't change
- [ ] Status rules live in one place
- [ ] A public update is visible to the Raiser on Status check, and an Internal note isn't
- [ ] The first public reply on a Case sets first-replied-at, and Resolved and Closed set their timestamps
- [ ] Department can be set without changing the Owner
- [ ] Filament feature tests cover every allowed and refused move

