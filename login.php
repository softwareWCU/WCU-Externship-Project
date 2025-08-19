<?php
// login.php
session_start();
$conn = new mysqli("localhost","root","","your_database");

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if($result->num_rows == 1){
        $user = $result->fetch_assoc();
        if(password_verify($password, $user['password'])){
            // Login successful
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>



<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
