<?php
session_start(); // Start the session

// Destroy the session to log out the user
session_destroy(); // This destroys all session data

// Redirect the user to the login page
header("Location: login.php"); // Change 'login.php' to the actual login page
exit(); // Ensure that no further code is executed after the redirect
?>
