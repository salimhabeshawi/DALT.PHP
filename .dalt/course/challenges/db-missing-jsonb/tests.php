<?php

/**
 * Missing JSONB Metadata — Test Specification
 *
 * Verifies two fixes:
 *   1. metadata is included in the INSERT query
 *   2. metadata is included in the SELECT query
 */

return [
    'inserts_metadata' => [
        'type'   => 'file_contains',
        'file'   => 'Http/controllers/posts/store.php',
        'search' => 'metadata',
        'hint'   => 'Add the metadata column to the INSERT INTO statement in store.php',
    ],

    'binds_metadata' => [
        'type'   => 'file_contains',
        'file'   => 'Http/controllers/posts/store.php',
        'search' => ':metadata',
        'hint'   => 'Pass the :metadata parameter in the $db->query() call in store.php',
    ],

    'selects_metadata' => [
        'type'   => 'file_contains',
        'file'   => 'Http/controllers/posts/index.php',
        'search' => 'metadata',
        'hint'   => 'Add the metadata column to the SELECT statement in index.php',
    ],
];
