<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_email = $_POST["email"];

    $sql = "UPDATE users SET email = '$new_email' WHERE id = " . $_SESSION["id"];
    mysqli_query($link, $sql);

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
