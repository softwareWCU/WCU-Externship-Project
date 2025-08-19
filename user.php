<?php
// User.php
require_once "Database.php";

class User {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->conn;
    }

    // Register user
    public function register($username, $password) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $passwordHash);
        return $stmt->execute();
    }

    // Login user
    public function login($username, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows == 1){
            $user = $result->fetch_assoc();
            if(password_verify($password, $user['password'])){
                // Start session
                session_start();
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                return true;
            }
        }
        return false;
    }

    // Logout user
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
    }

    // Check if user is logged in
    public function isLoggedIn() {
        session_start();
        return isset($_SESSION['user_id']);
    }
}
?>
