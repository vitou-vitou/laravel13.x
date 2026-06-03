# Laravel example scaffold (templates)

**Not a runnable app.** Templates + `bin/new-example` create `examples/<your-slug>/`.

## Usage

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd /d/laravel13.x
./bin/new-example my-new-app "My New App"
```

## What `new-example` creates

1. `composer create-project laravel/laravel examples/<slug>`
2. `specify init . --here --integration cursor-agent --force`
3. **`herd link <slug> --update-env`** → `http://<slug>.test` in `.env`
4. `dev.sh`: `npm run dev` = Vite only when Herd linked
5. Committed dev `.env` + `phpunit.xml` `APP_KEY`
5. Stub `docs/NEXT_SESSION.md`, `.specify/memory/constitution.md`
6. Root `.gitignore` exception for `examples/<slug>/.env`

## After scaffold

1. `/speckit.specify` or edit `.specify/specs/001-<slug>/spec.md`
2. Superpowers TDD → implement
3. Add row to `docs/SESSION_STATE.md` when MVP passes tests

See `docs/NEW_EXAMPLE_SCAFFOLD.md`.
