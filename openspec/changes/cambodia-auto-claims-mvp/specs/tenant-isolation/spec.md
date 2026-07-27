## ADDED Requirements

### Requirement: Per-insurer tenancy
The system SHALL associate every Policy, Claim, ClaimDocument, Payout, Adjuster, and policyholder User with exactly one Tenant (insurer), and SHALL scope all queries to the acting user's tenant.

#### Scenario: Data scoped to tenant
- **WHEN** any user reads claims, policies, or payouts
- **THEN** the system returns only records belonging to that user's tenant

#### Scenario: Cross-tenant write blocked
- **WHEN** a user attempts to create or modify a record under a tenant other than their own
- **THEN** the system denies the operation

### Requirement: Single live tenant at launch, multi-tenant ready
The system SHALL support operating with a single insurer live at launch while keeping the schema and access rules multi-tenant, so additional insurers can be onboarded without a data migration.

#### Scenario: Onboard a second insurer
- **WHEN** a second insurer is added as a new Tenant
- **THEN** their users, policies, and claims are fully isolated from the first insurer with no schema change required
