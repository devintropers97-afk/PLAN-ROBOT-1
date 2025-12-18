<?php
/**
 * ZYN Trade System - Resume Robot API
 * Handles manual resume of auto-paused robot
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/language.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated'
    ]);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

if ($action !== 'resume') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
    exit;
}

// Get current settings
$settings = getRobotSettings($userId);

if (!$settings) {
    echo json_encode([
        'success' => false,
        'message' => 'No settings found'
    ]);
    exit;
}

// Check if robot is actually paused
if (!$settings['auto_pause_triggered']) {
    echo json_encode([
        'success' => false,
        'message' => 'Robot is not auto-paused'
    ]);
    exit;
}

// Get resume info to check if manual resume is allowed
$resumeInfo = getResumeInfo($userId);

if (!$resumeInfo['can_resume']) {
    echo json_encode([
        'success' => false,
        'message' => $resumeInfo['message'] ?? 'Cannot resume yet'
    ]);
    exit;
}

// Attempt to resume robot
$success = resumeRobotManual($userId);

if ($success) {
    echo json_encode([
        'success' => true,
        'message' => __('api_robot_resumed')
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => __('api_robot_resume_failed')
    ]);
}
