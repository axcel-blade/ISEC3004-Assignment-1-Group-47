<?php
const LOG_FILE = __DIR__ . "/app.log";

// Appends "[time] LEVEL message" to the log file. CR/LF are escaped so a
// logged value cannot start a fake log line.
function app_log(string $level, string $message): void
{
    $message = str_replace(["\r", "\n"], ['\r', '\n'], $message);
    $line = "[" . date("Y-m-d H:i:s") . "] $level $message\n";
    echo $line;
    @file_put_contents(LOG_FILE, $line, FILE_APPEND);
}
