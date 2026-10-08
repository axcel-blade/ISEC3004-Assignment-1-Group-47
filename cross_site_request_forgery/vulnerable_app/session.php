<?php
/**
 * Stores PHP sessions in the SQLite database instead of the default
 * in-memory/temporary file storage, so sessions survive server restarts.
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . "config.php";

$link->exec("CREATE TABLE IF NOT EXISTS sessions (
    id TEXT PRIMARY KEY,
    data TEXT NOT NULL,
    last_access INTEGER NOT NULL
)");

class DatabaseSessionHandler implements SessionHandlerInterface
{
    private $link;

    public function __construct(PDO $link)
    {
        $this->link = $link;
    }

    public function open($path, $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id): string|false
    {
        $stmt = $this->link->prepare("SELECT data FROM sessions WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetchColumn();
        return $data === false ? "" : $data;
    }

    public function write($id, $data): bool
    {
        $stmt = $this->link->prepare("REPLACE INTO sessions (id, data, last_access) VALUES (?, ?, ?)");
        return $stmt->execute([$id, $data, time()]);
    }

    public function destroy($id): bool
    {
        $stmt = $this->link->prepare("DELETE FROM sessions WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function gc($max_lifetime): int|false
    {
        $stmt = $this->link->prepare("DELETE FROM sessions WHERE last_access < ?");
        $stmt->execute([time() - $max_lifetime]);
        return $stmt->rowCount();
    }
}

session_set_save_handler(new DatabaseSessionHandler($link), true);
?>
