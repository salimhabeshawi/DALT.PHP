<?php

$db  = \Core\App::resolve(\Core\Database::class);
$pdo = $db->getConnection();

$fromId = $_POST['from_id'] ?? null;
$toId   = $_POST['to_id']   ?? null;
$amount = (int)($_POST['amount'] ?? 0);

// BUG: No try/catch and no rollBack.
// If the second UPDATE fails, the first is already committed
// and credits vanish permanently.
$pdo->beginTransaction();

$db->query(
    'UPDATE users SET credits = credits - :amount WHERE id = :id',
    ['amount' => $amount, 'id' => $fromId]
);

$db->query(
    'UPDATE users SET credits = credits + :amount WHERE id = :id',
    ['amount' => $amount, 'id' => $toId]
);

$pdo->commit();

header('Content-Type: application/json');
echo json_encode(['success' => true]);
