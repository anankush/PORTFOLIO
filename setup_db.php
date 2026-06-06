<?php
/**
 * Database Setup & Seeder Script
 * Upload this file and visit: http://yourdomain.com/setup_db.php
 * WARNING: Make sure to delete this file from the server after running!
 */

// Enable error reporting for installation debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Require database connection
require_once __DIR__ . '/db.php';

try {
    // 1. Read database.sql schema
    $sql_file = __DIR__ . '/database.sql';
    if (!file_exists($sql_file)) {
        die("Error: database.sql file not found in the root directory!");
    }
    
    $sql = file_get_contents($sql_file);
    
    // 2. Execute SQL schema definitions
    $pdo->exec($sql);
    echo "✔ Database schema tables checked/created successfully.<br>";
    
    // 3. Seed default admin user (username: admin, password: admin123)
    $stmt = $pdo->query("SELECT COUNT(*) FROM `users`");
    if ($stmt->fetchColumn() == 0) {
        $hashed_password = password_hash('admin123', PASSWORD_BCRYPT);
        $insertUser = $pdo->prepare("INSERT INTO `users` (`username`, `password`) VALUES (?, ?)");
        $insertUser->execute(['admin', $hashed_password]);
        echo "✔ Default admin user created. (Username: <strong>admin</strong>, Password: <strong>admin123</strong>). Please login and change it immediately!<br>";
    } else {
        echo "ℹ Admin user table is not empty. Seeding skipped for users.<br>";
    }
    
    // 4. Seed default portfolio info
    $stmt = $pdo->query("SELECT COUNT(*) FROM `portfolio_info`");
    if ($stmt->fetchColumn() == 0) {
        $default_info = [
            'name' => 'Anik Sen',
            'title' => 'Web Developer & Student',
            'bio_short' => 'I am a computer science student passionate about building modern, clean, and interactive web applications.',
            'bio_full' => 'Hello! I am a passionate student exploring new web technologies. Coding is not just part of my academic studies, it is also my hobby. I enjoy writing clean code and creating beautiful user interfaces with fluid animations.',
            'email' => 'anik.student@example.com',
            'phone' => '+880 1712-345678',
            'location' => 'Dhaka, Bangladesh',
            'github' => 'https://github.com/',
            'linkedin' => 'https://linkedin.com/',
            'facebook' => 'https://facebook.com/',
            'resume_url' => '#',
            'profile_picture' => ''
        ];
        
        $insertInfo = $pdo->prepare("INSERT INTO `portfolio_info` (`meta_key`, `meta_value`) VALUES (?, ?)");
        foreach ($default_info as $key => $value) {
            $insertInfo->execute([$key, $value]);
        }
        echo "✔ Default portfolio details seeded successfully.<br>";
    } else {
        echo "ℹ Portfolio info table is not empty. Seeding skipped.<br>";
    }
    
    // 5. Seed default education
    $stmt = $pdo->query("SELECT COUNT(*) FROM `education`");
    if ($stmt->fetchColumn() == 0) {
        $default_education = [
            [
                'degree' => 'Diploma in Computer Science & Technology',
                'institution' => 'Polytechnic Institute',
                'year' => '2023 - Present',
                'result' => 'CGPA: 3.85 (Ongoing)'
            ],
            [
                'degree' => 'Secondary School Certificate (SSC)',
                'institution' => 'High School',
                'year' => '2022',
                'result' => 'GPA: 5.00'
            ]
        ];
        
        $insertEdu = $pdo->prepare("INSERT INTO `education` (`degree`, `institution`, `year`, `result`) VALUES (?, ?, ?, ?)");
        foreach ($default_education as $edu) {
            $insertEdu->execute([$edu['degree'], $edu['institution'], $edu['year'], $edu['result']]);
        }
        echo "✔ Default education details seeded.<br>";
    } else {
        echo "ℹ Education table is not empty. Seeding skipped.<br>";
    }
    
    // 6. Seed default skills
    $stmt = $pdo->query("SELECT COUNT(*) FROM `skills`");
    if ($stmt->fetchColumn() == 0) {
        $default_skills = [
            ['Frontend', 'HTML5', 95],
            ['Frontend', 'CSS3', 90],
            ['Frontend', 'JavaScript', 85],
            ['Backend', 'PHP', 80],
            ['Backend', 'MySQL', 75],
            ['Tools', 'Git & GitHub', 85],
            ['Tools', 'VS Code', 90]
        ];
        
        $insertSkill = $pdo->prepare("INSERT INTO `skills` (`category`, `name`, `proficiency`) VALUES (?, ?, ?)");
        foreach ($default_skills as $skill) {
            $insertSkill->execute([$skill[0], $skill[1], $skill[2]]);
        }
        echo "✔ Default skills seeded.<br>";
    } else {
        echo "ℹ Skills table is not empty. Seeding skipped.<br>";
    }
    
    // 7. Seed default projects
    $stmt = $pdo->query("SELECT COUNT(*) FROM `projects`");
    if ($stmt->fetchColumn() == 0) {
        $default_projects = [
            [
                'title' => 'E-Commerce Website Mockup',
                'description' => 'A clean and responsive front-end design for an online store featuring product sliders, filter options, and interactive cart animations.',
                'image' => '',
                'tools' => 'HTML, CSS, JavaScript',
                'github_link' => 'https://github.com/',
                'live_link' => '#'
            ],
            [
                'title' => 'Student Management System',
                'description' => 'A PHP/MySQL web application built for schools to track student enrollments, attendance records, and grade results.',
                'image' => '',
                'tools' => 'PHP, MySQL, Bootstrap',
                'github_link' => 'https://github.com/',
                'live_link' => '#'
            ]
        ];
        
        $insertProject = $pdo->prepare("INSERT INTO `projects` (`title`, `description`, `image`, `tools`, `github_link`, `live_link`) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($default_projects as $proj) {
            $insertProject->execute([$proj['title'], $proj['description'], $proj['image'], $proj['tools'], $proj['github_link'], $proj['live_link']]);
        }
        echo "✔ Default projects seeded.<br>";
    } else {
        echo "ℹ Projects table is not empty. Seeding skipped.<br>";
    }
    
    echo "<h2>🎉 Setup and Seeding completed successfully!</h2>";
    echo "<p style='color:red; font-weight:bold;'>CRITICAL SECURITY WARNING: Delete this file (`setup_db.php`) from your server right now!</p>";
    
} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}
