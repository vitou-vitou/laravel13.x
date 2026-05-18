# Database Indexing Guide

## 1. Concepts

- Index = sorted pointer structure. DB finds rows without full scan.
- **B-tree** — default. Range queries, ORDER BY, equality. Most common.
- **Hash** — equality only. Faster for `=`, useless for `<`, `>`, `LIKE`.
- **Composite** — multi-column. Order matters: `(a, b, c)` helps `WHERE a=1`, `WHERE a=1 AND b=2`, NOT `WHERE b=2` alone.
- **Covering index** — index contains all columns query needs. Zero table lookup.
- **Full-text** — `MATCH AGAINST`. Text search.

## 2. When Indexes Help vs Hurt

| Helps | Hurts |
|-------|-------|
| `WHERE`, `JOIN ON`, `ORDER BY` columns | Write-heavy tables (INSERT/UPDATE slower) |
| High-cardinality columns | Low-cardinality (`is_active` bool — useless) |
| Foreign keys | Too many indexes = wasted storage |

## 3. Laravel Migration Syntax

```php
$table->index('email');
$table->index(['user_id', 'created_at']); // composite
$table->unique('slug');
$table->fullText('body');
```

## 4. Debug Slow Queries

```php
// Explain a query
DB::table('invoices')->where('user_id', 1)->explain();

// Log all queries
DB::enableQueryLog();
// ... run code ...
dd(DB::getQueryLog());
```

### Reading EXPLAIN output

| Value | Meaning |
|-------|---------|
| `type: ALL` | Full table scan — needs index |
| `type: ref` or `range` | Index used ✓ |
| `Extra: Using index` | Covering index ✓ |

## 5. IDE Tools

| Tool | Use |
|------|-----|
| TablePlus / DBeaver | Visual index manager, query explain GUI |
| PHPStorm DB plugin | Inline explain, schema navigator |
| MySQL Workbench | Visual EXPLAIN diagram |

## 6. Laravel-Specific Tools

- **Laravel Telescope** — logs slow queries in dev
- **Clockwork** — query timeline in browser devtools
- **laravel-query-detector** — warns N+1 in dev

## 7. Learning Path

1. Learn concepts (B-tree, composite, covering)
2. Write migrations with indexes
3. Run `explain()` on real queries
4. Use Telescope to find slow queries
5. Fix with indexes
6. Verify with explain again
