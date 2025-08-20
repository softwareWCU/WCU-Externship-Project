<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'classes/user.php';

$user = new User();
$error = "";
if (isset($_POST['loginBtn'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Backend validation
    if (empty($username) && empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (empty($username)) {
        $error = "Please Enter Username";
    } elseif (empty($password)) {
        $error = "Please Enter Password";
    } else {
        if ($user->login($username, $password)) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}

// You can echo $error in your existing HTML form where needed
?>
<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
</head>

<body>
    <?php if (!empty($error))
        echo "<p style='color:red;'>$error</p>"; ?>
</body>

</html>