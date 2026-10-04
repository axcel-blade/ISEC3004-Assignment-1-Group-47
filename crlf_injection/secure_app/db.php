<?php
function get_account(?string $username = null, ?int $account_id = null): ?array
{
    global $link;
    if ($username !== null) {
        $stmt = $link->prepare("SELECT id, username, password, balance FROM accounts WHERE username = ?");
        $stmt->execute([$username]);
    } else {
        $stmt = $link->prepare("SELECT id, username, password, balance FROM accounts WHERE id = ?");
        $stmt->execute([$account_id]);
    }
    $row = $stmt->fetch(PDO::FETCH_NUM);
    return $row === false ? null : $row;
}

function update_balance(int $account_id, float $delta): void
{
    global $link;
    $stmt = $link->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
    $stmt->execute([$delta, $account_id]);
}
