<?php

/**
 * Broken First Queries Challenge - Test Specification
 *
 * Verifies three fixes:
 *   1. SQL injection removed from the index controller (parameterized query used)
 *   2. Wrong column name fixed in the show controller
 *   3. json_encode used instead of var_dump/echo $array in the show controller
 */

return [
    'no_sql_injection_in_index' => [
        'type' => 'file_not_contains',
        'file' => 'Http/controllers/users/index.php',
        'search' => '{$search}',
        'hint' => 'Remove the interpolated $search from inside the SQL string — use a :search named parameter and pass it in the params array instead',
    ],

    'uses_parameter_binding' => [
        'type' => 'file_contains',
        'file' => 'Http/controllers/users/index.php',
        'search' => ':search',
        'hint' => 'Use :search as a named parameter and pass [\'search\' => "%$search%"] to the query() call',
    ],

    'correct_column_in_show' => [
        'type' => 'file_contains',
        'file' => 'Http/controllers/users/show.php',
        'search' => 'WHERE id = :id',
        'hint' => 'Fix the WHERE clause in users/show.php — the column is "id", not "user_id"',
    ],

    'no_wrong_column_in_show' => [
        'type' => 'file_not_contains',
        'file' => 'Http/controllers/users/show.php',
        'search' => 'WHERE user_id',
        'hint' => 'Remove "WHERE user_id = :id" — the users table has no user_id column',
    ],

    'uses_json_encode_in_show' => [
        'type' => 'file_contains',
        'file' => 'Http/controllers/users/show.php',
        'search' => 'json_encode',
        'hint' => 'Replace var_dump() and echo $user with json_encode($user) to send a valid JSON response',
    ],

    'no_var_dump_in_show' => [
        'type' => 'file_not_contains',
        'file' => 'Http/controllers/users/show.php',
        'search' => 'var_dump',
        'hint' => 'Remove var_dump() — it produces PHP debug output, not JSON',
    ],
];
