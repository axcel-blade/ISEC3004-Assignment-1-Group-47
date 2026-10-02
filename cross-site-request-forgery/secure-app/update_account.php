<?php
require_once "csrf.php";
secure_session_start();
require_once "config.php";
require_once "logger.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //rejects POST requests if they dont have a valid CSRF token
    if (!csrf_validate()) {
        $referer = $_SERVER["HTTP_REFERER"] ?? "none";
        $origin  = $_SERVER["HTTP_ORIGIN"] ?? "none";
        app_log("ALERT", "CSRF blocked: user " . ($_SESSION["username"] ?? "?") .
            " (origin: $origin, referer: $referer)");
        http_response_code(403);
        $error = "Request rejected: invalid or missing CSRF token.";
    } else {
        $new_email = $_POST["email"];

        //parameterised query
        $stmt = mysqli_prepare($link, "UPDATE users SET email = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $new_email, $_SESSION["id"]);
        mysqli_stmt_execute($stmt);

        app_log("INFO", "Email changed: user " . $_SESSION["username"] . " -> $new_email");

        header("location: update_account.php");
        exit;
    }
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
        <span class="help-block"><?php echo htmlspecialchars($error); ?></span>
        <form action="update_account.php" method="post">
            <?php echo csrf_field(); //embeds the hidden token the attacker cannot know ?>
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
