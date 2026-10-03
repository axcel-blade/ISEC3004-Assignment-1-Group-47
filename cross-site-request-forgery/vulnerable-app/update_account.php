<?php
//vulnerabilty
//SameSite=None lets the browser send the session cookie on cross site requests,
//making it a CSRF vulnerability.
session_set_cookie_params(["samesite" => "None", "secure" => true]);
session_start();
require_once "config.php";
require_once "logger.php";

/**
 * Vulnerabilty
 * 
 * This is an endpoint that updates the email using session cookie to authenticate the request,
 * but it doesnt check if the request came from the same site.
 * 
 * There is no CSRF token, origin/refer headers are not checked, and the session cookie 
 * has no SameSite restriction.
 * 
 * the browser includes the session cookie with the cross site request, so a attacker can submit a 
 * hidden form from another site when the user is logged in
 * 
 */
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //there is no CSRF token or origin check for this POST, a forged cross site request 
    //is treated as a normal request
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
