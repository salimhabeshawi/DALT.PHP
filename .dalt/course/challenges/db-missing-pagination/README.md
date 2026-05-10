# Challenge: Missing Pagination

## Difficulty: Easy — 1 fix

## What This Challenge Is

The `GET /db/users` endpoint returns every user in the database with no limit. On a small dataset that's harmless. On a production table with 100,000 rows it exhausts memory and takes seconds. Add pagination using `LIMIT` and `OFFSET` so callers can request one page at a time.

Load the broken file:

```bash
php artisan challenge:start db-missing-pagination
```

This adds:
- `app/Http/controllers/db/users/index.php` — `GET /db/users`

Start the dev server (`php artisan serve`) and visit `http://localhost:8000/db/users` to see the unpaginated response.

## The Bug

### No LIMIT or OFFSET in the query

```php
// BROKEN — returns every row in the table
$users = $db->query(
    'SELECT id, name, email, created_at FROM users ORDER BY created_at DESC'
)->get();
```

**Fix:** Read `?page=` and `?limit=` from the request. Calculate the offset. Add `LIMIT :limit OFFSET :offset` to the query. Return `page` and `limit` alongside the data.

```php
// CORRECT
$page   = max(1, (int)($_GET['page']  ?? 1));
$limit  = max(1, min(100, (int)($_GET['limit'] ?? 10)));
$offset = ($page - 1) * $limit;

$users = $db->query(
    'SELECT id, name, email, created_at FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset',
    ['limit' => $limit, 'offset' => $offset]
)->get();

header('Content-Type: application/json');
echo json_encode([
    'data'  => $users,
    'page'  => $page,
    'limit' => $limit,
]);
```

## File to Edit

- `app/Http/controllers/db/users/index.php`

## Verify Your Solution

```bash
php artisan challenge:verify
```

The verifier checks:
- `LIMIT` is in the query
- `OFFSET` is in the query
- `:limit` named parameter is used
- `:offset` named parameter is used
- `json_encode` is called

## Testing Manually

With `php artisan serve` running:

```bash
# Default: page 1, limit 10
curl http://localhost:8000/db/users

# Page 2, 5 per page
curl "http://localhost:8000/db/users?page=2&limit=5"
```

Expected response shape:

```json
{
  "data": [...],
  "page": 1,
  "limit": 10
}
```

## Hints

- `($page - 1) * $limit` converts a 1-based page number to a row offset
- Always cap the limit with `min(100, ...)` — don't let callers pull unlimited rows
- Both `LIMIT` and `OFFSET` must use named parameters (`:limit`, `:offset`), not raw values inline
- `max(1, ...)` prevents negative or zero page numbers from producing nonsense offsets
