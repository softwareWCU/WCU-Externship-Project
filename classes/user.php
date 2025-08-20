<?php
require_once 'database_connection.php';

class User
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Login method (plain text password)
    public function login($username, $password)
    {
        $username = $this->conn->real_escape_string($username);
        $password = $this->conn->real_escape_string($password);

        $sql = "SELECT * FROM users WHERE username='$username' AND password='$password' LIMIT 1";
        $result = $this->conn->query($sql);

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return true;
        }
        return false;
    }

    public function isLoggedIn()
    {
        session_start();
        return isset($_SESSION['user_id']);
    }

    public function logout()
    {
        session_start();
        session_unset();
        session_destroy();
    }
}
?>