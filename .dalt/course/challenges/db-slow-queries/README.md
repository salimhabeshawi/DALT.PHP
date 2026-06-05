# Challenge: db-slow-queries

## The Problem

Your application is running slowly. `pg_stat_statements` shows that two queries are taking hundreds of milliseconds to execute because they are performing sequential scans on the `posts` table.

## What You Need to Fix

Load this challenge:

```bash
php artisan challenge:start db-slow-queries
```

You have three files:
- `Http/controllers/users/posts.php` — gets posts by user ID
- `Http/controllers/posts/published.php` — gets posts by status
- `database/migrations/004_add_indexes.sql` — an empty migration file

## What You Must Do

1. Read the two controllers and identify which columns they are filtering on in their `WHERE` clauses.
2. Open `database/migrations/004_add_indexes.sql`.
3. Write `CREATE INDEX` statements to add indexes to the missing columns on the `posts` table.
4. You need to create two separate indexes: one for the user ID and one for the status.

## Hints

- The syntax is `CREATE INDEX index_name ON table_name (column_name);`
- You need an index on `user_id` and an index on `status`.
- Name the indexes something logical, e.g., `idx_posts_user_id`.

## Verify

```bash
php artisan challenge:verify
```
