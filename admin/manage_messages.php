<?php
/**
 * Contact Form Messages Inbox
 * Displays messages submitted through the frontend form.
 */
require_once __DIR__ . '/header.php';

$success_msg = '';
$error_msg = '';

// Handle Delete Action
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM `messages` WHERE `id` = ?");
        $stmt->execute([$id]);
        $success_msg = 'Message deleted successfully!';
    } catch (PDOException $e) {
        $error_msg = 'Failed to delete message: ' . $e->getMessage();
    }
}

// Fetch all messages
$messages = [];
try {
    $stmt = $pdo->query("SELECT * FROM `messages` ORDER BY `id` DESC");
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {}
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

<div class="admin-card">
    <h2><i class="fas fa-inbox"></i> Visitor Messages Inbox</h2>
    
    <?php if (empty($messages)): ?>
        <div style="text-align: center; padding: 3rem; background: var(--bg-input); border-radius: 8px; border: 1px dashed var(--border-color);">
            <p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 0.5rem;"><i class="fas fa-envelope-open" style="font-size: 2.5rem; color: var(--text-muted); display: block; margin-bottom: 1rem;"></i> Your inbox is empty.</p>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Messages sent from the contact form on your portfolio website will appear here.</p>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <?php foreach ($messages as $msg): ?>
                <div style="background-color: var(--bg-input); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; position: relative;">
                    
                    <!-- Top header info -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid var(--border-color); padding-bottom: 0.8rem; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <span style="font-size: 0.85rem; color: var(--text-muted); display: block;">From:</span>
                            <strong style="font-size: 1.1rem; color: var(--text-primary);"><?= htmlspecialchars($msg['name']) ?></strong>
                            <a href="mailto:<?= htmlspecialchars($msg['email']) ?>" style="font-size: 0.9rem; color: var(--primary-color); display: inline-block; margin-left: 0.5rem; text-decoration: underline;">
                                &lt;<?= htmlspecialchars($msg['email']) ?>&gt;
                            </a>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 0.85rem; color: var(--text-muted); display: block;">Sent Date:</span>
                            <span style="font-size: 0.9rem; font-weight: 500;"><?= htmlspecialchars($msg['created_at']) ?></span>
                        </div>
                    </div>
                    
                    <!-- Subject and Message Body -->
                    <div>
                        <span style="font-size: 0.85rem; color: var(--text-muted); display: block;">Subject:</span>
                        <strong style="font-size: 1.05rem; display: block; color: var(--text-primary); margin-bottom: 0.5rem;">
                            <?= htmlspecialchars($msg['subject']) ?>
                        </strong>
                        <p style="background: rgba(0,0,0,0.2); padding: 1rem; border-radius: 6px; color: var(--text-secondary); line-height: 1.6; font-size: 0.95rem; white-space: pre-wrap; border: 1px solid rgba(255,255,255,0.02);"><?= htmlspecialchars($msg['message']) ?></p>
                    </div>
                    
                    <!-- Actions -->
                    <div style="display: flex; justify-content: flex-end;">
                        <a href="manage_messages.php?delete=<?= (int)$msg['id'] ?>" class="btn-admin btn-admin-danger" style="padding: 0.4rem 1rem; font-size: 0.85rem;" onclick="return confirm('Are you sure you want to delete this contact message? This action is irreversible.');">
                            Delete Message <i class="fas fa-trash-alt" style="margin-left: 0.3rem;"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
