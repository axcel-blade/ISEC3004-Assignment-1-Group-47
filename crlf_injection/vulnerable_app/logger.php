<?php
const LOG_FILE = __DIR__ . "/app.log";

function app_log(string $level, string $message): void
{
    $message = str_replace(["\r", "\n"], ['\r', '\n'], $message);
    $line = "[" . date("Y-m-d H:i:s") . "] $level $message\n";
    echo $line;
    @file_put_contents(LOG_FILE, $line, FILE_APPEND);
}
