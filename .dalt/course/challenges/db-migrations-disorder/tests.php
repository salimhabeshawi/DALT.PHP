<?php

/**
 * Migration Disorder — Test Specification
 *
 * Verifies three fixes:
 *   1. 001_create_users_table.sql creates users
 *   2. 002_create_posts_table.sql creates posts
 *   3. 002_create_posts_table.sql uses BIGSERIAL, not AUTOINCREMENT
 */

return [
    '001_is_users' => [
        'type'   => 'file_contains',
        'file'   => 'database/migrations/001_create_users_table.sql',
        'search' => 'CREATE TABLE IF NOT EXISTS users',
        'hint'   => 'Rename the files so 001_ is the users table. The tests specifically look for 001_create_users_table.sql',
    ],

    '002_is_posts' => [
        'type'   => 'file_contains',
        'file'   => 'database/migrations/002_create_posts_table.sql',
        'search' => 'CREATE TABLE IF NOT EXISTS posts',
        'hint'   => 'Rename the files so 002_ is the posts table. The tests specifically look for 002_create_posts_table.sql',
    ],

    'uses_bigserial' => [
        'type'   => 'file_contains',
        'file'   => 'database/migrations/002_create_posts_table.sql',
        'search' => 'BIGSERIAL',
        'hint'   => 'Change the posts id column to use BIGSERIAL instead of INTEGER PRIMARY KEY AUTOINCREMENT',
    ],

    'no_autoincrement' => [
        'type'   => 'file_not_contains',
        'file'   => 'database/migrations/002_create_posts_table.sql',
        'search' => 'AUTOINCREMENT',
        'hint'   => 'Remove the SQLite AUTOINCREMENT keyword from the posts migration',
    ],
];
