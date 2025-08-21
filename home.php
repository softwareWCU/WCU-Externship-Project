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
     

    
</head>

<body >
    <header>
        <div class="logo-container">
            <img src="logo.png" alt="Website Logo" class="logo">
        </div>
        <h3>Utility Service Request and Provision Management System for Wachemo University</h3>
    
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
        <li><a href="add_user.php">⬆️ Upload Staff</a></li>
        <li><a href="manage_users.php?status=all">👤 UserAccount</a></li>
    </ul>
</li>

        <li><a href="logout.php">⏻ Logout</a></li>
    </ul>
</nav>


    
    <div class="home-content">
        <h1>Welcome to the System Admin Page</h1>
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