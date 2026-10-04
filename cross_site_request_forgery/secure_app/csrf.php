<?php
/**
 * This uses CSRF protection by using session tokens and a hardened session cookie.
 * The vulerable app accepts any authenticated POST, making another site trick 
 * a user's browser into sending requests on their behalf.
 * 
 * This secure app uses three checks, SameSite=Strict keeps the session cookies out of cross site requests,
 * each session has a CSRF token included in every form and checked when a POST changes data,
 * and origin/refer checks to provide another layer of protection.
 * 
 */

/**
 * This starts a session with a stricter cookie setting
 */
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
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
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
    $submitted = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submitted)) {
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
