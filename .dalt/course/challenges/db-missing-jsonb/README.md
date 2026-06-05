# Challenge: db-missing-jsonb

## The Problem

The `POST /posts` endpoint accepts a `metadata` JSON field in the request body. However, the query that inserts the post ignores it, and the query that fetches the posts doesn't return it.

The `posts` table already has a `metadata JSONB` column. Your job is to wire it up in the controller so the data is saved and returned.

## What You Need to Fix

Load this challenge:

```bash
php artisan challenge:start db-missing-jsonb
```

Three files are copied into your project:

- `Http/controllers/posts/store.php` — inserts a post
- `Http/controllers/posts/index.php` — lists posts
- `routes/routes.php` — registers the endpoints

Open `Http/controllers/posts/store.php`. The query looks like this:

```php
$db->query(
    'INSERT INTO posts (title, body, user_id) VALUES (:title, :body, :user_id)',
    [
        'title'   => $_POST['title'] ?? '',
        'body'    => $_POST['body'] ?? '',
        'user_id' => $user['id'],
    ]
);
```

Open `Http/controllers/posts/index.php`. The query looks like this:

```php
$posts = $db->query(
    'SELECT id, title, created_at FROM posts ORDER BY created_at DESC'
)->get();
```

## What You Must Do

1. **Update the INSERT query**: Add the `metadata` column to the `INSERT INTO` statement and pass the `:metadata` parameter.
2. **Update the parameter array**: Pass `$_POST['metadata'] ?? null` to the `:metadata` parameter in the `$db->query` call in `store.php`.
3. **Update the SELECT query**: Add the `metadata` column to the `SELECT` statement in `index.php`.

## Hints

- `INSERT INTO posts (title, body, user_id, metadata) VALUES (:title, :body, :user_id, :metadata)`
- The value for `:metadata` can be `null` if the user didn't provide it, or the raw JSON string from `$_POST['metadata']`.
- In `index.php`, just add `metadata` to the list of columns selected.

## Verify

```bash
php artisan challenge:verify
```
