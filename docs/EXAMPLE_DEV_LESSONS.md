# Example dev — lessons learned (self-study handoff)

**Purpose:** One page so agents and humans stop re-debugging the same Windows + Herd + Laravel example issues.

**Read when:** `bin/new-example`, 500 on `.test`, `php: command not found`, empty Vite page, encryption errors.

---

## Pitfall matrix

| Symptom | Root cause | Fix once |
|---------|------------|--------|
| `bash: php: command not found` | Herd PHP not on Git Bash PATH | `export PATH="/d/laravel13.x/bin:$PATH"` or `~/.bashrc` |
| Blank / wrong page at `:5173` | Only Vite running; Laravel is elsewhere | Open `APP_URL` from `.env` (e.g. `http://my-app-55.test`) |
| Vite banner `APP_URL: http://localhost` | Stale config | Match `.env`; `php artisan config:clear` |
| 500 **Unsupported cipher / incorrect key length** | `APP_KEY` contains ANSI `[33m` from `key:generate --show` | `./bin/fix-example-app-key <slug>` |
| `No application encryption key` | Missing `.env` on fresh clone | `new-example` commits `.env`; else `cp .env.example .env` + fix script |
| `artisan test` OK but browser 500 | Web uses bad `APP_KEY`; tests use `phpunit.xml` | Align both via `fix-example-app-key` |
| Site not found / connection refused | Herd not linked | `cd examples/<slug> && herd link <slug> --update-env` |

---

## Golden paths

### New example (automated)

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd /d/laravel13.x
./bin/new-example <slug> "Display Name"
./bin/verify-example <slug>
```

**Result:** `http://<slug>.test`, clean `APP_KEY`, `npm run dev` = Vite only.

### Daily dev

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd examples/<slug>
php artisan test
npm run dev
# browser → grep APP_URL .env
```

### Agent must run before saying “done”

1. `php artisan test` — show pass count  
2. `./bin/verify-example <slug>` — APP_KEY + HTTP (if Herd up)  
3. Tell user **exact** `APP_URL`, not `:5173`

---

## Script map

| Script | When |
|--------|------|
| `bin/new-example` | Greenfield `examples/<slug>` |
| `bin/verify-example` | After scaffold or user reports 500 |
| `bin/fix-example-app-key` | Encryption / cipher errors |
| `bin/php`, `bin/composer` | Herd PHP on PATH |

---

## Scaffold invariants (do not regress)

`bin/new-example` must:

1. `key:generate --force --no-ansi` — **never** `--show` into `.env`  
2. `herd link <slug> --update-env`  
3. `cp .env` → `.env.example` **after** Herd updates `APP_URL`  
4. `dev.sh`: if `APP_URL` contains `.test` → Vite only  
5. End with `verify-example` (fail loud)

---

## SDD workflow (unchanged)

- Greenfield: **Spec-Kit + Superpowers** — not OpenSpec at init  
- Post-MVP: OpenSpec change orders  
- Do not re-scaffold MVPs in `docs/SESSION_STATE.md`  

---

## Related docs

- `docs/WINDOWS_HERD_GITBASH.md` — PATH + Herd  
- `docs/NEW_EXAMPLE_SCAFFOLD.md` — scaffold command  
- `.cursor/rules/windows-herd-gitbash.mdc` — agent always-on rule  

**Last consolidated:** 2026-06-03 (my-app-33/55, APP_KEY ANSI, Herd link)
