<?php

/**
 * Slow Queries — Test Specification
 *
 * Verifies two fixes:
 *   1. CREATE INDEX on posts(user_id) exists
 *   2. CREATE INDEX on posts(status) exists
 */

return [
    'has_user_id_index' => [
        'type'   => 'file_contains',
        'file'   => 'database/migrations/004_add_indexes.sql',
        'search' => 'posts(user_id)',
        'hint'   => 'Add a CREATE INDEX statement for the user_id column on the posts table.',
    ],

    'has_status_index' => [
        'type'   => 'file_contains',
        'file'   => 'database/migrations/004_add_indexes.sql',
        'search' => 'posts(status)',
        'hint'   => 'Add a CREATE INDEX statement for the status column on the posts table.',
    ],
    
    'uses_create_index' => [
        'type'   => 'file_contains',
        'file'   => 'database/migrations/004_add_indexes.sql',
        'search' => 'CREATE INDEX',
        'hint'   => 'You must use the CREATE INDEX syntax.',
    ]
];
