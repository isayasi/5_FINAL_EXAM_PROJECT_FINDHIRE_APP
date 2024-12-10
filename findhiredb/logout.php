<?php
session_start();  // Start the session

// Destroy the session to log the user out
session_unset();  // Unset all session variables
session_destroy();  // Destroy the session

// Redirect to the login page after logging out
header("Location: login.php");  // Replace 'login.php' with your login page URL
exit();  // Make sure no further code is executed after the redirect
?>
