<?php
/**
 * Admin Login Page
 * Handles secure session authentication using database password hash verification.
 */
session_start();
require_once __DIR__ . '/../db.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if ($username !== '' && $password !== '') {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `users` WHERE `username` = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session id for security against session fixation
                session_regenerate_id(true);
                
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $user['username'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error_msg = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error_msg = 'Database error. Please try again.';
        }
    } else {
        $error_msg = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Portfolio</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-primary: #0a0e17;
            --bg-secondary: rgba(20, 27, 45, 0.65);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-primary: #f3f4f6;
            --text-secondary: #9ca3af;
            --primary-color: #6366f1;
            --secondary-color: #a855f7;
            --gradient-primary: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* Animated Blobs */
        .blobs {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.15;
            animation: rotateBlobs 20s infinite alternate ease-in-out;
        }

        .blob-1 {
            top: -10%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: var(--primary-color);
        }

        .blob-2 {
            bottom: -10%;
            right: -10%;
            width: 450px;
            height: 450px;
            background: var(--secondary-color);
            animation-delay: -5s;
        }

        @keyframes rotateBlobs {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(40px, -40px) scale(1.1); }
        }

        /* Glass Login Box */
        .login-card {
            background: var(--bg-secondary);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            width: 100%;
            max-width: 420px;
            padding: 3.5rem 2.5rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            text-align: center;
        }

        .login-card h2 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .login-card p {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: 2.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            text-align: left;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .form-group input {
            padding: 0.85rem 1rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .error-alert {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-align: left;
        }

        .btn-submit {
            width: 100%;
            padding: 0.9rem;
            background: var(--gradient-primary);
            color: #ffffff;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.35);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 1rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
        }

        .back-home {
            display: inline-block;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .back-home:hover {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <div class="login-card">
        <h2>Admin Panel</h2>
        <p>Login to manage your portfolio</p>
        
        <?php if ($error_msg !== ''): ?>
            <div class="error-alert">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error_msg) ?></span>
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            
            <button type="submit" name="login" class="btn-submit">Login <i class="fas fa-sign-in-alt"></i></button>
        </form>
        
        <a href="../index.html" class="back-home"><i class="fas fa-arrow-left"></i> Back to Portfolio</a>
    </div>
</body>
</html>
