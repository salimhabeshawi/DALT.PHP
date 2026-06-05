<?php

$db = \Core\App::resolve(\Core\Database::class);

$q = trim($_GET['q'] ?? '');

if ($q === '') {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Query parameter q is required']);
    exit;
}

// BUG: ILIKE does a sequential scan and cannot use any index.
// BUG: The % concatenation is required for ILIKE but breaks relevance ranking.
// BUG: ORDER BY created_at ignores match quality — most relevant post might be buried.
$posts = $db->query(
    'SELECT id, title, created_at FROM posts WHERE title ILIKE :q ORDER BY created_at DESC',
    ['q' => '%' . $q . '%']
)->get();

header('Content-Type: application/json');
echo json_encode(['query' => $q, 'results' => $posts]);
