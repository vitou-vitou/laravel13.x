# The Admin Does Not Need a Rewrite. It Needs a Calendar.

Five years in, the panel is boring. That is the point. Staff still ship orders in the same Filament or Nova or Backpack you picked in year one. The work now is staying alive: Laravel majors, package majors, people leaving, backups you hope you can restore. A conference talk is not a migration plan.

I have seen a year-five rewrite that replaced a working admin with a new one and lost the audit log, the policies, and the webhook versions on the way. The new UI shipped. The business went back to spreadsheets for a month. Endurance is the opposite of that story.

## Three dates, with names on them

Put them where the team already looks.

Next framework minor. Next admin-package minor. Next restore drill. A person's name sits on each date. If they leave, the date does not. That is the program. A new frontend is not on the list.

Between those dates, leave the screens alone. The daily change is still a resource, a policy, a job. You do not "prepare the rewrite" in small pull requests that leave two panels half alive. Two panels is a defect, not a heritage feature.

## Upgrade like a mechanic, not like a founder

On the branch: read the official from-to guide for the framework and for the admin package. Bump the lockfile. Boot the panel. Fix what the guide says broke. Run the tests you earned earlier: wrong role cannot refund, webhook payload keys stay put, a job retry does not double-send.

Do not restyle in the same branch. Do not swap Filament for a custom Vue admin because the upgrade looks tedious. Tedium is cheaper than a second product. Blog posts from two majors ago will lie to you about form APIs. The upgrade guide that matches your versions will not.

When it boots and the tests pass, deploy. Not when the homepage merely looks fine.

## Delete the door you do not use

Search routes and `composer.json` for the Blade CRUD someone added beside Filament, the unused export, the inline mail path the queue replaced. Removing them is the feature. Leave one line in the runbook so a future branch does not resurrect them "because it was there in git."

I treat a dead admin route like a spare key under the mat. It still opens the house.

## Restore it once a year, as a staff user

Time the drill. If it takes longer than you tell customers you can recover, fix the backup, not the slide. Log in as support, not only as root, so policies still work on the restored data. Write the duration next to the date. A backup you have never restored is the same rumor it was in year one. Year five is when that rumor gets expensive.

Also offboard people the day they leave. A stale admin account is not an upgrade problem. It is a key you forgot to take back.

## Memory does not live in chat

If the team rebuilt a second admin twice, write one line in the README: we do not add another panel. If payload keys changed without a version twice, write that v1 keys are frozen. The lesson has to sit in the repo. A chat transcript will not be there next September. I have lost the thread before. The file was the only thing that survived.

You still do not document all of Eloquent in that note. You document the mistake you actually repeated. Laravel's upgrade guide stays the source for framework behavior. Your note stays the source for your rules.

## What the rewrite actually cost

The new admin was faster in the demo. It did not have the ship permission, so a junior could refund. It did not write audit rows, so nobody could answer "who shipped this?" It posted webhooks with new key names. Partners broke. The team spent the next month porting year-two and year-four behavior into the new UI, which is a slow way to arrive back at the old panel.

I would spend that month on the upgrade guide instead. Boot the current package on the next major. Delete the extra door. The staff should not be able to tell you upgraded, except that login still works and the restore drill has a fresh date.

## How you know year five is done

The panel you chose in year one still boots on a supported major. The other UI is gone. Someone restored a backup this year and wrote down how long it took. The next upgrade has a date and a name.

The pull request to refuse is the one that introduces a new admin during the upgrade "so we don't have to deal with the old API." Follow the guide. Delete the extra door. Run the drill. Then go back to ordinary Tuesdays, which is what a five-year admin is for.
