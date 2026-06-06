<?php
/**
 * Admin Panel Dashboard Home
 * Displays stats and direct action routes.
 */
require_once __DIR__ . '/header.php';

// Fetch stats
$count_projects = 0;
$count_skills = 0;
$count_edu = 0;
$count_sections = 0;
$count_messages = 0;

try {
    $count_projects = $pdo->query("SELECT COUNT(*) FROM `projects`")->fetchColumn();
    $count_skills = $pdo->query("SELECT COUNT(*) FROM `skills`")->fetchColumn();
    $count_edu = $pdo->query("SELECT COUNT(*) FROM `education`")->fetchColumn();
    $count_sections = $pdo->query("SELECT COUNT(*) FROM `custom_sections`")->fetchColumn();
    $count_messages = $pdo->query("SELECT COUNT(*) FROM `messages`")->fetchColumn();
} catch (PDOException $e) {
    // Database error or tables don't exist yet
}
?>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }
    
    .stat-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.2rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }
    
    .stat-icon {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 10px;
        background: rgba(99, 102, 241, 0.1);
        color: var(--primary-color);
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 1.5rem;
        border: 1px solid rgba(99, 102, 241, 0.2);
    }
    
    .stat-card:nth-child(2) .stat-icon {
        background: rgba(168, 85, 247, 0.1);
        color: var(--secondary-color);
        border-color: rgba(168, 85, 247, 0.2);
    }
    
    .stat-card:nth-child(3) .stat-icon {
        background: rgba(59, 130, 246, 0.1);
        color: var(--accent-color);
        border-color: rgba(59, 130, 246, 0.2);
    }
    
    .stat-card:nth-child(4) .stat-icon {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
        border-color: rgba(16, 185, 129, 0.2);
    }

    .stat-card:nth-child(5) .stat-icon {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
        border-color: rgba(245, 158, 11, 0.2);
    }
    
    .stat-info h3 {
        font-size: 0.85rem;
        text-transform: uppercase;
        color: var(--text-secondary);
        font-weight: 600;
        letter-spacing: 0.05em;
    }
    
    .stat-info p {
        font-size: 1.8rem;
        font-weight: 700;
        margin-top: 0.2rem;
    }
    
    .quick-links {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
    
    @media (max-width: 768px) {
        .quick-links {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-folder-open"></i></div>
        <div class="stat-info">
            <h3>Projects</h3>
            <p><?= (int)$count_projects ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-brain"></i></div>
        <div class="stat-info">
            <h3>Skills</h3>
            <p><?= (int)$count_skills ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
        <div class="stat-info">
            <h3>Education</h3>
            <p><?= (int)$count_edu ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
        <div class="stat-info">
            <h3>Custom Sections</h3>
            <p><?= (int)$count_sections ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-envelope"></i></div>
        <div class="stat-info">
            <h3>Messages</h3>
            <p><?= (int)$count_messages ?></p>
        </div>
    </div>
</div>

<div class="quick-links">
    <div class="admin-card">
        <h2><i class="fas fa-magic"></i> Quick Management Actions</h2>
        <p style="color: var(--text-secondary); margin-bottom: 1.5rem; font-size: 0.95rem;">
            Jump straight into updating specific sections of your website or review incoming messages.
        </p>
        <div style="display: flex; flex-direction: column; gap: 0.8rem;">
            <a href="manage_profile.php" class="btn-admin btn-admin-primary" style="justify-content: flex-start;">
                <i class="fas fa-user-edit"></i> Edit Profile Information
            </a>
            <a href="manage_projects.php" class="btn-admin btn-admin-primary" style="justify-content: flex-start; background: var(--secondary-color);">
                <i class="fas fa-plus"></i> Add a New Project
            </a>
            <a href="manage_sections.php" class="btn-admin btn-admin-primary" style="justify-content: flex-start; background: var(--accent-color);">
                <i class="fas fa-layer-group"></i> Create Custom Dynamic Section
            </a>
        </div>
    </div>
    
    <div class="admin-card">
        <h2><i class="fas fa-shield-alt"></i> Security & Deployment Info</h2>
        <div style="font-size: 0.95rem; line-height: 1.6; color: var(--text-secondary);">
            <p style="margin-bottom: 1rem;">
                <strong>Database Setup:</strong> Your local database name is configured as <code style="color: var(--primary-color);">if0_41843901_portfolio</code>.
            </p>
            <p style="margin-bottom: 1rem;">
                <strong>GitHub Actions Deployment:</strong> All commits pushed to <code>main</code> or <code>master</code> will automatically run the deployment workflow and upload your changes to InfinityFree hosting.
            </p>
            <p style="color: #f87171; font-weight: 500;">
                <i class="fas fa-exclamation-triangle"></i> Important: Ensure you have deleted the file <code>setup_db.php</code> from the remote server after initializing.
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
