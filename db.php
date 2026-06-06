<?php
/**
 * Database Connection configuration
 * Placeholders are replaced by GitHub Actions with secrets on deployment.
 * Fallback is provided for local execution.
 */
$db_host = 'DB_HOST_PLACEHOLDER';
$db_user = 'DB_USER_PLACEHOLDER';
$db_pass = 'DB_PASS_PLACEHOLDER';
$db_name = 'DB_NAME_PLACEHOLDER';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // Safe generic message to avoid credential exposure in stack traces
    die("Database connection failed. Please verify your configuration.");
}
