# Challenge: Incomplete Dockerfile

## Difficulty: Easy — 3 missing pieces

## What This Challenge Is

A Dockerfile for DALT.PHP has been started but is missing three critical parts. The image cannot be built until all three are added.

Run the challenge to load the incomplete Dockerfile into your project root:

```bash
php artisan challenge:start docker-incomplete-dockerfile
```

Then open `Dockerfile` in your editor and complete it.

## The Three Missing Parts

### 1. Working directory

PHP-FPM expects to serve files from `/var/www/html`. Every `COPY` and `RUN` instruction after this point will execute from that path. Without it, your files land at `/` (the root) and paths break.

The instruction is `WORKDIR`. Set it to `/var/www/html`.

### 2. PHP extensions

DALT.PHP uses PDO to connect to databases. The `pdo` extension provides the base PDO class. The `pdo_pgsql` extension provides the PostgreSQL driver. Neither is installed in the base `php:8.2-fpm-alpine` image.

The helper command for installing PHP extensions inside Docker is `docker-php-ext-install`. You can install multiple extensions in one `RUN` command by listing them space-separated.

The `RUN` instruction must come before the `COPY` instructions — this keeps the slow extension compilation in a cached layer.

### 3. CMD to start PHP-FPM

When the container starts, it needs to know what process to run. PHP-FPM is started with the `php-fpm` command. Use the array form of `CMD` so the process runs as PID 1 and receives signals correctly.

## Files Involved

- `Dockerfile` — the only file you need to edit

## Verify Your Solution

```bash
php artisan challenge:verify
```

The verifier checks:
- `WORKDIR /var/www/html` is present
- `docker-php-ext-install` is called
- `pdo_pgsql` extension is installed
- `CMD ["php-fpm"]` is present
- No `# TODO` comments remain

## Testing the Build

After verifying, try actually building the image:

```bash
docker build -t dalt-php .
```

A successful build ends with:
```
Successfully built <hash>
Successfully tagged dalt-php:latest
```

If it fails, read the error — Docker tells you exactly which layer failed and why.

## Hints

- `WORKDIR` takes a single path argument: `WORKDIR /var/www/html`
- `docker-php-ext-install pdo pdo_pgsql` — both on the same line saves one layer
- `CMD ["php-fpm"]` — square brackets, quoted string, no flags needed

## Why These Three Things

**WORKDIR:** Without it, `COPY composer.json ./` copies to `/composer.json`, not `/var/www/html/composer.json`. Nginx points at `/var/www/html` — your files are in the wrong place.

**Extensions:** Without `pdo_pgsql`, the database connection throws: `could not find driver`. Without `pdo`, nothing database-related works at all.

**CMD:** Without `CMD`, the container starts but has no process to run. It exits immediately. A container stops when its main process stops.

## Next Challenge

After completing this, move on to **docker-broken-nginx** to fix the Nginx configuration that routes HTTP requests to PHP-FPM.
