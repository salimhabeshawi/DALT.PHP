# Challenge: docker-plaintext-secrets

## The Problem

Your `docker-compose.yml` file currently contains this:

```yaml
    environment:
      POSTGRES_USER: postgres
      POSTGRES_PASSWORD: supersecret
```

This is dangerous. If you commit this file, the database password is in version control. If an attacker gets read access to your repository or server, they have your production credentials.

Docker Secrets provide a secure mechanism for passing sensitive information into containers without exposing them as environment variables.

## What You Need to Fix

Load this challenge:

```bash
php artisan challenge:start docker-plaintext-secrets
```

The `docker-compose.yml` file is copied to your project root. Your task is to refactor it to use Docker secrets for the database password.

## What You Must Do

1. **Define the secret**: Add a top-level `secrets` block to the bottom of the `docker-compose.yml` file that defines `db_password` pointing to `./secrets/db_password.txt`.
2. **Mount the secret**: Add a `secrets` list to the `db` service and reference `- db_password`.
3. **Use the secret**: In the `db` service's `environment` block, remove `POSTGRES_PASSWORD: supersecret` and add `POSTGRES_PASSWORD_FILE: /run/secrets/db_password`.

*(Note: In a real project, you would also need to create the `secrets/db_password.txt` file and update your PHP application to read from `/run/secrets/db_password`. For this challenge, we are only validating the syntax of the compose file.)*

## Hints

- The top-level block looks like this:
  ```yaml
  secrets:
    db_password:
      file: ./secrets/db_password.txt
  ```
- The service-level mount looks like this:
  ```yaml
    secrets:
      - db_password
  ```
- Postgres natively supports `POSTGRES_PASSWORD_FILE` and will read the file contents to set the password.

## Verify

```bash
php artisan challenge:verify
```
