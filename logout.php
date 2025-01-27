<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Set logout message
session_start();
$_SESSION['logout_message'] = "You have been logged out successfully.";

// Redirect to login page
header("Location: login.php");
exit;
?>