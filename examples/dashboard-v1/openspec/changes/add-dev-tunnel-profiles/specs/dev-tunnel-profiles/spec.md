## ADDED Requirements

### Requirement: Tunnel profile storage

The system SHALL store dev tunnel profiles with `name`, `domain`, `herd_host`, `is_active`, and optional health metadata.

#### Scenario: Unique profile name

- **WHEN** staff creates a tunnel with a duplicate name
- **THEN** validation fails

### Requirement: Single active tunnel

The system SHALL allow at most one tunnel profile marked active.

#### Scenario: Activate profile

- **WHEN** staff activates a tunnel profile
- **THEN** all other profiles are deactivated and `.env` OAuth keys sync to that domain

### Requirement: Tunnel admin access

The system SHALL expose Filament CRUD at `/admin/tunnels` only when `tunnel.enabled` is true and the user has `manage_dev_tunnels`.

#### Scenario: Staff without permission

- **WHEN** a user lacks `manage_dev_tunnels`
- **THEN** they cannot access tunnel admin pages

### Requirement: Tunnel health verify

The system SHALL probe `https://{domain}/login` and store last verification status on the profile.

#### Scenario: Healthy tunnel

- **WHEN** the login page returns HTTP 200
- **THEN** `last_verified_status` is `ok`
