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
    
    // Auto-migration for Admin Email Login
    try {
        $check_email = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'email'")->fetch();
        if (!$check_email) {
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `email` VARCHAR(100) UNIQUE AFTER `username`");
            
            // Fetch fallback email from portfolio info or default
            $default_email = 'admin@example.com';
            try {
                $saved_email = $pdo->query("SELECT `meta_value` FROM `portfolio_info` WHERE `meta_key` = 'email'")->fetchColumn();
                if ($saved_email && filter_var($saved_email, FILTER_VALIDATE_EMAIL)) {
                    $default_email = $saved_email;
                }
            } catch (PDOException $ex) {}
            
            // Assign default email to admin
            $update_stmt = $pdo->prepare("UPDATE `users` SET `email` = ? WHERE `username` = 'admin' OR `email` IS NULL");
            $update_stmt->execute([$default_email]);
        }
    } catch (PDOException $ex) {
        // Silent fail to prevent blocking if database tables are not set up yet
    }

} catch (PDOException $e) {
    // Safe generic message to avoid credential exposure in stack traces
    die("Database connection failed. Please verify your configuration.");
}
