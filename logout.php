<?php
session_start(); // Start the session
session_unset(); // Unset all of the session variables
session_destroy(); // Destroy the session

// Redirect to the login page
header("Location: home.php");
exit(); // Prevent further script execution after redirection
?>
