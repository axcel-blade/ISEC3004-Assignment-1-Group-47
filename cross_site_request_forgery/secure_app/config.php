<?php
define('DB_PATH', getenv('DB_PATH') ?: __DIR__ . DIRECTORY_SEPARATOR . 'database.sqlite');

try {
    $link = new PDO('sqlite:' . DB_PATH);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $link->exec('PRAGMA busy_timeout = 5000');
    $link->sqliteCreateFunction('md5', static function ($value) {
        return md5((string) $value);
    });
} catch (PDOException $e) {
    die('ERROR: Could not connect. ' . $e->getMessage());
}
?>
