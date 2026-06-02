## Context

This is the first of two stacked changes building a Telegram outbound messaging capability. It introduces a new external dependency (the Telegram Bot API) and a small amount of credential handling (a bot token), which warrants a short design note before coding. The second change will layer attachments on top of the entry point defined here, so the seam between "send text" and "send attachment" matters.

The capability is greenfield — no existing messaging code to integrate with. The host project follows the parent repository's Laravel/PHP conventions, but the design keeps the implementation mechanism loosely specified so the spec stays portable.

## Goals / Non-Goals

**Goals:**
- One entry point that sends plain text to any chat type via a caller-supplied `chat_id`.
- Validate input (non-empty text, configured token) before any network call.
- Surface Telegram API error descriptions to the caller.
- Shape the entry point so attachments can extend it later without a breaking change.

**Non-Goals:**
- Attachments / media (next stacked change).
- Message formatting (`parse_mode`, Markdown, HTML).
- Inbound handling: webhooks, polling, commands.
- Retries, backoff, or rate-limit handling.
- Resolving or discovering `chat_id` values — the caller provides them.

## Decisions

**Decision: Single send-text operation keyed on `chat_id`, not per-destination methods.**
Channel, group, and private chat all use the Telegram `sendMessage` method; they differ only by `chat_id` value (`@username`, `-100…`, or a numeric user id). Collapsing them into one operation avoids three near-identical code paths.
- Alternative considered: separate `sendToChannel` / `sendToGroup` / `sendToUser`. Rejected — no behavioral difference, just a different argument; would triple surface area for no gain.

**Decision: Validate before calling the API.**
Empty text and missing token are checked locally and short-circuit before any HTTP request. This keeps failures fast, cheap, and free of network ambiguity.
- Alternative considered: let Telegram reject empty text. Rejected — wastes a round trip and conflates client errors with API errors.

**Decision: Token comes from environment / configuration, mechanism unspecified in the spec.**
The spec requires only that a token is "configured." The concrete source (env var, config file, secrets manager) is an implementation choice and is intentionally not pinned.
- Alternative considered: pass the token as a call argument. Rejected for the default path — keeps secrets out of call sites — but not forbidden by the spec.

**Decision: Surface Telegram errors with their `description`.**
Telegram returns `{ ok: false, error_code, description }`. The caller receives the description so unknown-chat and bot-not-member failures are diagnosable.
- Alternative considered: collapse all failures into a generic error. Rejected — loses the actionable detail callers need.

**Decision: Talk to `https://api.telegram.org/bot<TOKEN>/sendMessage` directly over HTTPS.**
A single method call; no SDK required for this slice. An SDK can be introduced later if attachments justify it.

## Risks / Trade-offs

- **Bot cannot message users who haven't started it / isn't in the target chat** → Out of scope to fix; surfaced as an API error so the caller learns why. Document the precondition.
- **Token leakage in logs or errors** → Never log the token or the full request URL (which embeds the token); surface only the Telegram `description`.
- **No retries means a transient network blip fails the send** → Accepted for this slice; retries are an explicit non-goal and a candidate for a future change.
- **Entry-point shape might not cleanly extend to attachments** → Mitigate by treating text as one message kind among future kinds, rather than hard-coding a text-only signature.

## Open Questions

- Should the return value be a typed result object or a thrown exception on failure? Defer to implementation; either satisfies "surface the error."
- Will change 2 (attachments) reuse this exact entry point or sit beside it? Lean toward reuse, but confirm when proposing change 2.
