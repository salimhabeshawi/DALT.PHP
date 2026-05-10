FROM php:8.2-fpm-alpine

# TODO: Set the working directory to /var/www/html
# Every subsequent command (COPY, RUN, CMD) will run from this path.

# TODO: Install the PHP extensions needed for PostgreSQL database access.
# You need two extensions: the base PDO abstraction layer and its Postgres driver.
# Hint: see the lesson — the helper command is documented there.

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction

COPY . .

EXPOSE 9000

# TODO: Add the CMD instruction to start php-fpm
# Use the array ("exec") form, not the string form.
