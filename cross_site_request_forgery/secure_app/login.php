<?php
require_once "csrf.php";
secure_session_start();
require_once "logger.php";

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)
{
    header("location: welcome.php");
    exit;
}

require_once "config.php";

$username = $password = "";
$username_err = $password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    if (empty(trim($_POST["username"])))
    {
        $username_err = "Please enter username.";
    }
    else
    {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"])))
    {
        $password_err = "Please enter your password.";
    }
    else
    {
        $password = trim($_POST["password"]);
    }

    if (empty($username_err) && empty($password_err))
    {
        //parameterised query
        $sql = "SELECT id, username FROM users WHERE username = ? AND password = md5(?)";
        $stmt = $link->prepare($sql);
        $stmt->execute([$username, $password]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row)
        {

            //generate new session id when privileges change to prevent session fixation
            $old_session_id = session_id();
            session_regenerate_id(true);
            csrf_clear($old_session_id);

            $_SESSION["loggedin"] = true;
            $_SESSION["id"] = $row["id"];
            $_SESSION["username"] = $row["username"];
            app_log("INFO", "Login success: $username");

            header("location: welcome.php");
            exit;
        }
        else
        {
            $password_err = "The password you entered was not valid.";
            app_log("WARNING", "Login failed: $username");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>
        <form action="login.php" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" autocomplete="off" class="form-control" value="<?php echo htmlspecialchars($username); ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" autocomplete="off" class="form-control">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
        </form>
    </div>
</body>
</html>
