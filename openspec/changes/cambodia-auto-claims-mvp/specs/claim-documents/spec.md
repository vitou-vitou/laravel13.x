## ADDED Requirements

### Requirement: Upload claim photos and documents
The system SHALL allow a policyholder to attach one or more images or documents (e.g. damage photos, police report, driver license) to a claim they own.

#### Scenario: Attach a photo to a claim
- **WHEN** a policyholder uploads an image to their claim
- **THEN** the system stores the file in object storage, creates a ClaimDocument linked to the claim with type, filename, size, and captured metadata, and makes it visible in the claim detail

#### Scenario: Reject unsupported or oversized files
- **WHEN** a policyholder uploads a file exceeding the size limit or of a disallowed type
- **THEN** the system rejects it with a clear bilingual error and stores nothing

### Requirement: Low-bandwidth tolerant uploads
The system SHALL tolerate slow or interrupted connections by accepting client-side compressed images and supporting retry of a failed upload without duplicating an already-stored document.

#### Scenario: Retry after a failed upload
- **WHEN** an upload fails mid-transfer and the client retries with the same idempotency key
- **THEN** the system completes the upload once and does not create a duplicate ClaimDocument

### Requirement: Basic fraud metadata capture
The system SHALL record available image metadata (capture timestamp, and geolocation when provided) and flag when the same file hash is attached to more than one claim.

#### Scenario: Duplicate image across claims
- **WHEN** an uploaded file's content hash matches a document already attached to a different claim
- **THEN** the system stores the document but sets a `duplicate_suspected` flag visible to adjusters
