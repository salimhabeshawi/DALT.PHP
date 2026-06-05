# Challenge: docker-missing-multistage

## The Problem

Docker builds your image in a single stage. That means the final image contains Composer, its dependencies, and any build tooling — none of which belong in production.

Right now your image is unnecessarily large. Multi-stage builds let you use a heavy `builder` image to install dependencies, then throw it away and copy only the artifacts you need into a lean `runtime` image.

## What You Need to Fix

Load this challenge:

```bash
php artisan challenge:start docker-missing-multistage
```

A `Dockerfile` is copied into your project root. It works — but it installs Composer directly into the final image. This is the broken pattern you need to fix.

Open the `Dockerfile`. You'll see three problems:

1. **No builder stage** — there's only one `FROM` block, and it installs everything into the same image that runs PHP-FPM in production
2. **Composer binary copied into the runtime image** — the line `COPY --from=composer:2 /usr/bin/composer /usr/bin/composer` puts Composer where it doesn't belong
3. **No HEALTHCHECK** — Docker has no way to know whether PHP-FPM is actually accepting requests, only that the process is alive

## What You Must Do

Convert the Dockerfile to a proper two-stage build:

### Stage 1: `builder`

Use `FROM composer:2 AS builder` as your first stage. This image has PHP and Composer pre-installed. Your job in this stage is only to install dependencies:

```
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader
```

### Stage 2: `runtime`

Start fresh with `FROM php:8.2-fpm-alpine`. Install extensions. Then copy the vendor directory from the builder — not Composer itself:

```
COPY --from=builder /app/vendor ./vendor
```

Remove `COPY --from=composer:2 /usr/bin/composer /usr/bin/composer` entirely — that line installs the Composer binary into your production image, which is what you're trying to avoid.

### Add a HEALTHCHECK

Before the `CMD`, add a `HEALTHCHECK` that tests PHP-FPM's configuration:

```
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD php-fpm -t || exit 1
```

## Hints

- The first `FROM` should be `FROM composer:2 AS builder` and sets up a temporary build environment
- The second `FROM` is `FROM php:8.2-fpm-alpine` — this is the image that actually runs in production
- `COPY --from=builder /app/vendor ./vendor` pulls the installed `vendor/` directory from the builder stage into the runtime image
- `HEALTHCHECK` goes after the `EXPOSE` instruction, before `CMD`

## Verify

```bash
php artisan challenge:verify
```

All five checks must pass.
