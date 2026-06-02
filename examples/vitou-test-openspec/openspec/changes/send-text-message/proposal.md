## Why

We need an outbound Telegram notification path so the application can push plain-text messages to a channel, group, or private chat. This is the foundational slice; attachment support builds on it in a later change.

## What Changes

- Introduce a Telegram messaging capability that sends a plain-text message to a single chat via the Telegram Bot API.
- One code path serves all three destination types (channel, group, private chat); they differ only by the `chat_id` value supplied by the caller.
- Validate input before any network call: reject empty message text, and fail fast when no bot token is configured.
- Surface Telegram API errors (e.g. unknown chat, bot not a member of the target chat) back to the caller rather than swallowing them.

Non-goals for this change: attachments (separate stacked change), message formatting (`parse_mode` / Markdown / HTML), receiving messages or handling commands/webhooks, and retry / rate-limit handling.

## Capabilities

### New Capabilities
- `telegram-messaging`: Sending outbound text messages to a Telegram chat identified by `chat_id`, with input validation and error surfacing.

### Modified Capabilities
<!-- None. This is a greenfield capability. -->

## Impact

- New capability `telegram-messaging` with a single send-text entry point.
- New dependency on the Telegram Bot API (`https://api.telegram.org/bot<TOKEN>/sendMessage`) over HTTPS.
- Requires a bot token to be provided via environment / configuration (exact mechanism left to implementation).
- No existing code or APIs modified; additive only.
