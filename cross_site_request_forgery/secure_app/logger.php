<?php
define('LOG_FILE', __DIR__ . '/app.log');

function app_log($level, $message)
{
    $message = str_replace(array("\r", "\n"), array('\r', '\n'), $message);
    $line = "[" . date("Y-m-d H:i:s") . "] $level $message\n";
    // Write to the log file in the app folder...
    @file_put_contents(LOG_FILE, $line, FILE_APPEND);
    // ...and to the terminal (stderr shows up in the built-in server's console / docker logs).
    @file_put_contents("php://stderr", $line);
}
