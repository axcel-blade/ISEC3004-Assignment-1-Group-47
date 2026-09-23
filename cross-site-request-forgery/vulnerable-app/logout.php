<?php
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
