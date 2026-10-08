<?php
require_once "csrf.php";
secure_session_start();
require_once "logger.php";

if (isset($_SESSION["username"])) {
    app_log("INFO", "Logout: " . $_SESSION["username"]);
}

csrf_clear(session_id());
$_SESSION = array();
session_destroy();

header("location: login.php");
exit;
?>
