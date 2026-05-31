# Telegram Study Bot

Laravel 13 service that sends the **8-Principle Study Packet** (`180-laravel-project-types-study-packet.md`) to users via Telegram.

Source document (repo root): `docs/study/180-laravel-project-types-study-packet.md`  
Bundled copy: `resources/study-packets/180-laravel-project-types-study-packet.md`

## Commands

| Telegram command | Action |
|------------------|--------|
| `/start` | Welcome message |
| `/study` | Send study packet as `.md` document |
| `/packet`, `/doc` | Same as `/study` |
| `/help` | Command list |

The packet is ~27 KB Markdown — too long for one chat message, so the bot uses **sendDocument**.

## Setup

```bash
cd examples/telegram-study-bot
composer install
cp .env.example .env
php artisan key:generate
```

1. Open [@BotFather](https://t.me/BotFather) → `/newbot` → copy token.
2. Add to `.env`:

```env
TELEGRAM_BOT_TOKEN=123456:ABC...
```

3. Optional — restrict access:

```env
TELEGRAM_ALLOWED_CHAT_IDS=your_chat_id
```

(Get your chat ID from [@userinfobot](https://t.me/userinfobot).)

## Run (long polling — local dev)

```bash
php artisan telegram:poll
```

One batch only:

```bash
php artisan telegram:poll --once
```

## Run (webhook — production)

1. Serve the app over HTTPS (e.g. `php artisan serve` + ngrok, or Forge).

2. Set secret in `.env`:

```env
TELEGRAM_WEBHOOK_SECRET=random-long-string
```

3. Register webhook (replace values):

```bash
curl "https://api.telegram.org/bot<TOKEN>/setWebhook" \
  -d "url=https://your-host/telegram/webhook" \
  -d "secret_token=<TELEGRAM_WEBHOOK_SECRET>"
```

4. Telegram POSTs updates to `/telegram/webhook`.

## Sync study packet from repo

If you update the master file at `docs/study/`:

```bash
cp ../../docs/study/180-laravel-project-types-study-packet.md \
  resources/study-packets/180-laravel-project-types-study-packet.md
```

Or point `.env` at the repo file:

```env
STUDY_PACKET_PATH=D:/laravel13.x/docs/study/180-laravel-project-types-study-packet.md
```

## Tests

```bash
php artisan test
```

## Health check

`GET /` returns JSON with `telegram_configured` and `study_packet_exists`.
