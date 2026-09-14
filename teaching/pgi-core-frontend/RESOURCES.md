# Learning Resources

Resources for understanding pgi-core-frontend, organized by trust level and topic.

## Primary Sources (Highest Trust)

These are the canonical references — prefer these over external docs or LLM recall.

### Project Documentation
- **`pgi-core` SKILL.md** — `.cursor/skills/pgi-core/SKILL.md`
  - Overview, stack, architecture, conventions
  - Read first before exploring codebase
- **`direct-book-lines.json`** — `resources/data/direct-book-lines.json`
  - Single source of truth for product catalog
  - Lists all codes, sale status (live/wip), families
- **`AGENTS.md`** — Root of repo
  - Coding standards, commit message rules
  - Enforced by team, not optional

### Internal References
- **`references/overview.md`** — High-level project summary
- **`references/journey-7pj.md`** — Staff journey: quote → policy → endorsement
- **`docs/PL-Direct-Book-Frontend-Structure.md`** — Vue component map
- **`docs/SESSION_STATE.md`** — Current work state (if exists)

## Secondary Sources (Curated)

### Laravel & PHP
- **Laravel 12 docs** — https://laravel.com/docs/12.x
  - Routing, middleware, blade, HTTP client
- **Pest PHP docs** — https://pestphp.com/docs
  - Testing framework (used in this project)

### Vue & Frontend
- **Vue 3 docs** — https://vuejs.org/guide/
  - Composition API, `<script setup>`
- **PrimeVue 3 docs** — https://primevue.org/
  - UI components (Button, Dropdown, TabView, etc.)
- **Vue Router 4 docs** — https://router.vuejs.org/
  - Route guards, meta fields

### Tools & Libraries
- **Tabulator Tables** — http://tabulator.info/
  - Table component (not PrimeVue DataTable)
- **CKEditor 5** — https://ckeditor.com/docs/ckeditor5/
  - Rich text editor
- **Snappy PDF** — https://github.com/barryvdh/laravel-snappy
  - PDF generation (wraps wkhtmltopdf)

## Tertiary Sources (Context Only)

Use for background understanding, not implementation details.

### Insurance Domain
- No specific resources yet. Insurance knowledge not required for code quality assessment.

### Architecture Patterns
- **BFF Pattern** — Backend-for-Frontend concept
  - This project is a BFF (thin layer over PAI API)

## Community (Wisdom)

### Internal
- Team Slack/Discord (if exists)
- Code review comments in PRs
- UAT testing team feedback

### External
- Laravel community: https://laracasts.com/discuss
- Vue community: https://discord.com/invite/vue

## To Investigate

Resources to explore when time permits:

- [ ] PAI API documentation (insurance backend)
- [ ] SM (Security Management) vendor docs
- [ ] Keycloak SSO setup guide
- [ ] Herd for Windows docs

## Notes

- Avoid LLM-generated insurance domain "knowledge" — verify against PAI docs or team
- When in doubt about code conventions, check `AGENTS.md` first
