# Challenge: Broken Transaction

## Difficulty: Medium — 2 bugs in 1 file

## What This Challenge Is

The `POST /db/transfer` endpoint moves credits from one user to another. It correctly opens a transaction with `beginTransaction()` and commits on success, but there is no `try/catch` and no `rollBack()`. If the second `UPDATE` fails (invalid user id, insufficient credits, database error), the first UPDATE is left permanently committed — credits were deducted but never arrived.

Load the broken file:

```bash
php artisan challenge:start db-broken-transaction
```

This adds:
- `app/Http/controllers/db/transfer.php` — `POST /db/transfer`

## The Two Bugs

### Bug 1 — No try/catch around the transaction body

```php
// BROKEN — if either UPDATE throws, the exception propagates uncaught
// and the transaction is left open with partial state
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
```

**Fix:** Wrap everything between `beginTransaction()` and `commit()` in a `try` block.

### Bug 2 — No rollBack() in the error path

Without a `catch` block that calls `$pdo->rollBack()`, a failed second query leaves the first update committed. The sender's credits are gone, but the receiver never got them.

```php
// CORRECT
try {
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
} catch (\Exception $e) {
    $pdo->rollBack();

    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Transfer failed']);
}
```

The `catch` block calls `$pdo->rollBack()`. This undoes the first UPDATE as if it never happened — both accounts stay consistent.

## File to Edit

- `app/Http/controllers/db/transfer.php`

## Verify Your Solution

```bash
php artisan challenge:verify
```

The verifier checks:
- `beginTransaction` is present (keep it)
- `commit` is present (keep it)
- `rollBack` is present (add it)
- `catch` is present (add the try/catch)

## Testing Manually

With `php artisan serve` running:

```bash
# Transfer 10 credits from user 1 to user 2
curl -X POST http://localhost:8000/db/transfer \
  -d "from_id=1&to_id=2&amount=10"
```

To test the error path, send an invalid `to_id` (one that doesn't exist) — the second UPDATE should fail, and you should see the error JSON response with no net change to user 1's balance.

## Hints

- `$pdo->rollBack()` undoes everything since `$pdo->beginTransaction()` — both UPDATEs vanish as if neither ran
- The `catch` block must call `rollBack()` before sending the error response — order matters
- `beginTransaction()` and `commit()` must stay in the code — they're not the bugs
- Without `rollBack()` in `catch`, an open failed transaction may leave your connection in an error state for subsequent queries
