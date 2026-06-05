# Challenge: db-migrations-disorder

## The Problem

Migrations must execute in a specific order. If Table B has a foreign key pointing to Table A, Table A must be created first. 

DALT's migration runner executes files in alphabetical order based on their filenames.

You have two migration files:
- `001_create_posts_table.sql`
- `002_create_users_table.sql`

The `posts` table has a `user_id` column that `REFERENCES users(id)`. Because `001` runs before `002`, the migration crashes with an error: `relation "users" does not exist`.

Furthermore, the person who wrote the posts migration used SQLite syntax (`INTEGER PRIMARY KEY AUTOINCREMENT`) instead of the Postgres equivalent (`BIGSERIAL PRIMARY KEY`). DALT's runner normally auto-converts this for you, but for this course we want you to write raw, native Postgres SQL.

## What You Need to Fix

Load this challenge:

```bash
php artisan challenge:start db-migrations-disorder
```

Two files are copied to `database/migrations/`:
- `001_create_posts_table.sql`
- `002_create_users_table.sql`

1. **Rename the files** so that the users table is `001_` and the posts table is `002_`.
2. **Open the posts migration** (now `002_create_posts_table.sql`) and change `INTEGER PRIMARY KEY AUTOINCREMENT` to `BIGSERIAL PRIMARY KEY`.

## Hints

- Use your editor or `mv` to rename the files. The test specifically checks the contents of `001_create_users_table.sql` and `002_create_posts_table.sql`.
- `BIGSERIAL` is the Postgres type for a self-incrementing 8-byte integer.

## Verify

```bash
php artisan challenge:verify
```
