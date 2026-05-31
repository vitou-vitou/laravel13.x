# Todo Tags Feature — Design Spec
Date: 2026-05-30

## Overview

Add tags support to todos. Tags stored as JSON array on the `todos` table. Filter todos by tag via query param.

## Data Layer

**Migration:** Add `tags` column to `todos` table.

```php
$table->json('tags')->nullable();
```

**Factory default:** `'tags' => []`

## Model (App\Models\Todo)

- Cast `tags` as `array`
- Add `tags` to `#[Fillable]`
- Add `scopeByTag(string $tag)` — filters where JSON array contains value

## Validation

Both `StoreTodoRequest` and `UpdateTodoRequest`:

```php
'tags'   => 'nullable|array',
'tags.*' => 'string|max:50',
```

## Resource (TodoResource)

```php
'tags' => $this->tags ?? [],
```

## API

### Store with tags
`POST /api/v1/todos` — accepts `tags: ["work", "urgent"]`

### Update tags
`PATCH /api/v1/todos/{id}` — accepts `tags: [...]`, replaces existing

### Filter by tag
`GET /api/v1/todos?tag=work` — returns todos where tags JSON contains "work"

## TDD Test Order

Tests written BEFORE each implementation piece:

1. `test_store_todo_with_tags` — 201, tags appear in response
2. `test_store_todo_without_tags_defaults_to_empty_array` — tags = []
3. `test_store_fails_when_tags_is_not_an_array` — 422
4. `test_store_fails_when_tag_exceeds_50_chars` — 422
5. `test_index_filters_by_tag` — only matching todos returned
6. `test_update_replaces_tags` — PATCH replaces tags array

## Out of Scope

- Tag autocomplete endpoint
- Tag management (CRUD for tags themselves)
- Multiple tag filter (`?tag=a&tag=b`)
