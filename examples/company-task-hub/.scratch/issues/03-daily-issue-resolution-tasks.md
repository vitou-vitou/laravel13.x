# 03: Daily Issue Resolution Tasks Domain and UI

**What to build:** Implement the daily incident management pillar, including the `DailyIssueTask` model, factory, seeder, resolution workflow (automatic `resolved_at` timestamping upon resolution), Filament resource with severity indicators, and Pest tests.

**Blocked by:** 01: Application Foundation and Database Schema

**Status:** ready-for-agent

- [ ] `DailyIssueTask` model with root cause, resolution notes, and auto-timestamping logic
- [ ] `DailyIssueTaskFactory` covering incident categories and severity levels
- [ ] `DailyIssueTaskResource` Filament page with quick-resolve action and severity tags
- [ ] Pest feature test verifies incident logging, resolution flow, and timestamping
