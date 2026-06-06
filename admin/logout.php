<?php
/**
 * Logout handler
 * Clears sessions and redirects to login.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = [];
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
