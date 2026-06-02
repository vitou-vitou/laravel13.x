## 1. Configuration

- [ ] 1.1 Add a bot-token configuration source (env / config) with no token committed
- [ ] 1.2 Add a config accessor that reads the token and reports when it is missing

## 2. Core Implementation

- [ ] 2.1 Create the `telegram-messaging` module / service scaffold
- [ ] 2.2 Implement a send-text operation taking `chat_id` and message text
- [ ] 2.3 Build the `sendMessage` HTTPS request to `https://api.telegram.org/bot<TOKEN>/sendMessage`
- [ ] 2.4 Parse the Telegram response and detect `ok: false`

## 3. Validation & Errors

- [ ] 3.1 Reject empty / whitespace-only text before any network call (validation error)
- [ ] 3.2 Fail fast with a configuration error when the token is missing
- [ ] 3.3 Surface Telegram API errors to the caller, including the `description`
- [ ] 3.4 Ensure the token / full request URL is never logged or included in errors

## 4. Tests

- [ ] 4.1 Test send to channel `@name`, group `-100…`, and private numeric id (mock the API)
- [ ] 4.2 Test empty-text rejection makes no API call
- [ ] 4.3 Test missing-token fails fast with no API call
- [ ] 4.4 Test unknown chat / bot-not-member error is surfaced with its description

## 5. Docs

- [ ] 5.1 Document the bot-token setup and the precondition that the bot must be a member of the target chat
- [ ] 5.2 Document the send-text usage with one example per destination type
