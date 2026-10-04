<?php
const HOST = "0.0.0.0";
const PORT = 8080;

function db_path(): string
{
    return getenv("DB_PATH") ?: __DIR__ . DIRECTORY_SEPARATOR . "bank.sqlite";
}

function connect_db(): PDO
{
    $path = db_path();
    if (!is_file($path)) {
        fwrite(STDERR, "database file not found: $path\n");
        exit(1);
    }

    $conn = new PDO("sqlite:" . $path);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("PRAGMA busy_timeout = 5000");
    $conn->sqliteCreateFunction("md5", static function ($value) {
        return md5((string) $value);
    });
    return $conn;
}
