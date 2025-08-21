<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'database_connection.php'; // your Database class

$error = "";
$username = "";

$db = new Database();
$conn = $db->getConnection(); // make sure this returns mysqli connection

if (isset($_POST['loginBtn'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $sql = "SELECT password_hash FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("SQL error: " . $conn->error); // ✅ debug if prepare() fails
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $dbPassword = $row['password_hash'];
            
            if ($password === $dbPassword) {
                header("Location: home.php");
                exit;
            } else {
                $error = "Invalid username or password.";
                header("Location: index.php");
            }
        } else {

            echo "Invalid username && password.";



        }

        $stmt->close();
    }
}
?>