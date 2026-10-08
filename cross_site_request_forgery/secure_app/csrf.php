<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . "session.php";

$link->exec("CREATE TABLE IF NOT EXISTS csrf_tokens (
    session_id TEXT PRIMARY KEY,
    token TEXT NOT NULL,
    created_at INTEGER NOT NULL
)");

function secure_session_start()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    session_start();
}

/**
 * This gets the sessions CSRF token or create one if there is no token.
 */
function csrf_token()
{
    global $link;

    $stmt = $link->prepare("SELECT token FROM csrf_tokens WHERE session_id = ?");
    $stmt->execute([session_id()]);
    $token = $stmt->fetchColumn();

    if ($token === false) {
        $token = bin2hex(random_bytes(32));
        $stmt = $link->prepare("REPLACE INTO csrf_tokens (session_id, token, created_at) VALUES (?, ?, ?)");
        $stmt->execute([session_id(), $token, time()]);
    }
    return $token;
}

/**
 * This removes the CSRF token stored for a session
 */
function csrf_clear($session_id)
{
    global $link;

    $stmt = $link->prepare("DELETE FROM csrf_tokens WHERE session_id = ?");
    $stmt->execute([$session_id]);
}

/**
 * This adds a CSRF token to a form as a hiddent input, an attacker cant read the token from the users session, 
 * making a forged form have incorrect values
 */
function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

/**
 * This checks a submitted post, returning true if both tokens match and the origin/refer must point to this host
 */
function csrf_validate()
{
    //synchronizer token check
    global $link;

    $submitted = $_POST['csrf_token'] ?? '';
    $stmt = $link->prepare("SELECT token FROM csrf_tokens WHERE session_id = ?");
    $stmt->execute([session_id()]);
    $stored = $stmt->fetchColumn();
    if ($stored === false || !hash_equals($stored, (string)$submitted)) {
        return false;
    }

    //origin/referer check
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $source = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
    if ($source !== '') {
        $source_host = parse_url($source, PHP_URL_HOST);
        $source_port = parse_url($source, PHP_URL_PORT);
        if ($source_port) {
            $source_host .= ':' . $source_port;
        }
        if (!hash_equals($host, (string)$source_host)) {
            return false;
        }
    }

    return true;
}
?>
