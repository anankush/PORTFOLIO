<?php
/**
 * Dynamic Custom Sections Manager
 * Allows creating custom sections (e.g., Certifications, Services) and items under those sections.
 */
require_once __DIR__ . '/header.php';

$success_msg = '';
$error_msg = '';

// --- Handle Section Header Actions (Add/Delete Section) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_section'])) {
    $title = trim($_POST['sec_title'] ?? '');
    $order = (int)($_POST['sec_order'] ?? 0);
    
    if ($title !== '') {
        try {
            $stmt = $pdo->prepare("INSERT INTO `custom_sections` (`title`, `section_order`) VALUES (?, ?)");
            $stmt->execute([$title, $order]);
            $success_msg = 'New custom section category created successfully!';
        } catch (PDOException $e) {
            $error_msg = 'Failed to create section category (Title must be unique): ' . $e->getMessage();
        }
    } else {
        $error_msg = 'Section Title is required.';
    }
}

if (isset($_GET['delete_section'])) {
    $sec_id = (int)$_GET['delete_section'];
    try {
        $stmt = $pdo->prepare("DELETE FROM `custom_sections` WHERE `id` = ?");
        $stmt->execute([$sec_id]);
        $success_msg = 'Custom section category and all its entries deleted!';
    } catch (PDOException $e) {
        $error_msg = 'Failed to delete section: ' . $e->getMessage();
    }
}

// --- Handle Section Item Actions (Add/Edit/Delete Section Item) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_item'])) {
    $sec_id = (int)($_POST['item_sec_id'] ?? 0);
    $item_title = trim($_POST['item_title'] ?? '');
    $item_subtitle = trim($_POST['item_subtitle'] ?? '');
    $item_desc = trim($_POST['item_desc'] ?? '');
    $item_date = trim($_POST['item_date'] ?? '');
    $item_link = trim($_POST['item_link'] ?? '');
    $edit_item_id = isset($_POST['edit_item_id']) ? (int)$_POST['edit_item_id'] : 0;
    
    if ($sec_id > 0 && $item_title !== '') {
        try {
            if ($edit_item_id > 0) {
                // Edit item
                $stmt = $pdo->prepare("UPDATE `custom_section_items` SET `section_id` = ?, `item_title` = ?, `item_subtitle` = ?, `item_description` = ?, `item_date` = ?, `item_link` = ? WHERE `id` = ?");
                $stmt->execute([$sec_id, $item_title, $item_subtitle, $item_desc, $item_date, $item_link, $edit_item_id]);
                $success_msg = 'Section entry updated successfully!';
            } else {
                // Add item
                $stmt = $pdo->prepare("INSERT INTO `custom_section_items` (`section_id`, `item_title`, `item_subtitle`, `item_description`, `item_date`, `item_link`) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$sec_id, $item_title, $item_subtitle, $item_desc, $item_date, $item_link]);
                $success_msg = 'New section entry added successfully!';
            }
            $_POST = [];
            $edit_item_id = 0;
        } catch (PDOException $e) {
            $error_msg = 'Failed to save section entry: ' . $e->getMessage();
        }
    } else {
        $error_msg = 'Section Category selection and Item Title are required fields.';
    }
}

if (isset($_GET['delete_item'])) {
    $item_id = (int)$_GET['delete_item'];
    try {
        $stmt = $pdo->prepare("DELETE FROM `custom_section_items` WHERE `id` = ?");
        $stmt->execute([$item_id]);
        $success_msg = 'Section entry deleted successfully!';
    } catch (PDOException $e) {
        $error_msg = 'Failed to delete section entry: ' . $e->getMessage();
    }
}

// Fetch active item if in Edit mode
$edit_item = null;
if (isset($_GET['edit_item'])) {
    $id = (int)$_GET['edit_item'];
    $stmt = $pdo->prepare("SELECT * FROM `custom_section_items` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$id]);
    $edit_item = $stmt->fetch();
}

// Fetch all Section Headers
$sections = [];
try {
    $stmt = $pdo->query("SELECT * FROM `custom_sections` ORDER BY `section_order` ASC, `id` ASC");
    $sections = $stmt->fetchAll();
} catch (PDOException $e) {}

// Fetch all Section Items grouped by Section Title
$items = [];
try {
    $stmt = $pdo->query("
        SELECT i.*, s.title as section_title 
        FROM `custom_section_items` i 
        JOIN `custom_sections` s ON i.section_id = s.id 
        ORDER BY s.section_order ASC, i.id ASC
    ");
    while ($row = $stmt->fetch()) {
        $items[$row['section_title']][] = $row;
    }
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

<div class="quick-links" style="grid-template-columns: 0.8fr 1.2fr; margin-bottom: 2rem;">
    <!-- LEFT: Manage Section Categories -->
    <div class="admin-card">
        <h2><i class="fas fa-folder-plus"></i> Create Custom Section</h2>
        <form action="manage_sections.php" method="POST" class="admin-form" style="gap: 1rem;">
            <div class="form-group">
                <label for="sec_title">Section Header Title</label>
                <input type="text" id="sec_title" name="sec_title" required placeholder="e.g. Certifications, Services">
            </div>
            <div class="form-group">
                <label for="sec_order">Display Order (Sorting index)</label>
                <input type="number" id="sec_order" name="sec_order" value="0" min="0">
            </div>
            <button type="submit" name="add_section" class="btn-admin btn-admin-primary" style="margin-top: 0.5rem; width: 100%;">
                Create Section <i class="fas fa-plus"></i>
            </button>
        </form>
        
        <h3 style="margin-top: 2rem; margin-bottom: 1rem; font-size: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Existing Sections</h3>
        <?php if (empty($sections)): ?>
            <p style="font-size: 0.9rem; color: var(--text-secondary); text-align: center; padding: 1rem 0;">No custom sections yet.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <?php foreach ($sections as $sec): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-input); padding: 0.6rem 0.8rem; border-radius: 6px; border: 1px solid var(--border-color);">
                        <div>
                            <strong style="font-size: 0.95rem;"><?= htmlspecialchars($sec['title']) ?></strong>
                            <span style="font-size: 0.75rem; color: var(--text-secondary); display: block;">Order: <?= (int)$sec['section_order'] ?></span>
                        </div>
                        <a href="manage_sections.php?delete_section=<?= (int)$sec['id'] ?>" class="action-btn action-btn-delete" style="width: 1.8rem; height: 1.8rem; font-size: 0.8rem;" title="Delete Section Category" onclick="return confirm('WARNING: Deleting this section category will permanently delete ALL items saved under this section! Continue?');">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- RIGHT: Manage Items under Sections -->
    <div class="admin-card">
        <h2>
            <i class="fas fa-layer-group"></i>
            <?= $edit_item ? 'Edit Section Entry' : 'Add Item to Custom Section' ?>
        </h2>
        
        <?php if (empty($sections)): ?>
            <div style="text-align: center; padding: 2rem; background: var(--bg-input); border-radius: 8px; border: 1px dashed var(--border-color);">
                <p style="color: var(--text-secondary);">Please create a custom section category on the left side before adding entries.</p>
            </div>
        <?php else: ?>
            <form action="manage_sections.php" method="POST" class="admin-form">
                <?php if ($edit_item): ?>
                    <input type="hidden" name="edit_id" value="<?= (int)$edit_item['section_id'] ?>">
                    <input type="hidden" name="edit_item_id" value="<?= (int)$edit_item['id'] ?>">
                <?php endif; ?>
                
                <div class="form-row-2">
                    <div class="form-group">
                        <label for="item_sec_id">Select Section Category</label>
                        <select id="item_sec_id" name="item_sec_id" required>
                            <option value="" disabled <?= !isset($_POST['item_sec_id']) && !$edit_item ? 'selected' : '' ?>>Choose Section...</option>
                            <?php foreach ($sections as $sec): ?>
                                <option value="<?= (int)$sec['id'] ?>" <?= (($_POST['item_sec_id'] ?? ($edit_item['section_id'] ?? '')) == $sec['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sec['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="item_title">Entry Title</label>
                        <input type="text" id="item_title" name="item_title" required placeholder="e.g. AWS Cloud Practitioner Certificate" value="<?= htmlspecialchars($_POST['item_title'] ?? ($edit_item['item_title'] ?? '')) ?>">
                    </div>
                </div>
                
                <div class="form-row-2">
                    <div class="form-group">
                        <label for="item_subtitle">Subtitle / Organization (optional)</label>
                        <input type="text" id="item_subtitle" name="item_subtitle" placeholder="e.g. Amazon Web Services" value="<?= htmlspecialchars($_POST['item_subtitle'] ?? ($edit_item['item_subtitle'] ?? '')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="item_date">Date / Year (optional)</label>
                        <input type="text" id="item_date" name="item_date" placeholder="e.g. 2024" value="<?= htmlspecialchars($_POST['item_date'] ?? ($edit_item['item_date'] ?? '')) ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="item_desc">Description (optional)</label>
                    <textarea id="item_desc" name="item_desc" rows="3" placeholder="Provide extra details, scope, or learning outcomes..."><?= htmlspecialchars($_POST['item_desc'] ?? ($edit_item['item_description'] ?? '')) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="item_link">Redirect Link / Verification URL (optional)</label>
                    <input type="url" id="item_link" name="item_link" placeholder="https://verify.url" value="<?= htmlspecialchars($_POST['item_link'] ?? ($edit_item['item_link'] ?? '')) ?>">
                </div>
                
                <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1rem;">
                    <button type="submit" name="save_item" class="btn-admin btn-admin-primary">
                        <?= $edit_item ? 'Update Entry <i class="fas fa-edit"></i>' : 'Save Entry <i class="fas fa-save"></i>' ?>
                    </button>
                    <?php if ($edit_item): ?>
                        <a href="manage_sections.php" class="btn-admin btn-view-site" style="height: auto;">Cancel Edit</a>
                    <?php endif; ?>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="admin-card">
    <h2><i class="fas fa-table-list"></i> Custom Section Entries List</h2>
    
    <?php if (empty($items)): ?>
        <p style="color: var(--text-secondary); text-align: center; padding: 1.5rem 0;">No entries found. Create some above.</p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>Title / Subtitle</th>
                        <th>Date</th>
                        <th>Links</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $current_section = '';
                    foreach ($items as $sec_title => $sec_items):
                        foreach ($sec_items as $item):
                    ?>
                        <tr>
                            <td style="font-weight: 750; color: var(--accent-color);">
                                <?php 
                                if ($current_section !== $sec_title) {
                                    $current_section = $sec_title;
                                    echo htmlspecialchars($current_section);
                                } else {
                                    echo '<span style="color: var(--text-muted);">"</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <strong style="font-size: 0.95rem;"><?= htmlspecialchars($item['item_title']) ?></strong>
                                <?php if ($item['item_subtitle']): ?>
                                    <span style="font-size: 0.8rem; display: block; color: var(--text-secondary);"><?= htmlspecialchars($item['item_subtitle']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= $item['item_date'] !== '' ? htmlspecialchars($item['item_date']) : '<span style="color: var(--text-muted);">N/A</span>' ?></td>
                            <td>
                                <?php if ($item['item_link'] && $item['item_link'] !== '#'): ?>
                                    <a href="<?= htmlspecialchars($item['item_link']) ?>" target="_blank" style="color: var(--primary-color); font-size: 0.85rem;"><i class="fas fa-link"></i> Verification URL</a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">None</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="manage_sections.php?edit_item=<?= (int)$item['id'] ?>" class="action-btn action-btn-edit" title="Edit Entry">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <a href="manage_sections.php?delete_item=<?= (int)$item['id'] ?>" class="action-btn action-btn-delete" title="Delete Entry" onclick="return confirm('Are you sure you want to delete this custom section entry?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php 
                        endforeach;
                    endforeach; 
                    ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
