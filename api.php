<?php
/**
 * Central API Gateway for Portfolio
 * Handles public data retrieval and contact message submissions.
 * Returns standard JSON responses.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/db.php';

$action = $_GET['action'] ?? '';

// Helper function to return JSON responses
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

if ($action === 'get_portfolio') {
    $response = [
        'profile' => [],
        'education' => [],
        'skills' => [],
        'projects' => [],
        'custom_sections' => []
    ];
    
    try {
        // 1. Fetch Profile/Portfolio info
        $stmt = $pdo->query("SELECT * FROM `portfolio_info`");
        while ($row = $stmt->fetch()) {
            $response['profile'][$row['meta_key']] = $row['meta_value'];
        }
        
        // Fill profile fallbacks
        $defaults = [
            'name' => 'Your Name',
            'title' => 'Web Developer & Student',
            'bio_short' => 'Welcome to my portfolio.',
            'bio_full' => '',
            'email' => '',
            'phone' => '',
            'location' => '',
            'github' => '',
            'linkedin' => '',
            'facebook' => '',
            'resume_url' => '#',
            'profile_picture' => ''
        ];
        foreach ($defaults as $key => $val) {
            if (!isset($response['profile'][$key])) {
                $response['profile'][$key] = $val;
            }
        }
        
        // 2. Fetch Education
        $stmt = $pdo->query("SELECT * FROM `education` ORDER BY `id` DESC");
        $response['education'] = $stmt->fetchAll();
        
        // 3. Fetch Skills
        $stmt = $pdo->query("SELECT * FROM `skills` ORDER BY `category`, `proficiency` DESC");
        $skills = $stmt->fetchAll();
        // Group skills by category
        $grouped_skills = [];
        foreach ($skills as $skill) {
            $grouped_skills[$skill['category']][] = $skill;
        }
        $response['skills'] = $grouped_skills;
        
        // 4. Fetch Projects
        $stmt = $pdo->query("SELECT * FROM `projects` ORDER BY `id` DESC");
        $response['projects'] = $stmt->fetchAll();
        
        // 5. Fetch Custom Dynamic Sections and their items
        $stmt = $pdo->query("SELECT * FROM `custom_sections` ORDER BY `section_order` ASC");
        $sections = $stmt->fetchAll();
        
        $custom_sections = [];
        foreach ($sections as $sec) {
            $item_stmt = $pdo->prepare("SELECT * FROM `custom_section_items` WHERE `section_id` = ? ORDER BY `id` ASC");
            $item_stmt->execute([$sec['id']]);
            $sec['items'] = $item_stmt->fetchAll();
            $custom_sections[] = $sec;
        }
        $response['custom_sections'] = $custom_sections;
        
        sendResponse([
            'status' => 'success',
            'data' => $response
        ]);
        
    } catch (PDOException $e) {
        sendResponse([
            'status' => 'error',
            'message' => 'Failed to fetch portfolio data: ' . $e->getMessage()
        ], 500);
    }
} 

elseif ($action === 'send_message') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse([
            'status' => 'error',
            'message' => 'Invalid request method.'
        ], 405);
    }
    
    // Support standard POST data as well as raw JSON input
    $rawInput = file_get_contents('php://input');
    $jsonData = json_decode($rawInput, true);
    
    $name = trim($_POST['name'] ?? $jsonData['name'] ?? '');
    $email = trim($_POST['email'] ?? $jsonData['email'] ?? '');
    $subject = trim($_POST['subject'] ?? $jsonData['subject'] ?? '');
    $message = trim($_POST['message'] ?? $jsonData['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        sendResponse([
            'status' => 'error',
            'message' => 'All fields (name, email, subject, message) are required.'
        ], 400);
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse([
            'status' => 'error',
            'message' => 'Please provide a valid email address.'
        ], 400);
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO `messages` (`name`, `email`, `subject`, `message`) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        
        sendResponse([
            'status' => 'success',
            'message' => 'Your message was sent successfully! I will get back to you soon.'
        ]);
    } catch (PDOException $e) {
        sendResponse([
            'status' => 'error',
            'message' => 'Failed to save message due to a database error.'
        ], 500);
    }
} 

else {
    sendResponse([
        'status' => 'error',
        'message' => 'Action parameter is invalid or missing.'
    ], 400);
}
