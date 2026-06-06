<?php
/**
 * Manage Education Entries
 * Adds, edits, or deletes educational qualification logs.
 */
require_once __DIR__ . '/header.php';

$success_msg = '';
$error_msg = '';

// Handle Delete Action
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM `education` WHERE `id` = ?");
        $stmt->execute([$id]);
        $success_msg = 'Education entry deleted successfully!';
    } catch (PDOException $e) {
        $error_msg = 'Failed to delete entry: ' . $e->getMessage();
    }
}

// Handle Add / Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $degree = trim($_POST['degree'] ?? '');
    $institution = trim($_POST['institution'] ?? '');
    $year = trim($_POST['year'] ?? '');
    $result = trim($_POST['result'] ?? '');
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    
    if ($degree !== '' && $institution !== '' && $year !== '') {
        try {
            if ($edit_id > 0) {
                // Edit mode
                $stmt = $pdo->prepare("UPDATE `education` SET `degree` = ?, `institution` = ?, `year` = ?, `result` = ? WHERE `id` = ?");
                $stmt->execute([$degree, $institution, $year, $result, $edit_id]);
                $success_msg = 'Education entry updated successfully!';
            } else {
                // Add mode
                $stmt = $pdo->prepare("INSERT INTO `education` (`degree`, `institution`, `year`, `result`) VALUES (?, ?, ?, ?)");
                $stmt->execute([$degree, $institution, $year, $result]);
                $success_msg = 'New education entry added successfully!';
            }
            // Clear post values to reset form
            $_POST = [];
            $edit_id = 0;
        } catch (PDOException $e) {
            $error_msg = 'Failed to save entry: ' . $e->getMessage();
        }
    } else {
        $error_msg = 'Degree, Institution, and Year are required fields.';
    }
}

// Check if we are in Edit Mode (fetching active item)
$edit_item = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM `education` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$id]);
    $edit_item = $stmt->fetch();
}

// Fetch all education entries
$education = [];
try {
    $stmt = $pdo->query("SELECT * FROM `education` ORDER BY `id` DESC");
    $education = $stmt->fetchAll();
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
        <i class="fas fa-graduation-cap"></i> 
        <?= $edit_item ? 'Edit Education Entry' : 'Add New Education Entry' ?>
    </h2>
    
    <form action="manage_education.php" method="POST" class="admin-form">
        <?php if ($edit_item): ?>
            <input type="hidden" name="edit_id" value="<?= (int)$edit_item['id'] ?>">
        <?php endif; ?>
        
        <div class="form-row-2">
            <div class="form-group">
                <label for="degree">Degree / Course Name</label>
                <input type="text" id="degree" name="degree" required placeholder="e.g. Diploma in CSE" value="<?= htmlspecialchars($_POST['degree'] ?? ($edit_item['degree'] ?? '')) ?>">
            </div>
            <div class="form-group">
                <label for="institution">Institution / University</label>
                <input type="text" id="institution" name="institution" required placeholder="e.g. Polytechnic Institute" value="<?= htmlspecialchars($_POST['institution'] ?? ($edit_item['institution'] ?? '')) ?>">
            </div>
        </div>
        
        <div class="form-row-2">
            <div class="form-group">
                <label for="year">Year / Duration</label>
                <input type="text" id="year" name="year" required placeholder="e.g. 2023 - Present or 2022" value="<?= htmlspecialchars($_POST['year'] ?? ($edit_item['year'] ?? '')) ?>">
            </div>
            <div class="form-group">
                <label for="result">Result / GPA (Optional)</label>
                <input type="text" id="result" name="result" placeholder="e.g. CGPA: 3.85 or GPA: 5.00" value="<?= htmlspecialchars($_POST['result'] ?? ($edit_item['result'] ?? '')) ?>">
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1rem;">
            <button type="submit" class="btn-admin btn-admin-primary">
                <?= $edit_item ? 'Update Entry <i class="fas fa-edit"></i>' : 'Add Entry <i class="fas fa-plus"></i>' ?>
            </button>
            <?php if ($edit_item): ?>
                <a href="manage_education.php" class="btn-admin btn-view-site" style="height: auto;">Cancel Edit</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="admin-card">
    <h2><i class="fas fa-list"></i> Academic Records List</h2>
    
    <?php if (empty($education)): ?>
        <p style="color: var(--text-secondary); text-align: center; padding: 1.5rem 0;">No records found. Add one above.</p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Degree / Course</th>
                        <th>Institution</th>
                        <th>Result</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($education as $edu): ?>
                        <tr>
                            <td style="white-space: nowrap; font-weight: 650; color: var(--primary-color);">
                                <?= htmlspecialchars($edu['year']) ?>
                            </td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($edu['degree']) ?></td>
                            <td><?= htmlspecialchars($edu['institution']) ?></td>
                            <td>
                                <?= $edu['result'] !== '' ? htmlspecialchars($edu['result']) : '<span style="color: var(--text-muted);">N/A</span>' ?>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="manage_education.php?edit=<?= (int)$edu['id'] ?>" class="action-btn action-btn-edit" title="Edit Entry">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <a href="manage_education.php?delete=<?= (int)$edu['id'] ?>" class="action-btn action-btn-delete" title="Delete Entry" onclick="return confirm('Are you sure you want to delete this education entry?');">
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
