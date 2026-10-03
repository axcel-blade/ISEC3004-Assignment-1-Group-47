<?php
//vulnerabilty
//SameSite=None lets the browser send the session cookie on cross site requests,
//making it a CSRF vulnerability.
session_set_cookie_params(["samesite" => "None", "secure" => true]);
session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: welcome.php");
} else {
    header("location: login.php");
}
exit;
?>
