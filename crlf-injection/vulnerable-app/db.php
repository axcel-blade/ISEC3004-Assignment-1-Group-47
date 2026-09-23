<?php
function get_account(?string $username = null, ?int $account_id = null): ?array
{
    global $link;
    if ($username !== null) {
        $stmt = $link->prepare("SELECT id, username, password, balance FROM accounts WHERE username = ?");
        $stmt->bind_param("s", $username);
    } else {
        $stmt = $link->prepare("SELECT id, username, password, balance FROM accounts WHERE id = ?");
        $stmt->bind_param("i", $account_id);
    }
    $stmt->execute();
    $row = $stmt->get_result()->fetch_row();
    $stmt->close();
    return $row === null ? null : $row;
}

function update_balance(int $account_id, float $delta): void
{
    global $link;
    $stmt = $link->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
    $stmt->bind_param("di", $delta, $account_id);
    $stmt->execute();
    $stmt->close();
}
