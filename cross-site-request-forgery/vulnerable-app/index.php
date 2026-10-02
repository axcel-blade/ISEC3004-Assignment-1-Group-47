<?php
// VULNERABLE: session cookie is explicitly set to SameSite=None so the browser
// sends it on cross-site requests. This reproduces classic CSRF behaviour;
// without this, modern browsers default to SameSite=Lax and would block the
// forged request at the browser layer, masking the application-level flaw.
session_set_cookie_params(["samesite" => "None", "secure" => true]);
session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: welcome.php");
} else {
    header("location: login.php");
}
exit;
?>
