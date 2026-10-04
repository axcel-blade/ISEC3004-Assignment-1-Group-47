<?php
require_once "csrf.php";
secure_session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: welcome.php");
} else {
    header("location: login.php");
}
exit;
?>
