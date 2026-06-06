<?php
/**
 * Session authentication guard with automatic idle timeout.
 * Included at the top of all admin dashboard pages.
 */
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_use_only_cookies', 1);
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    session_set_cookie_params([
        'samesite' => 'Strict',
        'httponly' => true,
        'secure' => isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443
    ]);
    session_start();
}

// 1. Redirect to login if user is not authenticated
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// 2. Security Optimization: 30 minutes (1800 seconds) idle session timeout
$timeout_duration = 1800; 

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    // Session expired
    $_SESSION = [];
    session_destroy();
    
    // Redirect to login with timeout flag
    header("Location: login.php?timeout=1");
    exit();
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();
