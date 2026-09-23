<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/http.php";
require_once __DIR__ . "/views.php";

$sessions = [];
$link = null;

function handle($conn): void
{
    global $sessions;

    $raw = fread($conn, 8192);
    if ($raw === false || $raw === "") {
        return;
    }

    [$method, $raw_path, $headers, $body] = parse_request($raw);
    $parsed = parse_url($raw_path);
    $path = $parsed["path"] ?? "/";
    $query = [];
    if (isset($parsed["query"])) {
        parse_str($parsed["query"], $query);
    }
    $form = [];
    if ($method === "POST") {
        parse_str($body, $form);
    }

    $session_id = get_session($headers);

    if ($path === "/style.css" && $method === "GET") {
        fwrite($conn, safe_header_bytes("HTTP/1.1 200 OK", [["Content-Type", "text/css"]]) . STYLESHEET);
        return;
    }

    if ($path === "/" && $method === "GET") {
        if ($session_id) {
            fwrite($conn, safe_header_bytes("HTTP/1.1 302 Found", [["Location", "/dashboard"]]));
            return;
        }
        $html = sprintf(LOGIN_PAGE, "");
        fwrite($conn, safe_header_bytes("HTTP/1.1 200 OK", [["Content-Type", "text/html"]]) . $html);
        return;
    }

    if ($path === "/" && $method === "POST") {
        $username = $form["username"] ?? "";
        $password = $form["password"] ?? "";
        $row = get_account(username: $username);
        if ($row && $row[2] === md5($password)) {
            $sid = bin2hex(random_bytes(16));
            $sessions[$sid] = (int) $row[0];
            fwrite($conn, safe_header_bytes("HTTP/1.1 302 Found", [
                ["Location", "/dashboard"],
                ["Set-Cookie", "session=$sid; HttpOnly"],
            ]));
        } else {
            $html = sprintf(LOGIN_PAGE, "Invalid username or password.");
            fwrite($conn, safe_header_bytes("HTTP/1.1 200 OK", [["Content-Type", "text/html"]]) . $html);
        }
        return;
    }

    if ($path === "/dashboard" && $method === "GET") {
        if (!$session_id) {
            fwrite($conn, safe_header_bytes("HTTP/1.1 302 Found", [["Location", "/"]]));
            return;
        }
        $account = get_account(account_id: $sessions[$session_id]);
        $html = sprintf(DASHBOARD_PAGE, $account[1], (float) $account[3]);
        fwrite($conn, safe_header_bytes("HTTP/1.1 200 OK", [["Content-Type", "text/html"]]) . $html);
        return;
    }

    if ($path === "/transfer" && $method === "POST") {
        if (!$session_id) {
            fwrite($conn, safe_header_bytes("HTTP/1.1 302 Found", [["Location", "/"]]));
            return;
        }

        $sender_id = $sessions[$session_id];
        $recipient_id = $form["recipient_id"] ?? "";
        $amount = $form["amount"] ?? "0";
        $next_url = $form["next"] ?? "/dashboard";
        $next_url = urldecode($next_url);

        if (is_numeric($amount) && is_numeric($recipient_id)) {
            $amount_val = (float) $amount;
            $recipient_id_val = (int) $recipient_id;
            if ($amount_val > 0) {
                update_balance($sender_id, -$amount_val);
                update_balance($recipient_id_val, $amount_val);
            }
        }

        // VULNERABLE: 'next' is attacker-controlled and written directly into the
        // raw response bytes with no CRLF filtering, unlike PHP's header() or a
        // framework Response, which both reject embedded \r\n.
        $response =
            "HTTP/1.1 302 Found\r\n" .
            "Location: " . $next_url . "\r\n" .
            "\r\n";
        fwrite($conn, $response);
        return;
    }

    if ($path === "/logout" && $method === "GET") {
        if ($session_id) {
            unset($sessions[$session_id]);
        }
        fwrite($conn, safe_header_bytes("HTTP/1.1 302 Found", [
            ["Location", "/"],
            ["Set-Cookie", "session=; Max-Age=0"],
        ]));
        return;
    }

    fwrite($conn, safe_header_bytes("HTTP/1.1 404 Not Found", [["Content-Type", "text/plain"]]) . "Not Found");
}

function main(): void
{
    global $link;
    $link = connect_db();

    $server = stream_socket_server("tcp://" . HOST . ":" . PORT, $errno, $errstr);
    if ($server === false) {
        fwrite(STDERR, "bind failed: $errstr ($errno)\n");
        exit(1);
    }
    echo "SecureBank demo listening on " . HOST . ":" . PORT . "\n";

    while (true) {
        $conn = @stream_socket_accept($server, -1);
        if ($conn === false) {
            continue;
        }
        try {
            handle($conn);
        } catch (Throwable $e) {
            echo "error: " . $e->getMessage() . "\n";
        } finally {
            fclose($conn);
        }
    }
}

main();
