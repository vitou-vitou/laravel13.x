# Your Admin Works. Now Make Sure a Curious Employee Cannot Export Everyone.

Last year the panel became the place staff work. This year the danger changes. The danger is not a missing feature. It is a working feature in the wrong hands: a full customer export, a public upload URL, a "login as" button with no record, an email sent inside the click so the page dies when Gmail is slow.

Year two is trust. The same Filament or Backpack or Nova screen. Fewer columns on it. A policy on the server. Mail that can fail without taking the request down with it.

## Hiding a column is not security

Make a list before you touch the UI. Email, phone, national id, card tokens, anything you would not want in a screenshot. The default table shows a mask. A reveal button checks a policy and writes the same kind of audit row you added in year one.

If the only protection is "we didn't add the column," someone will add it back in a resource class on a Friday. The policy is the gate. The mask is courtesy.

Laravel's authorization docs are the mechanism: policies, `authorize()`, the admin package's permission hooks. Which columns count as sensitive is your decision. Write it down next to the model so the next resource doesn't dump `$user` onto the page.

## Files do not go on the public disk

Logos you meant to publish can live in public storage. Identity documents cannot. Use the file field your admin package already documents, store on a private disk, and hand out a temporary link after the policy says yes.

A world-readable `/storage` URL is a failure even when the button looks polished. I have seen teams spend a sprint on table design and then store passports where anyone with the path could download them. The filesystem docs for your Laravel version are short. Read the private-disk part, not a random upload tutorial from another major.

## The button should not wait on SMTP

A "email the customer" action that calls `Mail::send()` inside the request will get blamed on the UI the first time the mail host stalls. Queue it. One mailable, `ShouldQueue`, a worker that is actually running before you announce the feature.

Locally, the `log` or `array` mailer is fine. Production does not get to stay on the `sync` queue once real volume shows up. You do not have to queue the password reset on day one if nobody is watching workers yet. You do have to turn a worker on before a campaign, not during it.

Once a week, look at failed jobs. A queue nobody looks at is a silent outage with a nice progress spinner.

## The tests that matter are dull

Guest is rejected. The wrong role is rejected. The right role succeeds. A private file returns 403 for everyone else. That is the suite. You do not need screenshot tests for a mask.

Here is the shape I want for a refund, whether the screen is Filament or Backpack:

```php
public function refund(Order $order): void
{
    $this->authorize('refund', $order);

    $order->update([
        'status' => 'refunded',
        'refunded_at' => now(),
    ]);
}
```

The policy is the product. The button is decoration. If a package plugin offers "impersonate" as one line, do not ship it unless the session expires and the audit log says who became whom. Support superpowers without a trace are how you lose a year of trust in an afternoon.

## Do not stack a second permission system

Your admin package already has roles or gates. Adding Spatie "because every tutorial uses it" on top, without deciding which one wins, means a person is allowed in one place and denied in the other. Pick one. Document it in the README in a sentence.

Client-side validation is fine as a hint. It is not the rule. The Form Request or the package's server rules are the rule. An API sitting next to the panel must hide `password` and `remember_token`. Returning the raw user model is how a JSON tab undoes the mask you just built.

## How you know year two is done

A person with the wrong role cannot refund, export, or download the private file, and you have a test that says so. Mail from the panel goes through a queue. The customer table does not show the full sensitive column by default.

The pull request to refuse is the one that adds "export all customers" as a table action with no policy. It will look like a one-hour win. It is the incident. Close it or make it authorized, minimal, queued, and audited.

Stay on the docs for the major you have installed when you touch auth, mail, or disks. This year is not "read all of Eloquent." It is "the writes you already have cannot be abused casually."
