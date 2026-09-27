# Stop Building a Platform. Give Staff One Admin They Can Use on Monday.

Your support lead still updates orders in a spreadsheet. Someone on the team wants Filament, someone else wants a custom Vue dashboard, and a third person already started a Blade CRUD "just for products." Year one is the year you pick one door and make it the place work actually happens.

I have watched this go wrong the same way three times. The panel looks impressive in a demo. Staff still ask for the spreadsheet because the demo never included their real task: change a status, see who did it, and know yesterday's database can be restored. That is the whole job for the first year. Not tenancy. Not webhooks. Not a second admin "for later."

## Pick one package and write its name down

Filament, Nova, Backpack, Orchid, or a small Blade panel. One of them. If `composer.json` already has `filament/filament` or `backpack/crud`, that decision is made. Do not install a second panel because a blog called the first one old.

Put the name in the README. The next person who joins the repo should not have to guess. Laravel's own docs are for auth, Eloquent, and migrations. The panel package's docs are for screens. Your department names and seed passwords are neither. They are just your app, and the seed password does not belong in production.

Generate three resources staff already have words for. Customers, orders, products. Whatever they said in the first meeting. A fourth entity can wait until those three survive a real Monday.

## The only path a status change should take

Say the task is "mark this order shipped."

Staff log in. They open the order on the panel you chose. The package's own route handles the click, not a controller you invented beside it. Their role is allowed to ship. One update sets the status and the timestamp. In the same database transaction you insert a small audit row: who, what action, which record, when.

That is it. You do not need a repository, an action class, and a Filament page that all write the same column. You do not need event sourcing. The audit table can be boring:

```php
audit_logs: user_id, action, subject_type, subject_id, created_at
```

If the server dies mid-request, you do not want a shipped order with no trace, and you do not want a trace for an order that never shipped. Same transaction. You can add fancy diffs in year two if you still care.

## Roles are job titles, not a permission museum

Operator and support are enough if that is how the company talks. A grid of forty checkboxes feels serious and then nobody knows which box lets someone refund. Start with the jobs people already have. A person who should not ship orders should not see a working ship button, and the server should reject the action even if they forge the request.

Shared logins make the audit row a lie. Create a user, assign the role, watch them finish one real task, then throw away the shared password. If the whole team uses `admin@company.com`, you do not have an audit log. You have a diary with one name in it.

## A backup you have never restored is a rumor

Dump the database. Restore it into an empty database. Log in. Open one order. Write the commands in a runbook the same day. If the only person who knows how to restore it is on leave, you do not have a backup.

This is unglamorous and it is the difference between year one and a demo. I would rather ship a plain Filament resource list and a tested restore than a custom design system and a `mysqldump` cron nobody has run backward.

## What you refuse this year

A second admin UI. A repository for a single Eloquent model. Microservices. A rewrite in Inertia because Blade "doesn't scale." You do not have a scale problem. You have a "staff won't use it" problem. Scale is a later year, and only if this panel is actually how work gets done.

Match the screens that already exist. If the repo is Filament, the new model is a Filament resource. A Blade CRUD next to it feels fast for an afternoon and then you maintain two ways to edit a product for five years.

## How you know year one is done

Three things are true.

Staff do the daily task in the panel, not in the sheet. A status change leaves an audit row. Someone other than you restored a backup and logged in.

When the riskiest pull request of the quarter shows up, ask a plain question: does this add a second way to edit the same record? If yes, close it. Do not start queues, partner APIs, or a new frontend until that answer stays no.

Laravel's authentication and Eloquent docs for your installed major are enough engine for this year. The panel package's "create a resource" chapter is enough UI. Everything else can wait until the panel has survived a real week.
