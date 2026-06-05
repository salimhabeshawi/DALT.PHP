<?php

$db = \Core\App::resolve(\Core\Database::class);

$tenantId = (int)$router->getParam('tenant_id');

// BUG: We are missing the SET app.tenant_id command here to inform Postgres of the current tenant context.

// BUG: We are manually filtering by tenant_id in PHP.
// With RLS properly configured, this WHERE clause shouldn't be necessary.
$posts = $db->query(
    'SELECT * FROM posts WHERE tenant_id = :id ORDER BY created_at DESC',
    ['id' => $tenantId]
)->get();

header('Content-Type: application/json');
echo json_encode(['data' => $posts]);
