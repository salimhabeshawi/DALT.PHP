# Lesson 11: DALT Database Layer

## From Raw SQL to Controller Code

In the last two lessons you wrote SQL directly in `psql`. Now you need to run those same queries from PHP controllers and return the results as JSON. DALT's database layer handles the PDO connection, prepared statements, and result fetching — you just write the SQL and pass the params.

This lesson covers every method on the `Database` object, shows how to wire the JOIN and aggregation queries from Lesson 10 into real controllers, adds pagination to list endpoints, and returns the raw PDO connection when you need transactions.

## Learning Objectives

- Resolve the `Database` instance from the container
- Use `->get()`, `->find()`, and `->findOrFail()` correctly
- Write paginated queries with `LIMIT` and `OFFSET`
- Build a JSON response from a controller
- Use `$db->getConnection()` for transactions with `rollBack()`

---

## Resolving the Database

Every controller that needs the database starts with this line:

```php
$db = \Core\App::resolve(\Core\Database::class);
```

`App::resolve` is DALT's IoC container. It returns the singleton `Database` instance, which wraps a PDO connection. The `Database` class is defined in `framework/Core/Database.php`.

---

## The Three Fetch Methods

### `->get()` — multiple rows

Returns an array of associative arrays. Returns an empty array if no rows match (never returns false).

```php
$users = $db->query(
    'SELECT id, name, email FROM users ORDER BY created_at DESC'
)->get();

// $users is: [['id' => 1, 'name' => 'Alice', 'email' => '...'], ...]
```

With parameters:

```php
$posts = $db->query(
    'SELECT id, title, created_at FROM posts WHERE user_id = :user_id ORDER BY created_at DESC',
    ['user_id' => $userId]
)->get();
```

### `->find()` — one row or false

Returns a single associative array, or `false` if no row matches. Use this when you're looking up a specific record and want to handle the not-found case yourself.

```php
$user = $db->query(
    'SELECT id, name, email FROM users WHERE id = :id',
    ['id' => $id]
)->find();

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}
```

### `->findOrFail()` — one row or 404

Like `->find()`, but throws a 404 automatically if no row is found. Use this when a missing record is always an error (e.g., a show endpoint).

```php
$user = $db->query(
    'SELECT id, name, email FROM users WHERE id = :id',
    ['id' => $id]
)->findOrFail();
// Never reaches here if no user found — 404 is thrown
```

---

## Wiring JOIN Queries into Controllers

Take the JOIN from Lesson 10 and put it in a controller:

```php
<?php

$db = \Core\App::resolve(\Core\Database::class);

$posts = $db->query(
    'SELECT posts.id, posts.title, posts.created_at, users.name AS author
     FROM posts
     LEFT JOIN users ON posts.user_id = users.id
     ORDER BY posts.created_at DESC'
)->get();

header('Content-Type: application/json');
echo json_encode($posts);
```

Routes file:

```php
$router->get('/posts', 'posts/index.php');
```

Visit `GET /posts` and you get a JSON array of posts with the author name embedded.

---

## Pagination with LIMIT and OFFSET

Returning all rows from a large table in one response is a common beginner mistake. At 10 rows it's fine. At 100,000 rows it kills memory and response time.

The fix: accept `?page=` and `?limit=` query params and use `LIMIT` and `OFFSET` in the query.

```php
<?php

$db = \Core\App::resolve(\Core\Database::class);

$page  = max(1, (int)($_GET['page']  ?? 1));
$limit = max(1, min(100, (int)($_GET['limit'] ?? 10)));
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

How `LIMIT` and `OFFSET` work:

- `LIMIT 10` — return at most 10 rows
- `OFFSET 0` — skip 0 rows (page 1)
- `OFFSET 10` — skip 10 rows (page 2)
- `OFFSET 20` — skip 20 rows (page 3)

The formula `($page - 1) * $limit` converts a 1-based page number to the correct offset.

**Always cap the limit.** `min(100, ...)` prevents a client from sending `?limit=1000000` and pulling your entire table.

---

## Writing JSON Responses

The pattern for every JSON controller in DALT:

```php
// Set the content type header before any output
header('Content-Type: application/json');

// Encode and output — json_encode handles arrays and nested objects
echo json_encode($data);

// exit to prevent any accidental output after
exit;
```

PHP arrays cannot be `echo`ed directly — `echo $array` prints the string "Array". Always use `json_encode`.

For error responses, set the status code before the header:

```php
http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['error' => 'Not found']);
exit;
```

---

## Transactions from PHP

When you need multiple writes to succeed or fail together, use `$db->getConnection()` to get the raw PDO object and wrap the queries in a transaction.

```php
<?php

$db  = \Core\App::resolve(\Core\Database::class);
$pdo = $db->getConnection();

$fromId = $_POST['from_id'] ?? null;
$toId   = $_POST['to_id']   ?? null;
$amount = (int)($_POST['amount'] ?? 0);

try {
    $pdo->beginTransaction();

    $db->query(
        'UPDATE users SET credits = credits - :amount WHERE id = :id',
        ['amount' => $amount, 'id' => $fromId]
    );

    $db->query(
        'UPDATE users SET credits = credits + :amount WHERE id = :id',
        ['amount' => $amount, 'id' => $toId]
    );

    $pdo->commit();

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} catch (\Exception $e) {
    $pdo->rollBack();

    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Transfer failed']);
}
```

Key points:

- `$db->getConnection()` returns the underlying `PDO` object
- `$pdo->beginTransaction()` starts the transaction
- `$pdo->commit()` applies all changes atomically
- `$pdo->rollBack()` in the `catch` undoes everything since `beginTransaction()`
- Without `rollBack()`, a failed partial transaction leaves your data inconsistent

---

## Building the Posts + Author Endpoint

Here's a complete controller that demonstrates JOIN, pagination, and a JSON response together:

```php
<?php

$db = \Core\App::resolve(\Core\Database::class);

$page   = max(1, (int)($_GET['page']  ?? 1));
$limit  = max(1, min(50, (int)($_GET['limit'] ?? 10)));
$offset = ($page - 1) * $limit;

$posts = $db->query(
    'SELECT posts.id, posts.title, posts.created_at, users.name AS author
     FROM posts
     LEFT JOIN users ON posts.user_id = users.id
     ORDER BY posts.created_at DESC
     LIMIT :limit OFFSET :offset',
    ['limit' => $limit, 'offset' => $offset]
)->get();

header('Content-Type: application/json');
echo json_encode([
    'data'  => $posts,
    'page'  => $page,
    'limit' => $limit,
]);
```

This is the pattern you'll use in the challenges that follow.

---

## Summary

| Method | Returns | Use when |
|---|---|---|
| `->get()` | array of rows | list endpoints, always succeeds |
| `->find()` | row or false | you need to handle not-found yourself |
| `->findOrFail()` | row or 404 | missing record is always an error |

- `LIMIT :limit OFFSET :offset` + `?page=` + `?limit=` is the standard pagination pattern
- Always set `Content-Type: application/json` before `echo json_encode()`
- Get the raw PDO via `$db->getConnection()` for transactions
- `rollBack()` in the `catch` block is not optional — it's what makes the transaction safe

## Your Task

Load the broken pagination controller:

```bash
php artisan challenge:start db-missing-pagination
```

The `GET /db/users` endpoint returns all users with no pagination. Add `LIMIT` and `OFFSET` using `:limit` and `:offset` named parameters, and structure the response as `{"data": [...], "page": 1, "limit": 10}`.

Verify:

```bash
php artisan challenge:verify
```

## Next Steps

- **Challenge: db-missing-pagination** — add LIMIT/OFFSET pagination to a users list endpoint
- **Challenge: db-broken-join** — fix wrong JOIN type and wrong ON clause
- **Challenge: db-broken-transaction** — add ROLLBACK to a transfer endpoint
