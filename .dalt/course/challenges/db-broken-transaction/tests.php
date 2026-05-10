<?php

/**
 * Broken Transaction Challenge — Test Specification
 *
 * Verifies that the transfer endpoint wraps its UPDATEs
 * in a try/catch with rollBack on failure.
 */

return [
    'uses_begin_transaction' => [
        'type'   => 'file_contains',
        'file'   => 'Http/controllers/db/transfer.php',
        'search' => 'beginTransaction',
        'hint'   => 'Keep the $pdo->beginTransaction() call — it must come before the first UPDATE',
    ],

    'uses_commit' => [
        'type'   => 'file_contains',
        'file'   => 'Http/controllers/db/transfer.php',
        'search' => 'commit',
        'hint'   => 'Keep the $pdo->commit() call — it applies both UPDATEs atomically on success',
    ],

    'uses_rollback' => [
        'type'   => 'file_contains',
        'file'   => 'Http/controllers/db/transfer.php',
        'search' => 'rollBack',
        'hint'   => 'Add $pdo->rollBack() in the catch block — without it a failed second UPDATE leaves the first committed permanently',
    ],

    'uses_catch' => [
        'type'   => 'file_contains',
        'file'   => 'Http/controllers/db/transfer.php',
        'search' => 'catch',
        'hint'   => 'Wrap both UPDATEs in a try { ... } catch (\Exception $e) { $pdo->rollBack(); } block',
    ],
];
