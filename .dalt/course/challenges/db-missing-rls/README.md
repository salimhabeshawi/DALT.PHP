# Challenge: db-missing-rls

## The Problem

Right now, your application isolates tenant data by manually adding `WHERE tenant_id = :id` to every query in PHP. If a developer creates a new endpoint and forgets to add that `WHERE` clause, the API will leak data from other tenants.

Row-Level Security (RLS) moves this responsibility to the database. When RLS is active, Postgres will automatically append the tenant filter to every query against the table.

## What You Need to Fix

Load this challenge:

```bash
php artisan challenge:start db-missing-rls
```

Three files are provided:
- `database/migrations/003_enable_rls.sql` — Currently empty.
- `Http/controllers/tenant/posts.php` — A controller that manually filters by `tenant_id`.
- `routes/routes.php` — Registers the route.

## What You Must Do

### 1. Write the Migration

Open `database/migrations/003_enable_rls.sql`.

1. Enable RLS on the `posts` table:
   `ALTER TABLE posts ENABLE ROW LEVEL SECURITY;`
2. Create a policy named `tenant_isolation` on the `posts` table. The policy should allow access if `tenant_id = current_setting('app.tenant_id')::INT`.

### 2. Update the Controller

Open `Http/controllers/tenant/posts.php`.

1. Before running the `SELECT` query, execute a `$db->query()` that sets the Postgres session variable:
   `SET app.tenant_id = :id` (pass the `$tenantId` as the parameter).
2. Remove the `WHERE tenant_id = :id` clause from the `$posts` query entirely. The query should just be `SELECT * FROM posts ORDER BY created_at DESC`.

Postgres will now automatically intercept the `SELECT * FROM posts` and filter it based on the `app.tenant_id` session variable you set in the previous query.

## Hints

- The syntax to create a policy looks like:
  ```sql
  CREATE POLICY tenant_isolation ON posts
  USING (tenant_id = current_setting('app.tenant_id')::INT);
  ```
- Make sure to actually call `$db->query('SET app.tenant_id = :id', ['id' => $tenantId]);` in PHP before querying the posts.

## Verify

```bash
php artisan challenge:verify
```
