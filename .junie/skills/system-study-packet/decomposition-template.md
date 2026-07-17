# Decomposition document template

Filename: `{slug}-decomposition.md`  
Companion: `{slug}-8-principle-study.md`

## Required sections

### 1. Context and boundaries

- In scope (bullets)
- Out of scope (bullets)
- External systems table (Stripe, DB, queue, etc.)

### 2. Subsystem decomposition

ASCII tree or bullet hierarchy, then per subsystem:

| Unit | Files | Responsibility |

### 3. Layer decomposition

Stack diagram (presentation → infrastructure) + table mapping layer → examples → rules in this app.

### 4. Data model decomposition

- ER-style text diagram
- Key table/field groups
- State machine (valid transitions + invalid + where guarded)

### 5. Request-flow decomposition

Numbered flows for critical paths (3–5), format:

```
Browser/API → Controller → Service → Model/DB → side effects
```

### 6. Route map

| Method | Path | Name | Middleware |

### 7. Service and dependency decomposition

Bindings (e.g. service provider), service responsibility table, contracts/interfaces.

### 8. Test decomposition

| Test class | Subsystem |  
Command to run tests.

### 9. Status matrix

**Subsystem table:** Done | Doing | Not started + notes  

**Cross-cutting table:** locks, audit, API, OpenSpec, docs drift, blockers  

**Phase/roadmap table** if project uses phases.

### 10. File index

| Concern | Path |

### Reading order

Numbered list for new developers.

### Footer

`Last aligned to codebase: {date or phase}. Update when features land.`

## Writing rules

- Facts from code/tests, not wishlist.
- Use same subsystem names as the 8-principle packet.
- Call out doc drift (e.g. handoff says 49 tests, suite has 53).
