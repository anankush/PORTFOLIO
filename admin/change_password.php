<?php
/**
 * Change Admin Password
 * Verifies current password and updates the hash in the database.
 */
require_once __DIR__ . '/header.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';
    
    if ($current_pass !== '' && $new_pass !== '' && $confirm_pass !== '') {
        if ($new_pass === $confirm_pass) {
            if (strlen($new_pass) >= 6) {
                try {
                    // Fetch current password hash from database
                    $stmt = $pdo->prepare("SELECT `password` FROM `users` WHERE `username` = ? LIMIT 1");
                    $stmt->execute([$_SESSION['admin_username']]);
                    $hashed_password = $stmt->fetchColumn();
                    
                    // Verify current password
                    if ($hashed_password && password_verify($current_pass, $hashed_password)) {
                        // Hash the new password using bcrypt
                        $new_hashed_password = password_hash($new_pass, PASSWORD_BCRYPT);
                        
                        // Update in database
                        $update_stmt = $pdo->prepare("UPDATE `users` SET `password` = ? WHERE `username` = ?");
                        $update_stmt->execute([$new_hashed_password, $_SESSION['admin_username']]);
                        
                        $success_msg = 'Password changed successfully!';
                    } else {
                        $error_msg = 'Incorrect current password.';
                    }
                } catch (PDOException $e) {
                    $error_msg = 'Database error: ' . $e->getMessage();
                }
            } else {
                $error_msg = 'New password must be at least 6 characters long.';
            }
        } else {
            $error_msg = 'New password and confirm password do not match.';
        }
    } else {
        $error_msg = 'All fields are required.';
    }
}
?>

<?php if ($success_msg !== ''): ?>
    <div class="alert-admin alert-admin-success">
        <i class="fas fa-check-circle"></i>
        <span><?= htmlspecialchars($success_msg) ?></span>
    </div>
<?php endif; ?>

<?php if ($error_msg !== ''): ?>
    <div class="alert-admin alert-admin-error">
        <i class="fas fa-exclamation-circle"></i>
        <span><?= htmlspecialchars($error_msg) ?></span>
    </div>
<?php endif; ?>

<div class="admin-card" style="max-width: 600px; margin: 0 auto;">
    <h2><i class="fas fa-key"></i> Change Admin Password</h2>
    
    <form action="change_password.php" method="POST" class="admin-form">
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
        </div>
        
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" required minlength="6" autocomplete="new-password">
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="6" autocomplete="new-password">
        </div>
        
        <button type="submit" name="change_password" class="btn-admin btn-admin-primary" style="margin-top: 1rem;">
            Update Password <i class="fas fa-save"></i>
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
