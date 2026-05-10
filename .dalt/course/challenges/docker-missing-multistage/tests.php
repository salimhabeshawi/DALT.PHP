<?php

/**
 * Docker Missing Multi-Stage Build — Test Specification
 *
 * Verifies three fixes:
 *   1. A builder stage using composer:2 is added
 *   2. Files are copied from the builder using COPY --from=builder
 *   3. A HEALTHCHECK instruction is added
 *   4. The composer binary is NOT copied directly into the runtime image
 *   5. The AS builder alias exists
 */

return [
    'has_builder_stage' => [
        'type'   => 'file_contains',
        'file'   => 'Dockerfile',
        'search' => 'AS builder',
        'hint'   => 'Add "FROM composer:2 AS builder" as the first stage — the alias "AS builder" is required for COPY --from=builder to work',
    ],

    'uses_composer_image' => [
        'type'   => 'file_contains',
        'file'   => 'Dockerfile',
        'search' => 'FROM composer:2',
        'hint'   => 'Use "FROM composer:2 AS builder" as your first stage — composer:2 has PHP and Composer pre-installed',
    ],

    'copies_from_builder' => [
        'type'   => 'file_contains',
        'file'   => 'Dockerfile',
        'search' => 'COPY --from=builder',
        'hint'   => 'In the runtime stage, use "COPY --from=builder /app/vendor ./vendor" to pull only the vendor directory — not the full build image',
    ],

    'has_healthcheck' => [
        'type'   => 'file_contains',
        'file'   => 'Dockerfile',
        'search' => 'HEALTHCHECK',
        'hint'   => 'Add a HEALTHCHECK instruction before CMD — e.g. "HEALTHCHECK --interval=30s --timeout=5s CMD php-fpm -t || exit 1"',
    ],

    'no_composer_binary_copy' => [
        'type'   => 'file_not_contains',
        'file'   => 'Dockerfile',
        'search' => 'COPY --from=composer:2 /usr/bin/composer /usr/bin/composer',
        'hint'   => 'Remove "COPY --from=composer:2 /usr/bin/composer /usr/bin/composer" — this puts the Composer binary into the runtime image, which defeats the purpose of a multi-stage build',
    ],
];
