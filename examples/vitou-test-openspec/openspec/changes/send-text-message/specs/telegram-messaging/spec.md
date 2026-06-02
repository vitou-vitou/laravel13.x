## ADDED Requirements

### Requirement: Send text message to a chat

The system SHALL send a plain-text message to a single Telegram chat identified by a caller-supplied `chat_id`. The same operation MUST serve all destination types — channel, group, and private chat — which differ only by the value of `chat_id`. The message MUST be delivered via the Telegram Bot API `sendMessage` method.

#### Scenario: Send to a channel by username

- **WHEN** the caller requests to send text with a `chat_id` of `"@mychannel"` and a non-empty message
- **THEN** the system sends the message to that channel via `sendMessage`
- **AND** reports success to the caller

#### Scenario: Send to a group by numeric id

- **WHEN** the caller requests to send text with a `chat_id` of `"-1001234567890"` and a non-empty message
- **THEN** the system sends the message to that group via `sendMessage`
- **AND** reports success to the caller

#### Scenario: Send to a private chat by numeric user id

- **WHEN** the caller requests to send text with a `chat_id` of `123456789` and a non-empty message
- **THEN** the system sends the message to that private chat via `sendMessage`
- **AND** reports success to the caller

### Requirement: Validate input before sending

The system SHALL validate inputs before making any network call. It MUST reject an empty or whitespace-only message and MUST NOT call the Telegram API in that case. It MUST fail fast with a clear error when no bot token is configured.

#### Scenario: Reject empty message text

- **WHEN** the caller requests to send text with an empty or whitespace-only message
- **THEN** the system returns a validation error
- **AND** makes no call to the Telegram API

#### Scenario: Fail fast when token is missing

- **WHEN** a send is requested but no bot token is configured
- **THEN** the system fails immediately with a configuration error
- **AND** makes no call to the Telegram API

### Requirement: Surface Telegram API errors

The system SHALL surface errors returned by the Telegram Bot API to the caller rather than silently ignoring them. This includes errors such as an unknown chat or the bot not being a member of the target chat.

#### Scenario: Unknown chat id

- **WHEN** a send is requested with a `chat_id` that does not exist or that the bot cannot reach
- **THEN** the Telegram API returns an error response
- **AND** the system surfaces that error to the caller, including the Telegram error description

#### Scenario: Bot not a member of the target chat

- **WHEN** a send is requested to a channel or group the bot has not been added to
- **THEN** the Telegram API rejects the request
- **AND** the system surfaces that error to the caller
