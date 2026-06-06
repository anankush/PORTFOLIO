<?php
/**
 * Manage Personal Profile Settings
 * Edits bio, social links, contact info, and uploads profile pictures/resumes securely.
 */
require_once __DIR__ . '/header.php';

$success_msg = '';
$error_msg = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    // 1. Process basic text fields
    $fields = [
        'name', 'title', 'bio_short', 'bio_full', 
        'email', 'phone', 'location', 
        'github', 'linkedin', 'facebook',
        'gpa', 'coffee'
    ];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO `portfolio_info` (`meta_key`, `meta_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `meta_value` = ?");
        
        foreach ($fields as $field) {
            $value = trim($_POST[$field] ?? '');
            $stmt->execute([$field, $value, $value]);
        }
        
        // 2. Secure Upload Directory Check
        $upload_dir = __DIR__ . '/../images/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // 3. Handle Profile Picture Upload
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['profile_pic']['tmp_name'];
            $file_name = $_FILES['profile_pic']['name'];
            $file_size = $_FILES['profile_pic']['size'];
            
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($ext, $allowed_exts)) {
                if ($file_size <= 2 * 1024 * 1024) { // 2MB Limit
                    $new_name = 'profile_' . uniqid() . '.' . $ext;
                    $dest_path = $upload_dir . $new_name;
                    
                    if (move_uploaded_file($file_tmp, $dest_path)) {
                        // Update in DB
                        $stmt->execute(['profile_picture', $new_name, $new_name]);
                    } else {
                        $error_msg .= 'Failed to move profile picture to uploads directory. ';
                    }
                } else {
                    $error_msg .= 'Profile picture exceeds size limit of 2MB. ';
                }
            } else {
                $error_msg .= 'Invalid profile picture format. Only JPG, JPEG, PNG, WEBP allowed. ';
            }
        }
        
        // 4. Handle Resume PDF Upload
        if (isset($_FILES['resume_file']) && $_FILES['resume_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['resume_file']['tmp_name'];
            $file_name = $_FILES['resume_file']['name'];
            $file_size = $_FILES['resume_file']['size'];
            
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['pdf'];
            
            if (in_array($ext, $allowed_exts)) {
                if ($file_size <= 5 * 1024 * 1024) { // 5MB Limit
                    $new_name = 'resume_' . uniqid() . '.' . $ext;
                    $dest_path = $upload_dir . $new_name;
                    
                    if (move_uploaded_file($file_tmp, $dest_path)) {
                        // Store the relative link
                        $resume_link = 'images/uploads/' . $new_name;
                        $stmt->execute(['resume_url', $resume_link, $resume_link]);
                    } else {
                        $error_msg .= 'Failed to save resume file. ';
                    }
                } else {
                    $error_msg .= 'Resume file exceeds size limit of 5MB. ';
                }
            } else {
                $error_msg .= 'Invalid resume format. Only PDF allowed. ';
            }
        }
        
        if ($error_msg === '') {
            $success_msg = 'Profile settings updated successfully!';
        }
        
    } catch (PDOException $e) {
        $error_msg = 'Database error: ' . $e->getMessage();
    }
}

// Fetch current values
$info = [];
try {
    $stmt = $pdo->query("SELECT * FROM `portfolio_info`");
    while ($row = $stmt->fetch()) {
        $info[$row['meta_key']] = $row['meta_value'];
    }
} catch (PDOException $e) {}

// Safe value helper
function val($info, $key) {
    return htmlspecialchars($info[$key] ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<?php if ($success_msg !== ''): ?>
    <div class="alert-admin alert-admin-success">
        <i class="fas fa-check-circle"></i>
        <span><?= $success_msg ?></span>
    </div>
<?php endif; ?>

<?php if ($error_msg !== ''): ?>
    <div class="alert-admin alert-admin-error">
        <i class="fas fa-exclamation-circle"></i>
        <span><?= $error_msg ?></span>
    </div>
<?php endif; ?>

<div class="admin-card">
    <h2><i class="fas fa-user-edit"></i> Edit Portfolio Profile</h2>
    
    <form action="manage_profile.php" method="POST" enctype="multipart/form-data" class="admin-form">
        <!-- Name & Professional Title -->
        <div class="form-row-2">
            <div class="form-group">
                <label for="name">Your Full Name</label>
                <input type="text" id="name" name="name" required value="<?= val($info, 'name') ?>">
            </div>
            <div class="form-group">
                <label for="title">Title / Tagline</label>
                <input type="text" id="title" name="title" required placeholder="e.g. Student & Frontend Developer" value="<?= val($info, 'title') ?>">
            </div>
        </div>
        
        <!-- Short & Full Bio -->
        <div class="form-group">
            <label for="bio_short">Short Bio (Tagline description)</label>
            <input type="text" id="bio_short" name="bio_short" required placeholder="A brief one-sentence introduction" value="<?= val($info, 'bio_short') ?>">
        </div>
        
        <div class="form-group">
            <label for="bio_full">Full Bio (Story / About Me)</label>
            <textarea id="bio_full" name="bio_full" rows="5" placeholder="Tell visitors about your passion, academic studies, and hobbies..."><?= val($info, 'bio_full') ?></textarea>
        </div>
        
        <!-- Profile Picture & Resume Uploads -->
        <div class="form-row-2">
            <div class="form-group">
                <label for="profile_pic">Profile Picture (JPG, PNG, WEBP - Max 2MB)</label>
                <input type="file" id="profile_pic" name="profile_pic" accept=".jpg,.jpeg,.png,.webp">
                <?php if (isset($info['profile_picture']) && $info['profile_picture'] !== ''): ?>
                    <span style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.2rem;">
                        Current image: <a href="../images/uploads/<?= htmlspecialchars($info['profile_picture']) ?>" target="_blank" style="color: var(--primary-color); text-decoration: underline;"><?= htmlspecialchars($info['profile_picture']) ?></a>
                    </span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="resume_file">Resume / CV (PDF format - Max 5MB)</label>
                <input type="file" id="resume_file" name="resume_file" accept=".pdf">
                <?php if (isset($info['resume_url']) && $info['resume_url'] !== '#'): ?>
                    <span style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.2rem;">
                        Current Resume: <a href="../<?= htmlspecialchars($info['resume_url']) ?>" target="_blank" style="color: var(--primary-color); text-decoration: underline;">View Current PDF</a>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Contact details -->
        <h3 style="margin-top: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; font-size: 1.1rem;"><i class="fas fa-id-card"></i> Contact Information</h3>
        <div class="form-row-2">
            <div class="form-group">
                <label for="email">Public Email Address</label>
                <input type="email" id="email" name="email" value="<?= val($info, 'email') ?>">
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" value="<?= val($info, 'phone') ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="location">Location Address</label>
            <input type="text" id="location" name="location" placeholder="e.g. Dhaka, Bangladesh" value="<?= val($info, 'location') ?>">
        </div>
        
        <!-- Social profile URLs -->
        <h3 style="margin-top: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; font-size: 1.1rem;"><i class="fas fa-share-nodes"></i> Social Links</h3>
        <div class="form-row-2">
            <div class="form-group">
                <label for="github">GitHub Profile URL</label>
                <input type="url" id="github" name="github" placeholder="https://github.com/username" value="<?= val($info, 'github') ?>">
            </div>
            <div class="form-group">
                <label for="linkedin">LinkedIn Profile URL</label>
                <input type="url" id="linkedin" name="linkedin" placeholder="https://linkedin.com/in/username" value="<?= val($info, 'linkedin') ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="facebook">Facebook Profile URL</label>
            <input type="url" id="facebook" name="facebook" placeholder="https://facebook.com/username" value="<?= val($info, 'facebook') ?>">
        </div>
        
        <!-- Profile Statistics -->
        <h3 style="margin-top: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; font-size: 1.1rem;"><i class="fas fa-chart-bar"></i> Dynamic Statistics Counters</h3>
        <div class="form-row-2">
            <div class="form-group">
                <label for="gpa">Academic GPA / Grade (e.g. 3.90)</label>
                <input type="text" id="gpa" name="gpa" placeholder="e.g. 3.90" value="<?= val($info, 'gpa') ?>">
            </div>
            <div class="form-group">
                <label for="coffee">Cups of Coffee consumed</label>
                <input type="number" id="coffee" name="coffee" placeholder="e.g. 250" value="<?= val($info, 'coffee') ?>">
            </div>
        </div>
        
        <button type="submit" name="save_profile" class="btn-admin btn-admin-primary" style="margin-top: 1.5rem;">
            Save Profile Changes <i class="fas fa-save"></i>
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
