<?php

/**
 * Missing Pagination Challenge — Test Specification
 *
 * Verifies that the users list endpoint uses LIMIT/OFFSET
 * with named parameters and returns a JSON response.
 */

return [
    'uses_limit' => [
        'type'   => 'file_contains',
        'file'   => 'Http/controllers/db/users/index.php',
        'search' => 'LIMIT',
        'hint'   => 'Add LIMIT to the SQL query — use LIMIT :limit and pass the value in the params array',
    ],

    'uses_offset' => [
        'type'   => 'file_contains',
        'file'   => 'Http/controllers/db/users/index.php',
        'search' => 'OFFSET',
        'hint'   => 'Add OFFSET to the SQL query — use OFFSET :offset and calculate it as ($page - 1) * $limit',
    ],

    'uses_limit_param' => [
        'type'   => 'file_contains',
        'file'   => 'Http/controllers/db/users/index.php',
        'search' => ':limit',
        'hint'   => 'Use :limit as a named parameter instead of putting the number directly in the SQL string',
    ],

    'uses_offset_param' => [
        'type'   => 'file_contains',
        'file'   => 'Http/controllers/db/users/index.php',
        'search' => ':offset',
        'hint'   => 'Use :offset as a named parameter instead of putting the number directly in the SQL string',
    ],

    'uses_json_encode' => [
        'type'   => 'file_contains',
        'file'   => 'Http/controllers/db/users/index.php',
        'search' => 'json_encode',
        'hint'   => 'Return the response as JSON using json_encode — wrap data, page, and limit in an array',
    ],
];
