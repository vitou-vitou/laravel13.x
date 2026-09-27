# The Panel Is Trusted. The Next Outage Will Be a Click That Tries to Do Too Much.

Nobody complains that the admin is insecure anymore. They complain that "email everyone on this filter" spins until the browser gives up. Someone will suggest a new frontend. Don't. The panel is fine. The click is doing the work of a queue inside a single HTTP request.

Year three is the year heavy work moves off the request. Same Filament, Nova, or Backpack screens. Jobs, a scheduler, and filters that hit SQL.

## Start from the slow click

Do not start from an architecture slide. Find the timeout.

If export-all dies, that action becomes a job. If the night report is someone clicking a button before they go home, it becomes a scheduled command. If a table filter loads every row into PHP and then filters, it becomes a query and an index. Three fixes. Then stop buying infrastructure.

Laravel's queue and task scheduling docs for your version are the whole design. Horizon is a nice window onto Redis queues. It is not the prize. A `failed_jobs` row that a human notices is the prize. A worker that restarts on deploy, with one command written in the runbook, beats a dashboard nobody is on call for.

## The job should be safe to run twice

Retries are a feature only when the second run does not double-send or double-refund. Give imports a unique key. Give outbound mail a row that says this order already got this message. A job that throws halfway, retries, and creates a second customer is how scale makes bad data faster.

This is the bug I expect if you skip it:

```php
// runs again after a timeout and inserts another row
public function handle(): void
{
    Customer::create($this->payload);
}
```

Prefer a unique email, or `firstOrCreate`, or a send log the job checks first. Then turn retries on. Not before.

Pass filter arguments into the job, not a giant collection of models you already loaded in the controller. The worker should query again. The request should return while the worker works.

## Filters belong in the database

`->get()->filter()` on ten thousand orders will melt a table that looked fine in staging. Package tables already paginate. Do not bypass them. Add the index for the column staff actually filter on, after you have seen the slow page, not because a checklist said "indexes."

Pagination is not optional on staff lists. "Just this once, load all" becomes the month-end outage. I would rather show a pager staff grumble about than a spinner that never ends.

## Cache is the last tool, not the first

Measure the page. If one count dominates, cache that count with an expiry or clear it when the underlying rows change. Do not cache "this user is an admin" unless you know exactly when demotion takes effect. Do not cache the whole admin HTML. You will serve the wrong tenant's numbers and spend a day blaming the UI.

A cache without a way to bust it is a second source of truth. You already have one. It is the database.

## A Tuesday I keep seeing

Support selects "paid, this month" and hits "email a receipt." The browser waits. PHP is looping customers and talking to SMTP one by one. At customer 40 the gateway times out. The page shows an error. Forty people got mail. Sixty did not. The staff member clicks again. Now some people have two receipts.

The fix is not a new JavaScript table. The click stores the filter, dispatches one job, and the screen says the receipts are going out. The worker queries the same filter, skips anyone already in `receipt_sends`, and writes a row after each successful send. A retry continues the rest. Failed jobs show the exception. Nobody ships a React admin to get there.

## What you still do not build

A new React admin "so it will scale." An Inertia rewrite of the list page. A second cache layer in Redis and a static property. The `sync` queue left on in production because the demo used it.

The daily habit stays small: trust the lockfile, keep the package's screens, add a test when a job's behavior changes. `Queue::fake()` and an assertion that the right job was pushed is enough for the click. A sync driver in the test is enough to run the job's body when you need to see the rows.

## How you know year three is done

The timeout click returns immediately and a worker finishes the work. Failed jobs are visible. The scheduled report runs from cron, not from someone's laptop. The heaviest table filters in SQL and pages the result.

If a pull request loops `Mail::send()` inside a Filament action, send it back. That loop is a job. It needs a record so a retry does not mail the customer twice.

Stay on the queue, schedule, and pagination docs for the major you run. The admin package does not need to change this year. You are only moving work that never belonged in the click.
