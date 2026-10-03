<?php
//vulnerabilty
//SameSite=None lets the browser send the session cookie on cross site requests,
//making it a CSRF vulnerability.
session_set_cookie_params(["samesite" => "None", "secure" => true]);
session_start();
require_once "logger.php";

if (isset($_SESSION["username"])) {
    app_log("INFO", "Logout: " . $_SESSION["username"]);
}

$_SESSION = array();
session_destroy();

header("location: login.php");
exit;
?>
