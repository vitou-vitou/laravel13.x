# Someone Outside the Building Now Depends on Your Admin. Stop Renaming JSON Keys on Friday.

A partner wants order updates. A warehouse app wants a file every morning. Another team wants their own rows, not yours. The panel is still where your staff click. Year four is the year those clicks become a promise other software can rely on.

The promise is a contract: a versioned export or API, a tenant boundary that does not leak, and a webhook that can fail without failing the staff click. Cleaner field names are not an emergency. They are a version.

## Name the consumer before you name the endpoint

If you cannot say who reads the payload, you are not in year four. You are guessing. Wait.

A partner, a mobile app, an internal team, a warehouse. Each one gets a surface you version. They do not get a peek at your tables, and they do not get a second admin "so they can look around." Your staff keep the panel you already have. Filament or Backpack does not get replaced by an API-shaped rewrite.

## A webhook is a letter, not a function call

When staff mark an order shipped, the screen does what it did last year. The same permission check runs. Then a job posts a small body:

```json
{
  "event": "order.shipped",
  "id": "evt_123",
  "occurred_at": "2026-09-27T10:00:00Z",
  "order_id": 55
}
```

Sign it with a secret from the environment. Store each attempt and the response code. Show the last status on the order so support can say "your URL returned 500" without reading worker logs. Retry with backoff. Stop after a fixed number of tries and leave the row red.

Do not send the webhook inside the HTTP request. Their timeout is not your staff's timeout. Do not rename `order_id` to `id` in place because it looked neater. Freeze v1. Put the break in v2, with a date, and tell them.

Laravel queues and signed URLs are the mechanisms. The event name is yours. Write the keys down next to the route the way you would write a public API, because that is what it is.

## One tenant rule, used everywhere

If you have teams or tenants, the scope belongs in one place the panel, the export, and the webhook all use. A global scope you forget on the export query is how tenant A downloads tenant B.

The test is the feature. Create two tenants. Assert the list, the file, and the payload each stay inside their own tenant. One missed `where` is the bug that matters this year, more than any CSS on the resource.

## Large files are jobs that send a link

Do not stream a million rows through PHP while a staff member watches a spinner. Queue the file. Email a temporary signed URL. Put the column list in a short doc beside the route so a rename is an obvious break, not a surprise in someone else's spreadsheet.

Sanctum belongs here only if a real client must call you as a user or a token. Do not add it because an API article said every Laravel app needs it. A scheduled file may not need an API at all.

## The Friday rename

The payload had `order_id` for eight months. A pull request renamed it to `id` because the JSON "looked inconsistent," and it deployed before lunch. The partner's importer stored null order ids all weekend. Your panel still worked. Their warehouse did not. That is the whole lesson of this year: if someone else parses the body, your tidy rename is their outage.

Freeze the keys. Add fields if you must. Break them only under `/v2`, with a date in the same doc as the column list. The staff button does not change. Their URL does not have to know you refactored a PHP variable.

## How you know year four is done

A consumer you can name is live on v1. A breaking change has a version, not a silent deploy. Cross-tenant reads fail a test. Webhook deliveries are visible on the record staff already open.

The pull request to refuse edits the payload keys "while we're here" and deletes the delivery log as cleanup. That cleanup is the outage. Keep v1 stable. Keep the log. Keep a single admin UI.

Name the consumer before you name the endpoint. Then open the Laravel docs for queues and policies on the major you have installed, and leave the panel package alone except where the new action needs a button.
