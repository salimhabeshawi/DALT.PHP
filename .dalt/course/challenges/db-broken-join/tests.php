<?php

/**
 * Broken Join Challenge — Test Specification
 *
 * Verifies two fixes:
 *   1. INNER JOIN changed to LEFT JOIN
 *   2. ON clause fixed from posts.id = users.id to posts.user_id = users.id
 */

return [
    'uses_left_join' => [
        'type'   => 'file_contains',
        'file'   => 'Http/controllers/db/posts/index.php',
        'search' => 'LEFT JOIN',
        'hint'   => 'Change INNER JOIN to LEFT JOIN — INNER JOIN drops users with no posts from the results',
    ],

    'no_inner_join' => [
        'type'   => 'file_not_contains',
        'file'   => 'Http/controllers/db/posts/index.php',
        'search' => 'INNER JOIN',
        'hint'   => 'Remove INNER JOIN — replace it with LEFT JOIN users ON posts.user_id = users.id',
    ],

    'correct_on_clause' => [
        'type'   => 'file_contains',
        'file'   => 'Http/controllers/db/posts/index.php',
        'search' => 'posts.user_id = users.id',
        'hint'   => 'Fix the ON clause: it should be posts.user_id = users.id, not posts.id = users.id',
    ],

    'no_wrong_on_clause' => [
        'type'   => 'file_not_contains',
        'file'   => 'Http/controllers/db/posts/index.php',
        'search' => 'posts.id = users.id',
        'hint'   => 'Remove "posts.id = users.id" — posts.id is the post\'s own primary key, not the foreign key to users',
    ],
];
