## ADDED Requirements

### Requirement: Policyholder files a First Notice of Loss (FNOL)
The system SHALL allow an authenticated policyholder to open a claim against one of their active policies by submitting incident details: date/time of incident, location, incident type (collision, theft, third-party, other), and a free-text description.

#### Scenario: Submit a valid FNOL
- **WHEN** an authenticated policyholder with an active policy submits incident date, location, type, and description
- **THEN** the system creates a Claim with status `submitted`, links it to the policy and tenant, records a submitted timestamp, and returns a claim reference number

#### Scenario: Reject FNOL without an active policy
- **WHEN** a policyholder attempts to file a claim referencing a policy that is expired or not theirs
- **THEN** the system rejects the submission with a validation error and does not create a Claim

### Requirement: Draft and resume
The system SHALL allow a policyholder to save an in-progress claim as a `draft` and resume it later before submitting.

#### Scenario: Save then resume a draft
- **WHEN** a policyholder saves a partially completed claim
- **THEN** the system stores it with status `draft` and allows the same policyholder to retrieve and submit it later

### Requirement: Bilingual intake
The system SHALL present all intake form labels, validation messages, and incident-type options in both Khmer and English, with Khmer as the default.

#### Scenario: Khmer default rendering
- **WHEN** a policyholder opens the FNOL form without an explicit language preference
- **THEN** the form renders in Khmer and offers a toggle to English
