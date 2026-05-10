# Challenge: Broken First Queries

## Difficulty: Easy — 3 bugs across 2 files

## What This Challenge Is

Two controller files handle simple user API endpoints — one lists users, one fetches a single user. Together they have three bugs that cover the most common mistakes when writing raw SQL in PHP for the first time.

Load the broken files:

```bash
php artisan challenge:start db-first-queries
```

This adds:
- `app/Http/controllers/users/index.php` — `GET /users`
- `app/Http/controllers/users/show.php` — `GET /users/{id}`

Start the dev server (`php artisan serve`) and visit `http://localhost:8000/users` to see the broken behavior.

## The Three Bugs

### Bug 1 — SQL Injection in `users/index.php`

The search query concatenates the `$search` variable directly into the SQL string:

```php
// BROKEN — string interpolation puts raw user input inside the SQL
$users = $db->query(
    "SELECT id, name, email FROM users WHERE email LIKE '%{$search}%'"
)->get();
```

An attacker can send `?search=%' OR '1'='1` and bypass the WHERE clause entirely.

**Fix:** Replace string concatenation with a named parameter.

```php
// CORRECT
$users = $db->query(
    'SELECT id, name, email FROM users WHERE email ILIKE :search ORDER BY created_at DESC',
    ['search' => '%' . $search . '%']
)->get();
```

Note `ILIKE` — Postgres's case-insensitive LIKE. If you're running SQLite, use `LIKE` (SQLite's LIKE is case-insensitive by default for ASCII).

When `$search` is empty, you can skip the WHERE clause entirely:
```php
if ($search) {
    $users = $db->query(
        'SELECT id, name, email FROM users WHERE email ILIKE :search ORDER BY created_at DESC',
        ['search' => '%' . $search . '%']
    )->get();
} else {
    $users = $db->query('SELECT id, name, email FROM users ORDER BY created_at DESC')->get();
}
```

### Bug 2 — Wrong Column Name in `users/show.php`

The query uses `WHERE user_id = :id`, but the `users` table has no `user_id` column. The primary key column is simply `id`.

```php
// BROKEN
$user = $db->query(
    'SELECT id, name, email FROM users WHERE user_id = :id',
    ['id' => $id]
)->find();
```

On Postgres this throws an error: `column "user_id" does not exist`. On SQLite it returns no results silently.

**Fix:** Change `user_id` to `id`.

```php
// CORRECT
$user = $db->query(
    'SELECT id, name, email FROM users WHERE id = :id',
    ['id' => $id]
)->find();
```

How to confirm the correct column name: run `\d users` in psql, or open `database/migrations/001_create_users_table.sql`.

### Bug 3 — `var_dump` Instead of `json_encode` in `users/show.php`

The controller claims to return JSON (`Content-Type: application/json`) but the body contains PHP's `var_dump` output — not valid JSON. Any API client will fail to parse it.

```php
// BROKEN — both the 404 and 200 paths are wrong
var_dump(['error' => 'User not found']);
// ...
echo $user;   // PHP arrays can't be echoed — prints "Array"
```

**Fix:** Replace `var_dump` with `json_encode` on the error response, and use `json_encode` on the success response too.

```php
// CORRECT
if (!$user) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User not found']);
    exit;
}

header('Content-Type: application/json');
echo json_encode($user);
```

## Files to Edit

- `app/Http/controllers/users/index.php` — fix Bug 1
- `app/Http/controllers/users/show.php` — fix Bugs 2 and 3

## Verify Your Solution

```bash
php artisan challenge:verify
```

The verifier checks:
- No string concatenation with `$search` in `index.php`
- `:search` parameter binding is used in `index.php`
- `WHERE id = :id` in `show.php`
- No `WHERE user_id` in `show.php`
- `json_encode` used in `show.php`
- No `var_dump` in `show.php`

## Testing Manually

With `php artisan serve` running:

**List users:**
```bash
curl http://localhost:8000/users
```

**Search users:**
```bash
curl "http://localhost:8000/users?search=alice"
```

**Show one user (use a real id from the list):**
```bash
curl http://localhost:8000/users/1
```

**404 case:**
```bash
curl http://localhost:8000/users/9999
```

Expected: all responses return valid JSON with a `Content-Type: application/json` header.

## Hints

- Confirm column names by running `\d users` in psql, or reading `database/migrations/001_create_users_table.sql`
- PHP arrays cannot be `echo`ed — you always need `json_encode()`
- `ILIKE` is Postgres's case-insensitive LIKE; use plain `LIKE` if running SQLite

## Next Steps

- **Lesson 10: PostgreSQL Core** — JOINs, aggregations, indexes, and transactions
