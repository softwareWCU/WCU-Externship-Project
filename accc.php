<?php
session_start();
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
    <title>Generate Accounts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h2>Generate Accounts for Users</h2>
    <?php if(!empty($message)) echo $message; ?>
    
    <form method="post">
        <div class="mb-3">
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
        </div>
        <button type="submit" name="generate" class="btn btn-primary">Generate & Send</button>
    </form>
</body>
</html>