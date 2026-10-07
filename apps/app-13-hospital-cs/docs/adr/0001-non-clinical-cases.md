# Cases are strictly non-clinical

Hospital customer service handles complaints, enquiries and feedback, but storing clinical data (diagnoses, results, treatment) would make this app a medical record system, with the privacy, access-control and retention duties that come with that. We decided a Case holds only a free-text description, a Topic, the Raiser's contact details and an optional patient reference, and staff point to the hospital's record system for anything clinical. Attachments follow the same rule, and Agents remove any that show medical documents.

## Consequences

- Agents can't resolve a Complaint that needs clinical facts inside this app. They take it to the record system using the patient reference.
- Adding clinical fields later means revisiting this ADR first, along with consent, access control and retention.
