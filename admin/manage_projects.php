<?php
/**
 * Manage Project Catalog
 * Adds, edits, or deletes portfolio showcase projects with secure image upload and disk cleanup.
 */
require_once __DIR__ . '/header.php';

$success_msg = '';
$error_msg = '';

// Handle Delete Action
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        // Fetch project image to delete from disk
        $stmt = $pdo->prepare("SELECT `image` FROM `projects` WHERE `id` = ?");
        $stmt->execute([$id]);
        $image_file = $stmt->fetchColumn();
        
        if ($image_file && file_exists(__DIR__ . '/../images/uploads/' . $image_file)) {
            unlink(__DIR__ . '/../images/uploads/' . $image_file);
        }
        
        $stmt = $pdo->prepare("DELETE FROM `projects` WHERE `id` = ?");
        $stmt->execute([$id]);
        $success_msg = 'Project deleted successfully!';
    } catch (PDOException $e) {
        $error_msg = 'Failed to delete project: ' . $e->getMessage();
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $tools = trim($_POST['tools'] ?? '');
    $github_link = trim($_POST['github_link'] ?? '');
    $live_link = trim($_POST['live_link'] ?? '');
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    
    if ($title !== '' && $description !== '' && $tools !== '') {
        try {
            $image_name = '';
            
            // If editing, fetch current image first
            if ($edit_id > 0) {
                $stmt = $pdo->prepare("SELECT `image` FROM `projects` WHERE `id` = ?");
                $stmt->execute([$edit_id]);
                $image_name = $stmt->fetchColumn();
            }
            
            // Image Upload Logic
            if (isset($_FILES['proj_img']) && $_FILES['proj_img']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['proj_img']['tmp_name'];
                $file_name = $_FILES['proj_img']['name'];
                $file_size = $_FILES['proj_img']['size'];
                
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (in_array($ext, $allowed_exts)) {
                    if ($file_size <= 2 * 1024 * 1024) { // 2MB Limit
                        // Delete old image from disk if editing
                        if ($edit_id > 0 && $image_name && file_exists(__DIR__ . '/../images/uploads/' . $image_name)) {
                            unlink(__DIR__ . '/../images/uploads/' . $image_name);
                        }
                        
                        $upload_dir = __DIR__ . '/../images/uploads/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        $image_name = 'proj_' . uniqid() . '.' . $ext;
                        move_uploaded_file($file_tmp, $upload_dir . $image_name);
                    } else {
                        $error_msg .= 'Project image exceeds size limit of 2MB. ';
                    }
                } else {
                    $error_msg .= 'Invalid image format. Only JPG, JPEG, PNG, WEBP allowed. ';
                }
            }
            
            if ($error_msg === '') {
                if ($edit_id > 0) {
                    $stmt = $pdo->prepare("UPDATE `projects` SET `title` = ?, `description` = ?, `image` = ?, `tools` = ?, `github_link` = ?, `live_link` = ? WHERE `id` = ?");
                    $stmt->execute([$title, $description, $image_name, $tools, $github_link, $live_link, $edit_id]);
                    $success_msg = 'Project updated successfully!';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO `projects` (`title`, `description`, `image`, `tools`, `github_link`, `live_link`) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $description, $image_name, $tools, $github_link, $live_link]);
                    $success_msg = 'New project added successfully!';
                }
                $_POST = [];
                $edit_id = 0;
            }
        } catch (PDOException $e) {
            $error_msg = 'Failed to save project: ' . $e->getMessage();
        }
    } else {
        $error_msg = 'Project Title, Description, and Tools tags are required.';
    }
}

// Fetch item for editing
$edit_item = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM `projects` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$id]);
    $edit_item = $stmt->fetch();
}

// Fetch all projects
$projects = [];
try {
    $stmt = $pdo->query("SELECT * FROM `projects` ORDER BY `id` DESC");
    $projects = $stmt->fetchAll();
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
        <i class="fas fa-folder-open"></i>
        <?= $edit_item ? 'Edit Project Catalog Record' : 'Add New Showcase Project' ?>
    </h2>
    
    <form action="manage_projects.php" method="POST" enctype="multipart/form-data" class="admin-form">
        <?php if ($edit_item): ?>
            <input type="hidden" name="edit_id" value="<?= (int)$edit_item['id'] ?>">
        <?php endif; ?>
        
        <div class="form-row-2">
            <div class="form-group">
                <label for="title">Project Title</label>
                <input type="text" id="title" name="title" required placeholder="e.g. Portfolio Website" value="<?= htmlspecialchars($_POST['title'] ?? ($edit_item['title'] ?? '')) ?>">
            </div>
            
            <div class="form-group">
                <label for="tools">Technologies Used (Comma-separated tags)</label>
                <input type="text" id="tools" name="tools" required placeholder="e.g. HTML, CSS, PHP, MySQL" value="<?= htmlspecialchars($_POST['tools'] ?? ($edit_item['tools'] ?? '')) ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Project Description</label>
            <textarea id="description" name="description" rows="4" required placeholder="Explain what the project does, key features built, and goals achieved..."><?= htmlspecialchars($_POST['description'] ?? ($edit_item['description'] ?? '')) ?></textarea>
        </div>
        
        <div class="form-row-2">
            <div class="form-group">
                <label for="github_link">GitHub Link (optional)</label>
                <input type="url" id="github_link" name="github_link" placeholder="https://github.com/username/repo" value="<?= htmlspecialchars($_POST['github_link'] ?? ($edit_item['github_link'] ?? '')) ?>">
            </div>
            
            <div class="form-group">
                <label for="live_link">Live Demo Link (optional)</label>
                <input type="url" id="live_link" name="live_link" placeholder="https://demo.com" value="<?= htmlspecialchars($_POST['live_link'] ?? ($edit_item['live_link'] ?? '')) ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="proj_img">Project Showcase Screenshot (JPG, PNG, WEBP - Max 2MB)</label>
            <input type="file" id="proj_img" name="proj_img" accept=".jpg,.jpeg,.png,.webp">
            <?php if ($edit_item && $edit_item['image']): ?>
                <span style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.2rem;">
                    Current screenshot: <a href="../images/uploads/<?= htmlspecialchars($edit_item['image']) ?>" target="_blank" style="color: var(--primary-color); text-decoration: underline;"><?= htmlspecialchars($edit_item['image']) ?></a>
                </span>
            <?php endif; ?>
        </div>
        
        <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1rem;">
            <button type="submit" class="btn-admin btn-admin-primary">
                <?= $edit_item ? 'Update Project <i class="fas fa-edit"></i>' : 'Add Project <i class="fas fa-plus"></i>' ?>
            </button>
            <?php if ($edit_item): ?>
                <a href="manage_projects.php" class="btn-admin btn-view-site" style="height: auto;">Cancel Edit</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="admin-card">
    <h2><i class="fas fa-list-check"></i> Project Showcase Catalog</h2>
    
    <?php if (empty($projects)): ?>
        <p style="color: var(--text-secondary); text-align: center; padding: 1.5rem 0;">No projects added yet. Create one above.</p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Project Info</th>
                        <th>Tools Tags</th>
                        <th>Demo Links</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $proj): ?>
                        <tr>
                            <td>
                                <?php if ($proj['image']): ?>
                                    <img src="../images/uploads/<?= htmlspecialchars($proj['image']) ?>" alt="" style="width: 70px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-color);">
                                <?php else: ?>
                                    <div style="width: 70px; height: 50px; background: rgba(255,255,255,0.05); border-radius: 4px; display: flex; justify-content: center; align-items: center; color: var(--text-muted); border: 1px solid var(--border-color);">
                                        <i class="fas fa-laptop-code"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="font-size: 1rem;"><?= htmlspecialchars($proj['title']) ?></strong>
                                <p style="font-size: 0.85rem; color: var(--text-secondary); max-width: 350px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-top: 0.2rem;">
                                    <?= htmlspecialchars($proj['description']) ?>
                                </p>
                            </td>
                            <td>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.3rem; max-width: 250px;">
                                    <?php 
                                    $tags = explode(',', $proj['tools']);
                                    foreach ($tags as $tag):
                                        if (trim($tag) !== ''):
                                    ?>
                                        <span style="font-size: 0.75rem; background: var(--border-color); color: var(--text-secondary); padding: 0.1rem 0.4rem; border-radius: 3px; font-weight: 500;">
                                            <?= htmlspecialchars(trim($tag)) ?>
                                        </span>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                            </td>
                            <td style="white-space: nowrap;">
                                <?php if ($proj['github_link'] && $proj['github_link'] !== '#'): ?>
                                    <a href="<?= htmlspecialchars($proj['github_link']) ?>" target="_blank" style="color: var(--primary-color); display: block; font-size: 0.85rem; margin-bottom: 0.2rem;"><i class="fab fa-github"></i> Repository</a>
                                <?php endif; ?>
                                <?php if ($proj['live_link'] && $proj['live_link'] !== '#'): ?>
                                    <a href="<?= htmlspecialchars($proj['live_link']) ?>" target="_blank" style="color: var(--secondary-color); display: block; font-size: 0.85rem;"><i class="fas fa-external-link-alt"></i> Live View</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="manage_projects.php?edit=<?= (int)$proj['id'] ?>" class="action-btn action-btn-edit" title="Edit Project">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <a href="manage_projects.php?delete=<?= (int)$proj['id'] ?>" class="action-btn action-btn-delete" title="Delete Project" onclick="return confirm('Are you sure you want to delete this project? This will also remove the screenshot image file.');">
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
