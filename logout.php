<?php
// logout.php

// Start session
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login page
header("Location: index.php"); // change to your login page
exit;
?>
