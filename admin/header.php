<?php
/**
 * Common Admin Dashboard Header
 * Handles session checking, database connection, and sidebar navigation.
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

// Active page helper
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Portfolio</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #090d16;
            --bg-card: #111827;
            --bg-input: rgba(255, 255, 255, 0.03);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-primary: #f3f4f6;
            --text-secondary: #9ca3af;
            --primary-color: #6366f1;
            --secondary-color: #a855f7;
            --gradient-primary: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            
            --sidebar-width: 260px;
            --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar Navigation */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--bg-card);
            border-right: 1px solid var(--border-color);
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            padding: 2rem 1.5rem;
            z-index: 10;
        }

        .sidebar-brand {
            font-size: 1.4rem;
            font-weight: 800;
            margin-bottom: 2.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
        }

        .sidebar-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            flex-grow: 1;
        }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.8rem 1rem;
            color: var(--text-secondary);
            font-weight: 500;
            border-radius: 8px;
            text-decoration: none;
            transition: var(--transition-smooth);
        }

        .menu-link:hover, .menu-link.active {
            color: #ffffff;
            background-color: rgba(99, 102, 241, 0.12);
        }

        .menu-link.active {
            border-left: 3px solid var(--primary-color);
        }

        .menu-link-logout {
            color: #f87171;
        }

        .menu-link-logout:hover {
            background-color: rgba(239, 68, 68, 0.1);
        }

        /* Main Content Container */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            padding: 2.5rem 3rem;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .header-title h1 {
            font-size: 1.8rem;
            font-weight: 700;
        }

        .header-title p {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: 0.2rem;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        .btn-view-site {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
            font-weight: 600;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-primary);
            text-decoration: none;
            transition: var(--transition-smooth);
        }

        .btn-view-site:hover {
            background-color: var(--border-color);
        }

        /* Shared Dashboard Forms & Card Components */
        .admin-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            margin-bottom: 2rem;
        }

        .admin-card h2 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .admin-form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .form-group input, .form-group textarea, .form-group select {
            padding: 0.75rem 1rem;
            background-color: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.95rem;
            transition: var(--transition-smooth);
        }

        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        /* Buttons styling */
        .btn-admin {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.7rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            transition: var(--transition-smooth);
            text-decoration: none;
        }

        .btn-admin-primary {
            background: var(--gradient-primary);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.2);
        }

        .btn-admin-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(99, 102, 241, 0.35);
        }

        .btn-admin-danger {
            background-color: #ef4444;
            color: #ffffff;
        }

        .btn-admin-danger:hover {
            background-color: #dc2626;
        }

        /* Tables list styling */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            text-align: left;
        }

        .admin-table th, .admin-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .admin-table th {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .admin-table td {
            color: var(--text-primary);
            font-size: 0.95rem;
        }

        .admin-table tr:hover {
            background-color: rgba(255, 255, 255, 0.01);
        }

        .action-btns {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            width: 2rem;
            height: 2rem;
            border-radius: 4px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 0.9rem;
            border: 1px solid var(--border-color);
            background-color: var(--bg-dark);
            color: var(--text-secondary);
            cursor: pointer;
            transition: var(--transition-smooth);
            text-decoration: none;
        }

        .action-btn-edit:hover {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .action-btn-delete:hover {
            color: #ef4444;
            border-color: #ef4444;
        }

        /* Alerts in dashboard */
        .alert-admin {
            padding: 0.9rem 1.2rem;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-admin-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .alert-admin-error {
            background-color: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
                padding: 2rem 0.5rem;
                align-items: center;
            }
            .sidebar-brand {
                font-size: 1rem;
                margin-bottom: 2rem;
            }
            .sidebar-brand span {
                display: none;
            }
            .menu-link span {
                display: none;
            }
            .menu-link {
                justify-content: center;
                padding: 0.8rem;
                width: 44px;
                height: 44px;
            }
            .main-wrapper {
                margin-left: 70px;
                width: calc(100% - 70px);
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-user-cog"></i> <span>Dashboard</span>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php" class="menu-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i> <span>Overview</span>
                </a>
            </li>
            <li>
                <a href="manage_profile.php" class="menu-link <?= $current_page === 'manage_profile.php' ? 'active' : '' ?>">
                    <i class="fas fa-user-edit"></i> <span>Manage Profile</span>
                </a>
            </li>
            <li>
                <a href="manage_education.php" class="menu-link <?= $current_page === 'manage_education.php' ? 'active' : '' ?>">
                    <i class="fas fa-graduation-cap"></i> <span>Education</span>
                </a>
            </li>
            <li>
                <a href="manage_skills.php" class="menu-link <?= $current_page === 'manage_skills.php' ? 'active' : '' ?>">
                    <i class="fas fa-brain"></i> <span>Skills</span>
                </a>
            </li>
            <li>
                <a href="manage_projects.php" class="menu-link <?= $current_page === 'manage_projects.php' ? 'active' : '' ?>">
                    <i class="fas fa-folder-open"></i> <span>Projects</span>
                </a>
            </li>
            <li>
                <a href="manage_sections.php" class="menu-link <?= $current_page === 'manage_sections.php' ? 'active' : '' ?>">
                    <i class="fas fa-layer-group"></i> <span>Custom Sections</span>
                </a>
            </li>
            <li>
                <a href="manage_messages.php" class="menu-link <?= $current_page === 'manage_messages.php' ? 'active' : '' ?>">
                    <i class="fas fa-envelope"></i> <span>Messages</span>
                </a>
            </li>
            <li style="margin-top: auto;">
                <a href="logout.php" class="menu-link menu-link-logout">
                    <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Content Wrapper -->
    <div class="main-wrapper">
        <header class="header-bar">
            <div class="header-title">
                <h1>
                    <?php
                    // Display title based on page
                    switch($current_page) {
                        case 'dashboard.php': echo 'System Overview'; break;
                        case 'manage_profile.php': echo 'Personal Profile Details'; break;
                        case 'manage_education.php': echo 'Manage Education Details'; break;
                        case 'manage_skills.php': echo 'Manage Skills'; break;
                        case 'manage_projects.php': echo 'Manage Project Catalog'; break;
                        case 'manage_sections.php': echo 'Manage Custom Dynamic Sections'; break;
                        case 'manage_messages.php': echo 'Contact Messages Inbox'; break;
                        default: echo 'Admin Panel';
                    }
                    ?>
                </h1>
                <p>Welcome back, <?= htmlspecialchars($_SESSION['admin_username']) ?>!</p>
            </div>
            <div class="header-actions">
                <a href="../index.html" class="btn-view-site" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a>
            </div>
        </header>
        
        <main>
