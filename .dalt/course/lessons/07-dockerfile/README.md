# Lesson 07: Writing Dockerfiles

## What a Dockerfile Is

A Dockerfile is a recipe. It's a plain text file that tells Docker exactly how to build an image for your app — which base to start from, which tools to install, which files to copy, and how to start the process.

Every instruction in a Dockerfile creates a layer. Docker caches layers. When you rebuild, only layers after the first change get rebuilt.

This lesson walks through a production-style Dockerfile for DALT.PHP, every line explained — not just what it does, but **why**.

## Learning Objectives

By the end of this lesson, you will:
- Know what every common Dockerfile instruction does and why it's there
- Understand why layer order matters (and how caching saves build time)
- Know why PHP-FPM is used instead of PHP CLI for serving requests
- Know how Alpine Linux reduces image size
- Understand why `pdo_pgsql` needs to be explicitly installed
- Be ready to complete the `docker-incomplete-dockerfile` challenge

## The Complete Dockerfile for DALT.PHP

Here is the full Dockerfile you'll be building toward. Read through it — every line will be explained below.

```dockerfile
FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

RUN docker-php-ext-install pdo pdo_pgsql

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction

COPY . .

EXPOSE 9000

CMD ["php-fpm"]
```

That's 9 lines. Let's go through each one.

## Instruction by Instruction

### `FROM php:8.2-fpm-alpine`

Every Dockerfile starts with `FROM`. This is your base image — the foundation everything else is built on.

Breaking down `php:8.2-fpm-alpine`:
- `php` — the official PHP image from Docker Hub
- `8.2` — PHP version 8.2
- `fpm` — PHP-FPM variant (more on this below)
- `alpine` — built on Alpine Linux

**Why Alpine Linux?**
Alpine is a minimal Linux distribution (~5MB). The alternative, Debian-based images (`php:8.2-fpm`), are ~450MB. Alpine-based images are ~80MB. Smaller images build faster, transfer faster, and have a smaller attack surface.

**Why `fpm` and not `cli`?**
- `php:8.2-cli` — runs PHP commands, one at a time. Good for scripts, artisan commands.
- `php:8.2-fpm` — runs PHP-FPM, a process manager that handles HTTP requests. Required when Nginx sits in front of your app.

When Nginx receives an HTTP request for a `.php` file, it can't run PHP itself. It forwards the request to PHP-FPM over FastCGI. PHP-FPM processes it and returns the response to Nginx. This is how real production PHP runs.

```
Browser → Nginx (port 80) → PHP-FPM (port 9000) → Your PHP code
```

You cannot use `php:8.2-cli` as a web server base image. Always use `fpm` when Nginx is in front.

### `WORKDIR /var/www/html`

Sets the working directory inside the container. All subsequent commands (`RUN`, `COPY`, `CMD`) run from this path.

`/var/www/html` is the conventional path for web apps in Linux. You could use anything, but stick to conventions — Nginx configs and other tools often reference this path.

Without `WORKDIR`, Docker defaults to `/`. You'd end up with your files scattered at the root.

### `RUN docker-php-ext-install pdo pdo_pgsql`

`RUN` executes a shell command during the image build. This one installs two PHP extensions:
- `pdo` — PHP Data Objects, the abstraction layer for database access. Required for `new PDO(...)` to work.
- `pdo_pgsql` — the PostgreSQL driver for PDO. Required for `pgsql:host=...` DSNs to work.

`docker-php-ext-install` is a helper script built into the official PHP Docker images. It handles compilation, system dependency resolution, and enabling the extension — all in one command.

**Why isn't pdo_pgsql installed by default?**
The PHP image ships with minimal extensions to keep the image small. You opt in to what you need.

**Why this runs before COPY?**
Extension installation is slow and doesn't depend on your code. If it's in a layer before your code, Docker caches it. You won't wait for extension compilation on every code change — only when this line changes.

### `COPY composer.json composer.lock ./`

`COPY` copies files from your host machine into the image.

`./` means "into the current WORKDIR" (`/var/www/html`).

**Why copy just these two files first, not everything?**
Layer caching. `composer install` takes 20–60 seconds depending on packages. If we copy the whole project first, any file change (even changing a comment) would invalidate the cache and force a full `composer install` on every build.

By copying only `composer.json` and `composer.lock` first, Docker only re-runs `composer install` when your dependencies actually change — not when your code changes.

### `RUN composer install --no-dev --no-interaction`

Installs PHP dependencies from the lock file.

- `--no-dev` — skips development-only packages (PHPUnit, debug tools). Production images shouldn't have dev tools.
- `--no-interaction` — disables interactive prompts. Essential for automated builds — no one to type "yes" in CI.

**Where does Composer come from?**
The official PHP images don't include Composer by default. But in later lessons you'll see how to get it via a multi-stage build. For now, if your base image doesn't have Composer, add this before the `RUN`:
```dockerfile
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
```

### `COPY . .`

Copies your entire project into the image, at `WORKDIR` (`/var/www/html`).

First `.` = source (your project directory on the host).  
Second `.` = destination (the WORKDIR inside the image).

**This comes AFTER `composer install`**, so the vendor directory is already in place from the previous step. The `COPY . .` overlays your source code on top — a clean separation between dependencies and source.

**What about sensitive files?**
Use `.dockerignore` (covered below) to exclude `.env`, `.git`, `node_modules`, SQLite database files, and anything that shouldn't go into the image.

### `EXPOSE 9000`

Documents that the container listens on port 9000. PHP-FPM listens on 9000 by default.

`EXPOSE` does **not** actually publish the port to the host. It's documentation — a signal to Docker Compose and other tools about which port the container uses. Publishing to the host happens with `-p` flag or in `docker-compose.yml`.

### `CMD ["php-fpm"]`

`CMD` is the default command that runs when the container starts.

`["php-fpm"]` starts PHP-FPM in the foreground. This keeps the container alive (a container stops when its main process stops).

**Why array form, not string form?**
- String form (`CMD "php-fpm"`) runs via a shell: `sh -c "php-fpm"`. This adds a shell process as PID 1, which can interfere with signal handling.
- Array form (`CMD ["php-fpm"]`) runs the process directly as PID 1. Signals like `SIGTERM` go straight to php-fpm, allowing graceful shutdown.

Always use array form for `CMD` and `ENTRYPOINT`.

## The `.dockerignore` File

The `.dockerignore` file tells Docker what to exclude from the build context — the set of files sent to Docker during `docker build`.

Without it, Docker sends your entire project to the Docker daemon, including things like `.git/` (hundreds of MB), `node_modules/`, and `database/app.sqlite`.

Create `.dockerignore` in your project root:

```
.git
.env
node_modules
vendor
database/*.sqlite
storage/logs/*.log
public/build
*.md
tests
```

**Why exclude `vendor`?**
The Dockerfile runs `composer install` inside the image. The `vendor/` from your host isn't needed — it would just overwrite the one built inside the image.

**Why exclude `.env`?**
Environment variables are injected at runtime via `docker compose`, not baked into the image. Baking `.env` into the image is a security risk — the image might be pushed to a registry.

## Layer Cache in Practice

This is the most important concept in Dockerfile optimization. Let's trace what happens on a rebuild:

**First build:**
```
Layer 1: FROM php:8.2-fpm-alpine       ← downloaded (slow)
Layer 2: WORKDIR /var/www/html         ← created
Layer 3: RUN docker-php-ext-install    ← compiled (slow)
Layer 4: COPY composer.json ...        ← copied
Layer 5: RUN composer install          ← installed (slow)
Layer 6: COPY . .                      ← copied
Layer 7: EXPOSE 9000                   ← recorded
Layer 8: CMD ["php-fpm"]               ← recorded
```

**You change a PHP file, rebuild:**
```
Layer 1: FROM php:8.2-fpm-alpine       ← CACHED (instant)
Layer 2: WORKDIR /var/www/html         ← CACHED (instant)
Layer 3: RUN docker-php-ext-install    ← CACHED (instant)
Layer 4: COPY composer.json ...        ← CACHED (composer.json unchanged)
Layer 5: RUN composer install          ← CACHED (instant)
Layer 6: COPY . .                      ← REBUILT (code changed)
Layer 7-8: ...                         ← REBUILT
```

Only layers 6+ rebuild. A code change that would take 60+ seconds without cache takes ~1 second with it.

**You add a new package to composer.json, rebuild:**
```
Layer 1-3: CACHED (instant)
Layer 4: COPY composer.json ...        ← REBUILT (composer.json changed)
Layer 5: RUN composer install          ← REBUILT (must reinstall)
Layer 6-8: REBUILT
```

The order of instructions directly controls how much cache is reused.

## PHP-FPM and Nginx Together

PHP-FPM doesn't handle HTTP on its own. It needs Nginx (or Apache) in front.

```
                    ┌─────────────────────┐
HTTP request ──────►│   Nginx container   │
(port 8080)         │   (nginx:alpine)    │
                    └─────────┬───────────┘
                              │ FastCGI (port 9000)
                              ▼
                    ┌─────────────────────┐
                    │   App container     │
                    │ (php:8.2-fpm-alpine)│
                    │    Your DALT.PHP    │
                    └─────────────────────┘
```

Nginx receives the HTTP request, checks if it's a `.php` file, and forwards it to PHP-FPM via the FastCGI protocol on port 9000.

PHP-FPM runs your PHP code and returns the response to Nginx, which sends it back to the browser.

The Nginx config that makes this work — specifically the `fastcgi_pass` directive — is the subject of the second challenge in this phase.

## Your Task

You now have enough knowledge to write the Dockerfile yourself.

Run this command to load an incomplete Dockerfile into your project:

```bash
php artisan challenge:start docker-incomplete-dockerfile
```

The challenge will copy an incomplete `Dockerfile` to your project root. Your job is to complete the three missing parts:

1. Set the working directory
2. Install the PHP extensions needed for PostgreSQL
3. Add the command that starts PHP-FPM

After completing it, verify your solution:

```bash
php artisan challenge:verify
```

## Building and Running Your Image

Once the Dockerfile is complete, try building the image:

```bash
# Build the image, tag it as "dalt-php"
docker build -t dalt-php .

# See your image in the list
docker images

# Run a container from it (just to confirm it starts)
docker run --rm dalt-php php -v
```

If `docker build` succeeds and `php -v` shows PHP 8.2 with your extensions, the Dockerfile is correct.

## Common Dockerfile Mistakes

### Missing WORKDIR
Without `WORKDIR`, files get copied to `/`. Paths inside the container become unpredictable.

### Wrong layer order
```dockerfile
# BAD: code copied before dependencies installed
COPY . .
RUN composer install  # always re-runs, no cache benefit
```
```dockerfile
# GOOD: dependencies before code
COPY composer.json composer.lock ./
RUN composer install
COPY . .
```

### Using php:8.2-cli instead of php:8.2-fpm-alpine
The CLI image has no FPM. Nginx can't forward requests to it.

### Not installing extensions
DALT.PHP requires `pdo` and `pdo_pgsql`. If they're missing, the database connection fails with a cryptic error at runtime.

## Summary

| Instruction | Purpose |
|---|---|
| `FROM` | Base image to build on |
| `WORKDIR` | Default directory for all commands |
| `RUN` | Execute a command during build |
| `COPY` | Copy files from host into image |
| `EXPOSE` | Document which port the container listens on |
| `CMD` | Default command when container starts |

**Build order rules:**
1. Base image
2. System dependencies (extensions, packages) — slow, rarely changes
3. App dependencies (composer install) — medium, changes occasionally
4. App source code — fast, changes constantly

## Next Steps

- **Challenge: docker-incomplete-dockerfile** — complete the Dockerfile
- **Challenge: docker-broken-nginx** — fix the Nginx config so PHP requests are forwarded correctly
- **Lesson 08: Docker Compose** — run the full three-container DALT.PHP stack
