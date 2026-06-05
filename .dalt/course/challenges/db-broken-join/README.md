# Challenge: Broken JOIN

## Difficulty: Medium — 2 bugs in 1 file

## What This Challenge Is

The `GET /db/posts` endpoint should return all posts with the author's name embedded. The JOIN query has two bugs: the wrong join type causes some users to disappear, and the ON clause references the wrong column so the results are completely scrambled.

Load the broken file:

```bash
php artisan challenge:start db-broken-join
```

This adds:
- `app/Http/controllers/db/posts/index.php` — `GET /db/posts`

## The Two Bugs

### Bug 1 — INNER JOIN drops users with no posts

The query uses `INNER JOIN`:

```php
// BROKEN — INNER JOIN returns only rows that match on both sides.
// A user with no posts disappears from the results entirely.
$posts = $db->query(
    'SELECT posts.id, posts.title, posts.created_at, users.name AS author
     FROM posts
     INNER JOIN users ON posts.id = users.id
     ORDER BY posts.created_at DESC'
)->get();
```

**Fix:** Change `INNER JOIN` to `LEFT JOIN`. A LEFT JOIN returns all posts, with the author's name if a matching user exists (and NULL if not).

```php
// CORRECT join type
FROM posts
LEFT JOIN users ON posts.user_id = users.id
```

### Bug 2 — Wrong column in the ON clause

The ON clause says `posts.id = users.id`. This joins each post to the user whose `id` matches the post's own primary key — which is almost never the intended user. The correct link is `posts.user_id = users.id`.

```sql
-- BROKEN: joins post #5 to user #5 — wrong relationship
ON posts.id = users.id

-- CORRECT: joins post to its actual author
ON posts.user_id = users.id
```

If you only fix Bug 1 without fixing Bug 2, posts will still have the wrong author (or no author at all for most rows).

### Complete fix

```php
// CORRECT
$posts = $db->query(
    'SELECT posts.id, posts.title, posts.created_at, users.name AS author
     FROM posts
     LEFT JOIN users ON posts.user_id = users.id
     ORDER BY posts.created_at DESC'
)->get();
```

## File to Edit

- `app/Http/controllers/db/posts/index.php`

## Verify Your Solution

```bash
php artisan challenge:verify
```

The verifier checks:
- `LEFT JOIN` is present
- `INNER JOIN` is absent
- `posts.user_id = users.id` is present
- `posts.id = users.id` is absent

## Testing Manually

With `php artisan serve` running (and some posts + users in the database):

```bash
curl http://localhost:8000/db/posts
```

Expected: a JSON array where each post object has an `author` field with the user's name.

## Hints

- Run `\d posts` in psql to confirm the foreign key column is named `user_id`, not just `id`
- `INNER JOIN` and `LEFT JOIN` aren't interchangeable — choose based on whether missing right-side rows should be dropped or kept
- The ON clause is where most JOIN bugs live: always ask "what column in the left table references a column in the right table?"
