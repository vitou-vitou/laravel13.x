# 02: Company General Tasks Domain and UI

**What to build:** Implement the internal operations task management pillar, including the `CompanyTask` Eloquent model, factory, seeder, Filament resource with department filtering, priority badges, and CRUD interface, tested with Pest.

**Blocked by:** 01: Application Foundation and Database Schema

**Status:** ready-for-agent

- [ ] `CompanyTask` model with relations and casted enums
- [ ] `CompanyTaskFactory` with realistic department and priority states
- [ ] `CompanyTaskResource` Filament page with search, status filters, and priority styling
- [ ] Pest feature test verifies creating, editing, and filtering company tasks
