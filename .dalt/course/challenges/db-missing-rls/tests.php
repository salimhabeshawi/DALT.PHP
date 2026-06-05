<?php

/**
 * Missing RLS Policy — Test Specification
 *
 * Verifies four fixes:
 *   1. ENABLE ROW LEVEL SECURITY is in the migration
 *   2. CREATE POLICY is in the migration using current_setting
 *   3. SET app.tenant_id is called in the controller
 *   4. WHERE tenant_id is removed from the controller query
 */

return [
    'enables_rls' => [
        'type'   => 'file_contains',
        'file'   => 'database/migrations/003_enable_rls.sql',
        'search' => 'ENABLE ROW LEVEL SECURITY',
        'hint'   => 'Add ALTER TABLE posts ENABLE ROW LEVEL SECURITY to the migration file.',
    ],

    'creates_policy' => [
        'type'   => 'file_contains',
        'file'   => 'database/migrations/003_enable_rls.sql',
        'search' => 'CREATE POLICY',
        'hint'   => 'Create a policy in the migration. E.g., CREATE POLICY tenant_isolation ON posts USING ...',
    ],

    'uses_current_setting' => [
        'type'   => 'file_contains',
        'file'   => 'database/migrations/003_enable_rls.sql',
        'search' => 'current_setting',
        'hint'   => 'The policy must use current_setting(\'app.tenant_id\') to check the session context.',
    ],

    'sets_tenant_context' => [
        'type'   => 'file_contains',
        'file'   => 'Http/controllers/tenant/posts.php',
        'search' => 'SET app.tenant_id',
        'hint'   => 'Execute a query to set the session context before fetching posts: $db->query("SET app.tenant_id = :id", ["id" => $tenantId]);',
    ],
    
    'removes_manual_where' => [
        'type'   => 'file_not_contains',
        'file'   => 'Http/controllers/tenant/posts.php',
        'search' => 'WHERE tenant_id =',
        'hint'   => 'Remove the "WHERE tenant_id = :id" clause from the SELECT query. Let Postgres handle the filtering via RLS.',
    ],
];
