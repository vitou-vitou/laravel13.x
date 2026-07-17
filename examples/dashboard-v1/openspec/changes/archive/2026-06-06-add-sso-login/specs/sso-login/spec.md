### Requirement: Google SSO login

The system SHALL allow users to sign in with Google OAuth when Google credentials are configured.

#### Scenario: Redirect to Google

- **WHEN** a guest visits the Google SSO redirect route and Google is configured
- **THEN** they are redirected to Google's authorization page

#### Scenario: New SSO user

- **WHEN** Google returns a new user email
- **THEN** a user record is created with `sso_provider` and `sso_id`, email marked verified, and `customer` role assigned

#### Scenario: Link existing account

- **WHEN** Google returns an email matching an existing user without SSO fields
- **THEN** the existing account is linked and the user is logged in with existing roles preserved

#### Scenario: Provider disabled

- **WHEN** Google credentials are not configured
- **THEN** SSO routes return 404 and the login page omits the Google button
