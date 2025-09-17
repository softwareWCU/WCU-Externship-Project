<?php
require_once 'database_connection.php';

$error = "";
$username = "";

$db = new Database();
$conn = $db->getConnection();

if (isset($_POST['loginBtn'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "⚠ Please fill in all fields.";
    } else {
        $sql = "SELECT password,username,staff_type FROM staff WHERE username = ? ";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = "SQL error: " . $conn->error;
        } else {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $dbPassword = $row['password'];
                $username = $row['username'];
                session_start();
                $_SESSION['username'] = $row['username'];
                if (password_verify($password, $dbPassword) && $row['staff_type'] =="admin") {

                    header("Location: home.php");
                    exit;
                } else {
                    $error = "❌ Invalid username or password.";
                }
                if ($password === $dbPassword && $row['staff_type'] == "academic") {

                    header("Location: user_feedback.php");
                    exit;
                } else {
                    $error = "❌ Invalid username or password.";
                }
            } else {
                $error = "❌ Invalid username or password.";
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Login</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        .password-box {
            position: relative;
            width: 100%;
        }

        .password-box input {
            width: 100%;
            padding-right: 40px;
            /* space for the eye */
        }

        .toggle-icon {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
        }

        .error {
            color: #b30000;
            font-size: 13px;
            margin: 3px 0 10px 0;
        }
    </style>
</head>

<body>
    <header>
        <div class="logo-container">
            <img src="logo.png" alt="Website Logo" class="logo">
        </div>
        <h2>Wachemo University</h2><br>
        <h3>Support Request Management System(SRMS)</h3>
    </header>

    <div class="login-wrapper">
        <div class="login-box">
            <h2>Login</h2>

            <form id="loginForm" action="index.php" method="post">
                <input type="text" placeholder="Enter username" name="username"
                    value="<?php echo htmlspecialchars($username); ?>">
                <div id="usernameError" class="error-message"></div>

                <!-- Password with show/hide toggle -->
                <div class="password-box">
                    <input type="password" id="password" name="password" placeholder="Enter password">
                    <div id="passwordError" class="error-message"></div>
                    <span class="toggle-icon" onclick="togglePassword()">👁</span>
                </div>
                <?php if (!empty($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                <button type="submit" name="loginBtn">Login</button>
            </form>
        </div>
        <div class="container"></div>
    </div>

    <script>
        function togglePassword() {
            const pwd = document.getElementById("password");
            pwd.type = (pwd.type === "password") ? "text" : "password";
        }
    </script>

</body>

</html>