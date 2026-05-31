# Jira V2 Example

A Laravel 13 example demonstrating how to integrate with Jira's REST API and process webhooks.

## Features

- Basic authentication with Laravel Breeze
- Example routes for Jira API interactions
- Webhook endpoint to receive and process Jira events
- Example controller showing how to handle incoming webhook payloads

## Setup

```bash
cd examples/jira-v2
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install && npm run build
php artisan serve
```

## Webhook Example

A webhook route is registered at `/api/jira/webhook` (POST) handled by `App\Http\Controllers\WebhookController`.

To test locally, you can use a tool like [ngrok](https://ngrok.com/) to expose your local server and configure the webhook in Jira to point to `https://your-subdomain.ngrok.io/api/jira/webhook`.

The controller logs the payload and returns a 200 OK response.

## Test

```bash
php artisan test
```