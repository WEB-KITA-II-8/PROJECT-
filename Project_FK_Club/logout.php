<?php
// =============================================
// LOGOUT SYSTEM
// FILE: logout.php
// =============================================

// Start session
session_start();

// Remove all session variables
session_unset();

// Destroy session
session_destroy();

// Redirect user back to login page
header("Location: index.php");
exit();
?>