<?php
session_start();

// Destroy the session to log the user out
session_unset();  // Unsets all session variables
session_destroy();  // Destroys the session

// Redirect the user to the login page
header("Location: login.php");
exit;
?>
