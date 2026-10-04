<?php
define('LOG_FILE', __DIR__ . '/app.log');

function app_log($level, $message)
{
    $message = str_replace(array("\r", "\n"), array('\r', '\n'), $message);
    $line = "[" . date("Y-m-d H:i:s") . "] $level $message\n";
    @file_put_contents(LOG_FILE, $line, FILE_APPEND);
}
