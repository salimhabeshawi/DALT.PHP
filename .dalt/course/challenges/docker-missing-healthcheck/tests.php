<?php

/**
 * Docker Missing Health Check — Test Specification
 *
 * Verifies three fixes:
 *   1. healthcheck block is present
 *   2. pg_isready is used as the test
 *   3. condition: service_healthy is used in depends_on
 */

return [
    'has_healthcheck' => [
        'type'   => 'file_contains',
        'file'   => 'docker-compose.yml',
        'search' => 'healthcheck:',
        'hint'   => 'Add a "healthcheck:" block to the db service',
    ],

    'uses_pg_isready' => [
        'type'   => 'file_contains',
        'file'   => 'docker-compose.yml',
        'search' => 'pg_isready',
        'hint'   => 'Use pg_isready in the healthcheck test command (e.g. test: ["CMD-SHELL", "pg_isready -U postgres"])',
    ],

    'waits_for_health' => [
        'type'   => 'file_contains',
        'file'   => 'docker-compose.yml',
        'search' => 'service_healthy',
        'hint'   => 'Update depends_on to use object syntax and add "condition: service_healthy" for the db service',
    ],
];
