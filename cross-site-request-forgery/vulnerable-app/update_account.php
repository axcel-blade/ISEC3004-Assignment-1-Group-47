<?php
// VULNERABLE: session cookie is explicitly set to SameSite=None so the browser
// sends it on cross-site requests. This reproduces classic CSRF behaviour;
// without this, modern browsers default to SameSite=Lax and would block the
// forged request at the browser layer, masking the application-level flaw.
session_set_cookie_params(["samesite" => "None", "secure" => true]);
session_start();
require_once "config.php";
require_once "logger.php";

/*
 * VULNERABLE: Cross-Site Request Forgery (CSRF)
 *
 * This endpoint performs a state-changing action (updating the account email)
 * but authenticates the request using ONLY the session cookie. It never verifies
 * that the request actually came from our own site:
 *   - no anti-CSRF token is required,
 *   - the Origin/Referer header is not checked,
 *   - the session cookie has no SameSite restriction.
 *
 * Because browsers automatically attach the session cookie to ANY request to
 * this domain, a malicious page on another origin can auto-submit a hidden form
 * (see ../exploit/attacker.html) and silently change the victim's email while
 * they are logged in. That stolen email can then be used for a password reset,
 * leading to full account takeover.
 */
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // RISK: the POST body is trusted with no CSRF token and no origin check,
    // so a forged cross-site request is processed exactly like a legitimate one.
    $new_email = $_POST["email"];

    $sql = "UPDATE users SET email = '$new_email' WHERE id = " . $_SESSION["id"];
    mysqli_query($link, $sql);

    $referer = $_SERVER["HTTP_REFERER"] ?? "none";
    app_log("INFO", "Email changed: user " . $_SESSION["username"] . " -> $new_email (referer: $referer)");

    header("location: update_account.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Account</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <h2>Update Account Settings</h2>
        <form action="update_account.php" method="post">
            <div class="form-group">
                <label>New Email</label>
                <input type="email" name="email" class="form-control">
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Update">
            </div>
        </form>
        <p><a href="welcome.php">&larr; Back</a></p>
    </div>
</body>
</html>
