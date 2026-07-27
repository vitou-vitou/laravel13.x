## ADDED Requirements

### Requirement: Adjuster review queue
The system SHALL provide adjusters a queue of submitted claims scoped to their tenant, filterable by status and sortable by submission time.

#### Scenario: View pending claims
- **WHEN** an authenticated adjuster opens the review console
- **THEN** the system lists claims for their tenant with status `submitted` or `more_info_provided`, showing reference, policyholder, incident type, and age

#### Scenario: Tenant isolation in queue
- **WHEN** an adjuster of tenant A opens the queue
- **THEN** the system never shows claims belonging to tenant B

### Requirement: Review a claim and request more information
The system SHALL let an adjuster open a claim detail (incident data + documents) and, if information is insufficient, move it to `needs_more_info` with a bilingual message to the policyholder.

#### Scenario: Request more info
- **WHEN** an adjuster marks a claim as needing more information with a note
- **THEN** the system sets status `needs_more_info`, records the note, and notifies the policyholder in their language

### Requirement: Approve or reject a claim
The system SHALL let an adjuster approve or reject a claim, requiring a reason on rejection, and SHALL record who decided and when in an audit trail.

#### Scenario: Approve a claim
- **WHEN** an adjuster approves a claim
- **THEN** the system sets status `approved`, records the adjuster id and timestamp, and enables payout creation

#### Scenario: Reject requires a reason
- **WHEN** an adjuster attempts to reject a claim without a reason
- **THEN** the system blocks the action and requires a rejection reason before setting status `rejected`
