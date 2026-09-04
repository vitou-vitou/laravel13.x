---
name: ide-browser-remote
description: Drive PGI UI in Cursor IDE browser (cursor-ide-browser MCP). Lock, fill, save, reload, fetch edit JSON, print PDF via pdftotext. Use when user says remote, drive the journey, do it in the browser, or IDE browser smoke. Not Playwright.
---

# IDE browser remote

User says **remote** / **drive it** / **do it in the browser** → this skill. Not Playwright (`enable playwright` only).

Herd `APP_URL` (e.g. `http://pgi-core-frontend.test`). Never `:5173`.

Discover tools first: `GetDynamicTools` namespace `cursor-ide-browser`, then `CallDynamicTool`.

## Loop

1. `browser_tabs` list. Reuse tab or `browser_navigate`.
2. `browser_lock` **lock** before clicks. Unlock when done or when handing to user.
3. Login / captcha / 2FA → **stop**. Ask user.
4. Reuse one existing row (`13-smoke-few-records`). New quote only if none fits.
5. `browser_snapshot` `interactive: true`, `compact: true`. Refs go stale after Vue patch / tab change → snapshot again.
6. Native input → `browser_fill` / `browser_click`. CKEditor / PrimeVue dates → Vue form patch (below).
7. Save → wait toast (`Update success` / fail). Button often `disabled` while POST.
8. Prove persist: **reload edit URL** (not only same-session form).
9. Compare **API JSON** vs UI. Blank UI + empty nest = PAI did not store. Blank UI + filled nest = FE hydrate bug.
10. Print: fetch PDF same-origin. IDE PDF pane often **dark/blank** — not a fail. Check `%PDF` + `pdftotext`.
11. Report a pass/fail table. Unlock.

Human EN+KH payloads (`11-smoke-data-humanizer`). No `smoke` / `test` junk.

## Vue form patch (CK / nest)

Walk `#app.__vue_app__._instance`. Match `form.id` + `product_code`. `Object.assign` onto the reactive form (and nested block if present).

```js
const ctx = vnode.ctx || vnode.proxy || vnode.setupState
const form = ctx && (ctx.form || (ctx.setupState && ctx.setupState.form))
const f = form && (form.value || form)
```

Then `browser_snapshot` again. Click **Save** on Premium (quotation update lives there).

## API vs UI

From the page (cookies):

```js
fetch('/pl/quotations/{id}/edit', { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
```

Policy: `/pl/policies/{id}/edit`. Endt: `/pl/endorsements/{id}/edit`.

Clip HTML. Log nest key + top-level `[]` vs nested rows.

## Print

Quote: `/pl/quotations/{id}/pdf?letterHead=0&lang=EN&signature=0&noStamp=1`

```js
fetch(url, { credentials: 'same-origin' }).then(async (r) => {
  const buf = await r.arrayBuffer()
  const magic = String.fromCharCode(...new Uint8Array(buf.slice(0, 8)))
  return { status: r.status, type: r.headers.get('content-type'), size: buf.byteLength, magic }
})
```

Pass: `status 200`, `magic` starts `%PDF`, size not tiny JSON. If CDP base64 dumps to a log file: `node` write Buffer → `pdftotext -layout file.pdf -`.

Delete temp PDF after extract unless user asked to keep it.

## Do not

- Playwright / `agent-browser` / `npx playwright`.
- Spawn extra quotes/policies “for evidence”.
- Treat IDE PDF viewer black as print-broken.
- Leave browser locked after the turn.
- Guess login passwords.

## Product overlay

D&O 0198 → also read `dno-quotation-new` (nest `directors_and_officers`, print title `DIRECTORS AND OFFICERS`).
