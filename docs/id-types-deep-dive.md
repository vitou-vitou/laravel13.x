# ID Types Deep Dive

## Full Comparison Table

| Type | Example | Length | Sortable | Collision-safe | Human-readable | DB-friendly |
|------|---------|--------|----------|----------------|----------------|-------------|
| Auto Increment | `42` | 4–8B int | ✅ | ❌ enumerable | ✅ | ✅✅ best |
| UUID v1 | `...` (MAC+time) | 36 chars | ✅ partial | ✅ | ❌ | ⚠️ MAC leak |
| UUID v4 | `550e8400-e29b-...` | 36 chars | ❌ | ✅ 122-bit | ❌ | ⚠️ fragmented |
| UUID v7 | `0190d753-...` | 36 chars | ✅ ms-ordered | ✅ | ❌ | ✅ |
| GUID | same as UUID | 36 chars | ❌ | ✅ | ❌ | ⚠️ |
| ULID | `01ARZ3NDEKTSV4RRFFQ69G5FAV` | 26 chars | ✅ ms-ordered | ✅ 80-bit rand | ❌ | ✅ |
| KSUID | `0ujtsYcgvSTl8PAuAdqWYSMnLOv` | 27 chars | ✅ s-ordered | ✅ 128-bit rand | ❌ | ✅ |
| NanoID | `V1StGXR8_Z5jdHi6B-myT` | 21 chars (default) | ❌ | ✅ URL-safe | ❌ | ✅ |
| CUID | `cjld2cjxh0000qzrmn831i7rn` | 25 chars | ✅ partial | ✅ | ❌ | ✅ |
| CUID2 | `tz4a98xxat96iws9zmbrgj3a` | 24 chars | ✅ | ✅ stronger | ❌ | ✅ |
| Snowflake | `1541815603606036480` | 64-bit int | ✅ ms-ordered | ✅ (worker ID) | ❌ | ✅✅ |
| Hashids | `XRKUdse` | configurable | ❌ | ❌ reversible | ✅ short | ✅ |
| ShortUUID | `keVKQWvPiQWhE78CP4en9d` | 22 chars | ❌ | ✅ | partial | ✅ |
| ObjectID (Mongo) | `507f1f77bcf86cd799439011` | 24 hex chars | ✅ s-ordered | ✅ | ❌ | Mongo only |
| NanoID custom | `brave-7xK2` | configurable | ❌ | configurable | ✅ | ✅ |
| Adjective-Noun-Rand | `brave-lion-x7k2` | ~16–24 chars | ❌ | low | ✅✅ | ✅ |
| Timestamp-based | `20240526-x7k2` | varies | ✅ | low | ✅ | ✅ |

---

## Deep Dive Per Type

### Auto Increment ID
- Sequential integer, DB-generated
- **Problem**: exposes business volume (`/users/10042` reveals ~10K users)
- **Problem**: breaks in distributed DBs — need centralized sequence or coordination
- **Best for**: internal PKs never exposed to API consumers

### UUID v1
- Encodes MAC address + timestamp
- **Security risk**: MAC address leaks machine identity
- Mostly deprecated — use v4 or v7 instead

### UUID v4
- 122 bits pure random
- No ordering — random inserts cause B-tree page splits → index fragmentation
- PostgreSQL handles better than MySQL (FILLFACTOR tuning helps)
- **Standard**: RFC 4122
- **Best for**: legacy systems, cross-system IDs where order irrelevant

### UUID v7
- 48-bit Unix ms timestamp prefix + 74-bit random suffix
- Monotonically increasing within same millisecond (per spec)
- Solves v4's index fragmentation problem
- **Best for**: new distributed systems needing sortable unique IDs
- Laravel: `Str::orderedUuid()` is time-ordered but not strict UUIDv7 — use `ramsey/uuid` v4.7+

### GUID
- Microsoft terminology for UUID
- Identical format and collision properties
- SQL Server stores as `uniqueidentifier` (16B binary internally)

### ULID (Universally Unique Lexicographically Sortable Identifier)
- `[48-bit timestamp][80-bit random]` = 128 bits total
- Encoded in Crockford Base32 (26 chars, case-insensitive, no ambiguous chars)
- Monotonic within same millisecond (optional spec extension)
- Lexicographic sort = chronological sort
- **Best for**: event logs, audit trails, anything needing time-sort + uniqueness
- Laravel: `Str::ulid()` built-in since Laravel 10

```
01ARZ3NDEKTSV4RRFFQ69G5FAV
└──────┘└────────────────┘
timestamp      random
```

### KSUID (K-Sortable Unique Identifier)
- `[32-bit Unix timestamp][128-bit random]` = 160 bits
- Base62 encoded → 27 chars
- Timestamp resolution: **seconds** (not ms like ULID)
- Larger random component than ULID → stronger uniqueness
- Used by Segment, Stripe (internally)

```
0ujtsYcgvSTl8PAuAdqWYSMnLOv
└─────┘└──────────────────┘
 4B ts      16B random
```

### NanoID
- Configurable alphabet + length
- Default: 21 chars, URL-safe alphabet (`A-Za-z0-9_-`)
- 21 chars → ~126 bits of entropy ≈ UUID v4
- Can customize: `NanoID(alphabet='0123456789', size=10)` for numeric-only
- No timestamp component — purely random
- **Best for**: short tokens, API keys, URL slugs

### CUID / CUID2
- **CUID**: `c` + timestamp + fingerprint + random (25 chars)
- **CUID2**: redesigned — SHA3 hash-based, more secure, 24 chars default
- CUID2 fixes CUID's fingerprint leakage (hostname info)
- Both: collision-resistant, sortable roughly by time
- Popular in JavaScript/Node world (Prisma default)

### Snowflake ID (Twitter/X origin)
- 64-bit integer: `[41-bit ms timestamp][10-bit worker ID][12-bit sequence]`
- Worker ID = datacenter ID + machine ID
- 4096 IDs per millisecond per worker
- Fits in `BIGINT` — tiny storage, fast index
- Requires coordination layer (assign worker IDs)
- Used by: Twitter, Discord, Instagram, Mastodon
- **Best for**: high-throughput distributed systems with controlled infrastructure

```
[sign][41-bit timestamp][10-bit worker][12-bit sequence]
  0    1683000000000       0001          0000000000001
```

### Hashids
- **NOT unique ID generator** — encodes existing integers
- Reversible: `encode(42) → "XRKUdse"`, `decode("XRKUdse") → 42`
- Purpose: hide sequential IDs from API consumers
- Configurable salt, min length, alphabet
- **Security**: obfuscation only — not cryptographic, determined attacker can reverse
- **Best for**: exposing auto-increment PKs without revealing sequence

### ShortUUID
- UUID v4 encoded in Base57 or Base58 → 22 chars (vs 36)
- Same collision safety as UUID v4
- No ambiguous chars (0/O, I/l excluded in Base58)
- Reversible back to UUID

### MongoDB ObjectID
- 12-byte: `[4B timestamp][5B random][3B incrementing counter]`
- Hex-encoded → 24 chars
- Second-level timestamp resolution
- Encodes creation time extractable via API
- Specific to MongoDB — don't use in relational DBs without reason

### Adjective-Noun-Random
- Human-memorable compound name
- Libraries: `coolname` (Python), `docker-names` (JS), `petname` (Go)
- PHP: build manually or use `jawira/unicode-generator`
- Collision space: depends on word list size × random suffix
- **Best for**: ephemeral named resources (deployments, containers, sessions)

### Timestamp-based Custom ID
- e.g., `20260526-143022-x7K2`
- Human-readable date embedded
- Good for filenames, report IDs, batch job IDs
- Not globally unique without random suffix
- **Best for**: file naming, human-managed records

---

## Collision Budget (birthday paradox threshold)

50% collision probability reached at √(2 × space) IDs:

```
Auto-increment    → no collision (sequential), but predictable
UUID v4           → 2^61 IDs for 50% collision ≈ 2.3 quintillion
UUID v7           → same randomness as v4 in suffix portion
ULID              → 2^40 IDs per ms timestamp bucket
KSUID             → 2^64 IDs for 50% collision
NanoID 21 chars   → 2^63 IDs ≈ UUID-level
Snowflake         → 4096 per ms per worker, no collision by design
Hashids           → same as underlying integer space (no new uniqueness)
adj-noun (100×100×62^4) → 50% at ~40K IDs — SMALL, use random suffix
6-char alphanum   → 50% at ~105K IDs
```

---

## DB Storage Comparison

| Type | MySQL storage | PostgreSQL storage | Index perf |
|------|--------------|-------------------|------------|
| BIGINT (auto) | 8B | 8B | ✅✅ best |
| UUID (string) | 36B | 16B (`uuid` type) | ⚠️ v4=bad, v7=ok |
| UUID (binary) | 16B | — | ✅ |
| ULID (string) | 26B | 26B | ✅ (sortable) |
| Snowflake | 8B | 8B | ✅✅ |
| NanoID | 21B | 21B | ⚠️ random |

MySQL tip: store UUID as `BINARY(16)` using `UUID_TO_BIN(uuid, 1)` (swap flag=1 reorders for index friendliness).

---

## Laravel Usage

```php
// Auto ID
$table->id(); // BIGINT UNSIGNED AUTO_INCREMENT

// UUID v4
$table->uuid('id')->primary();
Str::uuid();

// UUID v7 / time-ordered
Str::orderedUuid(); // Laravel's time-ordered (close to v7)
// Strict v7: composer require ramsey/uuid ^4.7
use Ramsey\Uuid\Uuid;
Uuid::uuid7()->toString();

// ULID (Laravel 10+)
$table->ulid('id')->primary();
Str::ulid(); // returns Illuminate\Support\Str\Ulid

// NanoID — no official Laravel package, use JS side or:
// composer require hidehalo/nanoid-php (community)

// Snowflake — needs worker coordination:
// composer require godruoyi/php-snowflake
use Godruoyi\Snowflake\Snowflake;
(new Snowflake)->id();

// Hashids
// composer require hashids/hashids
use Hashids\Hashids;
$hashids = new Hashids('your-salt', 8);
$hashids->encode(42);      // → "XRKUdse"
$hashids->decode("XRKUdse"); // → [42]

// Random string
Str::random(12); // alphanumeric, not URL-safe guaranteed
```

---

## Decision Tree

```
Need human-readable?
├── yes → adj-noun-rand (ephemeral) or Hashids (wrap auto ID)
└── no
    ├── Single DB, no distribution?
    │   └── Auto increment BIGINT
    ├── Distributed, high throughput?
    │   └── Snowflake (coordinate workers) or KSUID
    ├── Need time-sortable?
    │   ├── Laravel simple → Str::orderedUuid() or Str::ulid()
    │   └── Strict standard → UUID v7 or ULID
    └── Pure uniqueness, no sort needed?
        └── UUID v4 or NanoID
```

---

## Security Considerations

| Scenario | Risk | Solution |
|----------|------|----------|
| Auto-increment in public API | Enumeration attack | Hashids or switch to UUID |
| UUID v1 | MAC address leaks | Use v4 or v7 |
| Predictable random ID | Brute-force token guess | Use 128+ bits entropy (UUID/NanoID) |
| Snowflake | Timestamp extraction | Acceptable — time is not secret |
| Hashids | Reversible obfuscation | Add ACL — don't rely on obscurity alone |
| CUID (v1) | Hostname in fingerprint | Use CUID2 |

---

## SaaS Recommended Stack

### By Layer

| Layer | Type | Reason |
|-------|------|--------|
| Tenant/Org ID | UUID v4 | Generated once, globally unique, no sort needed |
| User PK (internal) | BIGINT auto | Fast joins |
| User public ID | ULID | Expose in API, sortable, opaque |
| Resource PK (posts, projects) | ULID | Sortable + unique, no extra created_at index |
| Audit/event log | ULID | Time-ordered free |
| Invite token / API key | NanoID or `Str::random(48)` | Pure random, high entropy, not guessable |

### Migration Pattern

```php
// Tenants
$table->uuid('id')->primary();

// Users (dual ID)
$table->id();                         // BIGINT internal
$table->ulid('public_id')->unique();  // expose this in API

// Resources
$table->ulid('id')->primary();

// Tokens
$table->string('token', 64)->unique();
// generate: hash('sha256', Str::random(40))
```

### Why NOT others

| Type | Problem |
|------|---------|
| Auto-increment only | Enumeration — `/invoices/10042` leaks volume |
| UUID v4 as PK | Index fragmentation at scale |
| Snowflake | Needs worker coordination infra |
| Hashids | Reversible — not real security |
| Adjective-noun | Collision risk at SaaS scale |

---

## Cross-App / Multi-App ID Strategy

### The Problem
Multiple apps (web, mobile, admin, workers) need to reference same entities and trace requests end-to-end.

### ID Types by Role

| Need | Type | Lives |
|------|------|-------|
| Trace request across services | UUID v4 (`X-Correlation-ID` header) | Request lifetime |
| User identity across all apps | ULID (auth service owns it) | Forever |
| Device identity | UUID v4 (client-generated, stored in keychain) | Until reinstall |
| Session | NanoID 32+ chars | Until logout |
| Prevent duplicate mutations | UUID v4 idempotency key (client-generated) | 24h |
| Async event dedup (webhooks, queues) | ULID | Until processed |

### Flow Diagram

```
┌─────────┐     ┌─────────┐     ┌──────────┐     ┌────────┐
│ Web App │     │  API    │     │  Queue   │     │ Mobile │
└────┬────┘     └────┬────┘     └────┬─────┘     └───┬────┘
     │               │               │               │
     user_id        ULID — canonical, auth service owns, shared by ALL apps
     session_id     NanoID — per login session
     device_id      UUID v4 — per device, generated on first install
     correlation_id UUID v4 — per HTTP request, dies with request
     idempotency_key UUID v4 — per mutation, client-generated
     event_id       ULID — per async event, for webhook dedup
```

### JWT Payload Pattern

```json
{
  "sub": "01J3K8XYZABC...",      ← ULID — canonical user ID
  "device_id": "4f7c8a2b-...",   ← UUID v4 — this device
  "session_id": "xK7m2pQr...",   ← NanoID — this session
  "correlation_id": "req-abc..."  ← trace this request
}
```

### Laravel Implementation

```php
// Middleware: stamp correlation ID on every inbound request
class CorrelationId
{
    public function handle($request, $next)
    {
        $id = $request->header('X-Correlation-ID') ?? (string) Str::uuid();
        $request->headers->set('X-Correlation-ID', $id);
        Log::withContext(['correlation_id' => $id]);
        return $next($request)->header('X-Correlation-ID', $id);
    }
}

// Idempotency key — prevent duplicate payments/mutations
$key = $request->header('Idempotency-Key');
if ($cached = Cache::get("idem:{$key}")) {
    return response()->json($cached); // replay stored result
}

// Async event with ULID — receiver deduplicates by event ID
$event = [
    'id'             => (string) Str::ulid(),  // dedup key
    'correlation_id' => $correlationId,         // trace back to origin
    'user_id'        => $user->public_id,       // canonical entity ref
    'type'           => 'payment.completed',
    'payload'        => $data,
];
```

### Key Rules
- Auth service **owns** user ULID — never let each app generate its own user ID
- Correlation ID generated at **edge** (gateway or first app touched), passed downstream via header
- Idempotency key generated by **client** — server only stores/checks it
- Device ID stored in **keychain** (iOS/Android) or localStorage (web) — survives app restart, not reinstall

---

## Questions to Explore

- [ ] UUID v7 vs ULID — real benchmark in MySQL/Postgres?
- [ ] How Stripe/Discord implement Snowflake worker coordination?
- [ ] NanoID PHP package options — maturity comparison?
- [ ] Hashids security — can attacker reverse without salt?
- [ ] Binary UUID storage in Laravel migrations?
- [ ] CUID2 PHP implementation status?
