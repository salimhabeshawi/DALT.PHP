# Challenge: Missing Compose Services

## Difficulty: Easy — 2 missing services

## What This Challenge Is

A `docker-compose.yml` has been started for DALT.PHP. It defines the `app` service (your PHP-FPM container) but the stack is incomplete — the database and web server services are missing. Without them, you have a PHP process that can't accept HTTP requests and has nowhere to store data.

Run the challenge to load the incomplete file into your project:

```bash
php artisan challenge:start docker-compose-missing-services
```

Then open `docker-compose.yml` and add the two missing services.

## The Two Services to Add

### 1. `db` — PostgreSQL database

The official image is `postgres:16-alpine`. It needs:

**Environment variables** — use `env_file: .env`. Your `.env` file should contain:
```
POSTGRES_DB=dalt
POSTGRES_USER=postgres
POSTGRES_PASSWORD=secret
```
(Add these to `.env` if they aren't there yet.)

**Persistent storage** — Postgres data must survive container restarts. Mount the named volume `pgdata` (already declared in `volumes:`) at `/var/lib/postgresql/data` inside the container:
```yaml
volumes:
  - pgdata:/var/lib/postgresql/data
```

### 2. `nginx` — Web server

The official image is `nginx:alpine`. It needs:

**The config file** — mount the `nginx/default.conf` you completed in the previous challenge:
```yaml
volumes:
  - ./nginx/default.conf:/etc/nginx/conf.d/default.conf
```

**Port mapping** — expose port 8080 on your host machine, forwarding to port 80 inside the container:
```yaml
ports:
  - "8080:80"
```

### Update `app` to depend on `db`

The app container needs the database to be running before it starts. Add:
```yaml
depends_on:
  - db
```

## File Involved

- `docker-compose.yml` — the only file to edit

## Verify Your Solution

```bash
php artisan challenge:verify
```

The verifier checks:
- `postgres:16-alpine` image is declared
- `nginx:alpine` image is declared
- `/var/lib/postgresql/data` volume mount is present
- `depends_on` is set on the app service
- Port `8080` is mapped

## Running the Full Stack

After verifying, try starting the full stack:

```bash
docker compose up
```

Then visit `http://localhost:8080` — you should see the DALT.PHP welcome page.

To stop:
```bash
docker compose down
```

To stop and delete the database volume (fresh start):
```bash
docker compose down -v
```

## Debugging Compose Issues

**"Service 'db' failed to build"** — `db` uses an image, not a build. Make sure you wrote `image: postgres:16-alpine`, not `build: .`.

**"Bind mount failed"** — The `nginx/default.conf` file must exist on your host. Complete the `docker-broken-nginx` challenge first if you haven't.

**"Port 8080 already in use"** — Something else is using 8080. Change the host port to `8081:80` temporarily.

**"Connection refused" from PHP to Postgres** — Check that `DB_HOST=db` in your `.env`. The hostname must match the service name in `docker-compose.yml`.

## Next Challenge

**db-first-queries** — Fix broken SQL controllers that run against the users table.
