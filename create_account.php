<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}
// Prevent browser caching
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<?php
//session_start();
require 'vendor/autoload.php'; // PHPMailer
require_once 'database_connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$db = new Database();
$conn = $db->getConnection();

$message = "";

if(isset($_POST['generate'])){
    if(!empty($_POST['user_ids'])){
        foreach($_POST['user_ids'] as $userId){
            // Fetch user
            $stmt = $conn->prepare("SELECT id, firstname, email FROM staff WHERE id=? AND account_created=0");
            $stmt->bind_param("i",$userId);
            $stmt->execute();
            $res = $stmt->get_result();
            $user = $res->fetch_assoc();
            $stmt->close();

            if(!$user) continue;

            $username = $user['firstname'];
            $email    = $user['email'];

            // Generate random password
            $passwordPlain = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"),0,10);
            $passwordHash  = password_hash($passwordPlain, PASSWORD_BCRYPT);

            // Update DB
            $update = $conn->prepare("UPDATE staff SET username=?, password=?, account_created=1 WHERE id=?");
            $update->bind_param("ssi",$username,$passwordHash,$userId);
            $update->execute();
            $update->close();

            // Send email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'shemebo2008@gmail.com';   // change
                $mail->Password   = 'ifnd cctd scyu atfc';      // Gmail app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('shemebo2008@gmail.com','System Admin');
                $mail->addAddress($email,$username);

                $mail->isHTML(true);
                $mail->Subject = "Your New Account";
                $mail->Body    = "
                    <h3>Hello, $username</h3>
                    <p>Your account has been created.</p>
                    <p><b>Username:</b> $username<br>
                       <b>Password:</b> $passwordPlain</p>
                    
                ";

                $mail->send();
                $message .= "<div class='alert alert-success'>Account created & sent to $email</div>";
            } catch (Exception $e) {
                $message .= "<div class='alert alert-danger'>Failed to send to $email. Error: {$mail->ErrorInfo}</div>";
            }
        }
    } else {
        $message = "<div class='alert alert-danger'>No users selected.</div>";
    }
}
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
 <script>
window.history.forward();
function noBack() { window.history.forward(); }
window.onload = noBack;
window.onpageshow = function(evt) { if(evt.persisted) noBack(); };
window.onunload = function() {};
</script>

    <title>Home</title>
    <link rel="stylesheet" href="style.css">
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    
</head>

<body >
    <header>
        <div class="logo-container">
            <img src="logo.png" alt="Website Logo" class="logo">
        </div>
        <h2>Wachemo University</h2><br>
        <h3>Support Request Management System(SRMS)</h3> 
    </header>

    <!-- Sidebar Dashboard -->
    <!-- Sidebar Dashboard -->
<!-- Sidebar Dashboard -->
<nav class="sidebar">
    <h2>Dashboard</h2>
    <ul>
        <li class="dropdown">
    <a href="#">👥 Manage Users ▾</a>
    <ul class="dropdown-menu">
        <li><a href="upload_staff.php">⬆️ Upload Staff</a></li>
        <li><a href="manage_users.php?status=all">👤 UserAccount</a></li>
    </ul>
</li>

        <li><a href="logout.php">⏻ Logout</a></li>
    </ul>
</nav>
    <div class="selectemail">
   
    <?php if(!empty($message)) echo $message; ?>
    
    <form method="post">
        <div class="mb-3">
		<h2>Select User Email and Generate Account</h2>
            <label for="user_ids" class="form-label">Select Users:</label>
            <select name="user_ids[]" id="user_ids" class="form-select" multiple required size="8">
                <?php
                $users = $conn->query("SELECT id, firstname, email FROM staff WHERE account_created=0");
                while($row = $users->fetch_assoc()){
                    echo "<option value='{$row['id']}'>{$row['username']} ({$row['email']})</option>";
                }
                ?>
            </select>
            <small class="text-muted">Hold CTRL (Windows) or CMD (Mac) to select multiple users.</small>
        <button type="submit" name="generate" class="btn btn-primary">Generate & Send</button>
    
		</div>
        </form>
        </div>



<script>
document.addEventListener("DOMContentLoaded", function() {
    const dropdownToggle = document.querySelector(".dropdown-toggle");
    const dropdownMenu = document.querySelector(".dropdown-menu");

    dropdownToggle.addEventListener("click", function(e) {
        e.preventDefault(); // stop page reload
        dropdownMenu.style.display =
            dropdownMenu.style.display === "block" ? "none" : "block";
    });

    // Close if clicked outside
    document.addEventListener("click", function(e) {
        if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.style.display = "none";
        }
    });
});
</script>

</body>
<!--footer>
    &copy; 2025 Externship Group-AAP | All rights reserved.
</footer-->

</html>