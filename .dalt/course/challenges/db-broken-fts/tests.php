<?php

/**
 * Broken Full-Text Search — Test Specification
 *
 * Verifies three fixes:
 *   1. ILIKE removed from the controller
 *   2. search_vector column is used in the WHERE clause
 *   3. plainto_tsquery is used for the full-text match
 *   4. @@ operator is used
 */

return [
    'no_ilike' => [
        'type'   => 'file_not_contains',
        'file'   => 'Http/controllers/posts/search.php',
        'search' => 'ILIKE',
        'hint'   => 'Remove ILIKE entirely — replace "WHERE title ILIKE :q" with "WHERE search_vector @@ plainto_tsquery(\'english\', :q)"',
    ],

    'uses_plainto_tsquery' => [
        'type'   => 'file_contains',
        'file'   => 'Http/controllers/posts/search.php',
        'search' => 'plainto_tsquery',
        'hint'   => 'Use plainto_tsquery(\'english\', :q) in the WHERE clause — it converts plain search text into a normalized tsquery',
    ],

    'uses_search_vector' => [
        'type'   => 'file_contains',
        'file'   => 'Http/controllers/posts/search.php',
        'search' => 'search_vector',
        'hint'   => 'Reference the search_vector column in your WHERE clause — the posts table has this as a generated tsvector column',
    ],

    'uses_match_operator' => [
        'type'   => 'file_contains',
        'file'   => 'Http/controllers/posts/search.php',
        'search' => '@@',
        'hint'   => 'Use the @@ operator to match a tsvector against a tsquery — e.g. "WHERE search_vector @@ plainto_tsquery(\'english\', :q)"',
    ],
];
