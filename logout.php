<?php
// logout.php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Start a new session to set the logout message
session_start();
$_SESSION['success_message'] = "You have been logged out successfully.";

// Redirect to the login page
header("Location: index.php");
exit;
?>
