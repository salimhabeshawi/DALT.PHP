# Challenge: db-broken-fts

## The Problem

The `GET /posts/search?q=term` endpoint searches posts using `ILIKE`. This works on a 10-row table in development. On a real dataset it's a disaster:

- `ILIKE '%keyword%'` does a **sequential scan** — it reads every row, every character
- It **can't use a B-tree index** because the leading `%` prevents index lookups
- Relevance ranking is impossible — all matches are equal

The `posts` table has a `search_vector` column that Postgres maintains automatically. It's a `GENERATED ALWAYS AS` column storing a pre-computed `tsvector` of `title` and `body`. There's already a GIN index on it. Your job is to use it.

## What You Need to Fix

Load this challenge:

```bash
php artisan challenge:start db-broken-fts
```

Two files are copied into your project:

- `Http/controllers/posts/search.php` — uses `ILIKE` and `%` string concatenation
- `routes/routes.php` — registers `GET /posts/search`

Open `Http/controllers/posts/search.php`. The query looks like this:

```php
$posts = $db->query(
    'SELECT id, title, created_at FROM posts WHERE title ILIKE :q ORDER BY created_at DESC',
    ['q' => '%' . $q . '%']
)->get();
```

There are three problems:
1. **`ILIKE`** — case-insensitive substring match, no index support
2. **`%` concatenation** — necessary for ILIKE but means no index can help
3. **No relevance ordering** — `ORDER BY created_at` ignores match quality

## What You Must Do

Replace the broken query with a full-text search using the existing `search_vector` column:

```php
$posts = $db->query(
    "SELECT id, title, created_at,
            ts_rank(search_vector, plainto_tsquery('english', :q)) AS relevance
     FROM posts
     WHERE search_vector @@ plainto_tsquery('english', :q)
     ORDER BY relevance DESC
     LIMIT 20",
    ['q' => $q]
)->get();
```

Changes to make:
- Replace `WHERE title ILIKE :q` with `WHERE search_vector @@ plainto_tsquery('english', :q)`
- Change `['q' => '%' . $q . '%']` to `['q' => $q]` — no `%` needed with full-text search
- Replace `ORDER BY created_at DESC` with `ORDER BY relevance DESC`
- Remove `ILIKE` entirely — it must not appear anywhere in the controller

## Hints

- `@@` is the full-text search match operator in Postgres
- `plainto_tsquery('english', :q)` converts plain search text into a normalized query — it handles multiple words and stemming automatically
- `search_vector` is a generated column — Postgres already maintains it from `title || ' ' || body`; you don't need to call `to_tsvector()` in your query
- `ts_rank(search_vector, query)` scores matches; higher is more relevant

## Verify

```bash
php artisan challenge:verify
```
