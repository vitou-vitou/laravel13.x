## ADDED Requirements

### Requirement: Policyholder status timeline
The system SHALL show a policyholder the current status and chronological history of their claim: `draft`, `submitted`, `under_review`, `needs_more_info`, `approved`, `rejected`, `paid`.

#### Scenario: View claim timeline
- **WHEN** a policyholder opens their claim
- **THEN** the system shows the current status and an ordered timeline of status changes with timestamps, in the policyholder's language

#### Scenario: Only own claims visible
- **WHEN** a policyholder requests a claim they do not own
- **THEN** the system denies access

### Requirement: Bilingual status notifications
The system SHALL notify the policyholder on each material status change (submitted, needs_more_info, approved, rejected, paid) via their preferred channel, in Khmer or English per their preference.

#### Scenario: Notify on approval
- **WHEN** a claim transitions to `approved`
- **THEN** the system sends the policyholder a notification in their chosen language describing the approval and next step
