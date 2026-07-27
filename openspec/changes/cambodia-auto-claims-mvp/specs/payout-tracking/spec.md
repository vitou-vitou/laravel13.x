## ADDED Requirements

### Requirement: Record payout intent for an approved claim
The system SHALL allow creation of a Payout record against an approved claim, capturing amount, currency (KHR or USD), intended rail (e.g. ABA / Wing / Bakong — pending confirmation), and an optional external reference.

#### Scenario: Create payout for approved claim
- **WHEN** an adjuster or finance user records a payout for an `approved` claim with amount and currency
- **THEN** the system creates a Payout with status `pending` linked to the claim and tenant

#### Scenario: Block payout for non-approved claim
- **WHEN** a payout is attempted for a claim not in `approved` status
- **THEN** the system rejects it

### Requirement: Mark payout as paid
The system SHALL allow an authorized user to mark a payout `paid` with a settlement reference and date, which transitions the claim to `paid`.

#### Scenario: Confirm settlement
- **WHEN** an authorized user marks a pending payout as paid with a reference
- **THEN** the system sets the Payout to `paid`, sets the claim status to `paid`, and notifies the policyholder

### Requirement: No automated money movement in MVP
The system SHALL NOT execute real fund transfers through any payment rail in the MVP; payout execution is recorded manually until a rail integration is confirmed and scoped.

#### Scenario: MVP boundary
- **WHEN** a payout is created in the MVP
- **THEN** the system records intent and settlement status only, and performs no external money-movement API call
