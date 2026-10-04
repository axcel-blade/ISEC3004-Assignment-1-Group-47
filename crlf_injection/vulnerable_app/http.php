<?php
function parse_request(string $raw): array
{
    [$head, $body] = array_pad(explode("\r\n\r\n", $raw, 2), 2, "");
    $lines = explode("\r\n", $head);
    $request_line = $lines[0];
    $parts = explode(" ", $request_line);
    $method = $parts[0] ?? "";
    $raw_path = $parts[1] ?? "";
    $headers = [];
    foreach (array_slice($lines, 1) as $line) {
        if (str_contains($line, ":")) {
            [$k, $v] = explode(":", $line, 2);
            $headers[strtolower(trim($k))] = trim($v);
        }
    }
    return [$method, $raw_path, $headers, $body];
}

function get_session(array $headers): ?string
{
    global $sessions;
    $cookie = $headers["cookie"] ?? "";
    foreach (explode(";", $cookie) as $part) {
        if (str_contains($part, "=")) {
            [$k, $v] = explode("=", trim($part), 2);
            if ($k === "session" && isset($sessions[$v])) {
                return $v;
            }
        }
    }
    return null;
}

function safe_header_bytes(string $status_line, array $header_lines): string
{
    $out = $status_line . "\r\n";
    foreach ($header_lines as [$name, $value]) {
        $out .= "$name: $value\r\n";
    }
    $out .= "\r\n";
    return $out;
}
