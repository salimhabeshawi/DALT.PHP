<?php

/**
 * Docker Plaintext Secrets — Test Specification
 *
 * Verifies four fixes:
 *   1. Hardcoded password is removed
 *   2. Top-level secrets block exists
 *   3. POSTGRES_PASSWORD_FILE is used
 *   4. File mounts to /run/secrets/
 */

return [
    'no_hardcoded_password' => [
        'type'   => 'file_not_contains',
        'file'   => 'docker-compose.yml',
        'search' => 'POSTGRES_PASSWORD: supersecret',
        'hint'   => 'Remove the hardcoded POSTGRES_PASSWORD: supersecret from the environment block',
    ],

    'has_secrets_block' => [
        'type'   => 'file_contains',
        'file'   => 'docker-compose.yml',
        'search' => 'secrets:',
        'hint'   => 'Add a top-level "secrets:" block and a service-level "secrets:" list',
    ],

    'uses_password_file_env' => [
        'type'   => 'file_contains',
        'file'   => 'docker-compose.yml',
        'search' => 'POSTGRES_PASSWORD_FILE',
        'hint'   => 'Use POSTGRES_PASSWORD_FILE in the environment block to tell Postgres to read from a file',
    ],

    'points_to_run_secrets' => [
        'type'   => 'file_contains',
        'file'   => 'docker-compose.yml',
        'search' => '/run/secrets/',
        'hint'   => 'The POSTGRES_PASSWORD_FILE value should be a path inside /run/secrets/ (e.g., /run/secrets/db_password)',
    ],
];
