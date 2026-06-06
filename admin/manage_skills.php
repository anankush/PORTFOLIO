<?php
/**
 * Manage Skills Inventory
 * Adds, edits, or deletes developer skills.
 */
require_once __DIR__ . '/header.php';

$success_msg = '';
$error_msg = '';

// Handle Delete Action
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM `skills` WHERE `id` = ?");
        $stmt->execute([$id]);
        $success_msg = 'Skill deleted successfully!';
    } catch (PDOException $e) {
        $error_msg = 'Failed to delete skill: ' . $e->getMessage();
    }
}

// Handle Add / Edit Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $proficiency = (int)($_POST['proficiency'] ?? 80);
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    
    if ($category !== '' && $name !== '') {
        if ($proficiency >= 1 && $proficiency <= 100) {
            try {
                if ($edit_id > 0) {
                    $stmt = $pdo->prepare("UPDATE `skills` SET `category` = ?, `name` = ?, `proficiency` = ? WHERE `id` = ?");
                    $stmt->execute([$category, $name, $proficiency, $edit_id]);
                    $success_msg = 'Skill updated successfully!';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO `skills` (`category`, `name`, `proficiency`) VALUES (?, ?, ?)");
                    $stmt->execute([$category, $name, $proficiency]);
                    $success_msg = 'New skill added successfully!';
                }
                $_POST = [];
                $edit_id = 0;
            } catch (PDOException $e) {
                $error_msg = 'Failed to save skill: ' . $e->getMessage();
            }
        } else {
            $error_msg = 'Proficiency level must be a value between 1 and 100.';
        }
    } else {
        $error_msg = 'Skill Category and Skill Name are required.';
    }
}

// Check if in edit mode
$edit_item = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM `skills` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$id]);
    $edit_item = $stmt->fetch();
}

// Fetch all skills ordered by category and proficiency
$skills = [];
try {
    $stmt = $pdo->query("SELECT * FROM `skills` ORDER BY `category`, `proficiency` DESC");
    $skills = $stmt->fetchAll();
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
    <h2>
        <i class="fas fa-brain"></i>
        <?= $edit_item ? 'Edit Skill Profile' : 'Add New Technical Skill' ?>
    </h2>
    
    <form action="manage_skills.php" method="POST" class="admin-form">
        <?php if ($edit_item): ?>
            <input type="hidden" name="edit_id" value="<?= (int)$edit_item['id'] ?>">
        <?php endif; ?>
        
        <div class="form-row-2">
            <div class="form-group">
                <label for="category">Skill Category</label>
                <select id="category" name="category" required>
                    <option value="" disabled <?= !isset($_POST['category']) && !$edit_item ? 'selected' : '' ?>>Select Category</option>
                    <option value="Frontend" <?= (($_POST['category'] ?? ($edit_item['category'] ?? '')) === 'Frontend') ? 'selected' : '' ?>>Frontend (e.g. HTML, CSS, React)</option>
                    <option value="Backend" <?= (($_POST['category'] ?? ($edit_item['category'] ?? '')) === 'Backend') ? 'selected' : '' ?>>Backend (e.g. PHP, Node, MySQL)</option>
                    <option value="Tools" <?= (($_POST['category'] ?? ($edit_item['category'] ?? '')) === 'Tools') ? 'selected' : '' ?>>Tools / Others (e.g. Git, Figma, VS Code)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="name">Skill Name</label>
                <input type="text" id="name" name="name" required placeholder="e.g. JavaScript" value="<?= htmlspecialchars($_POST['name'] ?? ($edit_item['name'] ?? '')) ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="proficiency">Proficiency Level (1 - 100%)</label>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <input type="range" id="proficiency" name="proficiency" min="1" max="100" style="flex-grow: 1; accent-color: var(--primary-color);" value="<?= (int)($_POST['proficiency'] ?? ($edit_item['proficiency'] ?? 80)) ?>" oninput="this.nextElementSibling.value = this.value + '%'">
                <output style="font-weight: 700; width: 3rem; text-align: right;"><?= (int)($_POST['proficiency'] ?? ($edit_item['proficiency'] ?? 80)) ?>%</output>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1rem;">
            <button type="submit" class="btn-admin btn-admin-primary">
                <?= $edit_item ? 'Update Skill <i class="fas fa-edit"></i>' : 'Add Skill <i class="fas fa-plus"></i>' ?>
            </button>
            <?php if ($edit_item): ?>
                <a href="manage_skills.php" class="btn-admin btn-view-site" style="height: auto;">Cancel Edit</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="admin-card">
    <h2><i class="fas fa-list-check"></i> Skills Inventory</h2>
    
    <?php if (empty($skills)): ?>
        <p style="color: var(--text-secondary); text-align: center; padding: 1.5rem 0;">No skills added yet. Create one above.</p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Skill Name</th>
                        <th>Proficiency Level</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $current_cat = '';
                    foreach ($skills as $skill): 
                    ?>
                        <tr>
                            <td style="font-weight: 750; color: var(--secondary-color);">
                                <?php 
                                if ($current_cat !== $skill['category']) {
                                    $current_cat = $skill['category'];
                                    echo htmlspecialchars($current_cat);
                                } else {
                                    echo '<span style="color: var(--text-muted);">"</span>';
                                }
                                ?>
                            </td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($skill['name']) ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.8rem;">
                                    <div style="width: 100px; height: 6px; background: rgba(255,255,255,0.05); border-radius: 3px; overflow: hidden;">
                                        <div style="width: <?= (int)$skill['proficiency'] ?>%; height: 100%; background: var(--gradient-primary);"></div>
                                    </div>
                                    <span style="font-weight: 600;"><?= (int)$skill['proficiency'] ?>%</span>
                                </div>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="manage_skills.php?edit=<?= (int)$skill['id'] ?>" class="action-btn action-btn-edit" title="Edit Skill">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <a href="manage_skills.php?delete=<?= (int)$skill['id'] ?>" class="action-btn action-btn-delete" title="Delete Skill" onclick="return confirm('Are you sure you want to delete this skill?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
