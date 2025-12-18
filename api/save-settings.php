<?php
/**
 * ZYN Trade System - Save Settings API
 * Saves individual robot settings via AJAX
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

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

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid CSRF token'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];
$setting = $_POST['setting'] ?? '';
$value = $_POST['value'] ?? '';

// Whitelist of allowed settings
$allowedSettings = [
    'robot_enabled',
    'strategies',
    'risk_level',
    'trade_amount',
    'schedule_mode',
    'schedule_start_time',
    'schedule_end_time',
    'schedule_sessions',
    'schedule_per_day',
    'take_profit_target',
    'max_loss_limit',
    'resume_behavior',
    'notification_enabled',
    'sound_enabled'
];

if (!in_array($setting, $allowedSettings)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid setting'
    ]);
    exit;
}

try {
    global $pdo;

    // Check if settings exist for user
    $stmt = $pdo->prepare("SELECT id FROM robot_settings WHERE user_id = ?");
    $stmt->execute([$userId]);

    if ($stmt->fetch()) {
        // Update existing setting
        $stmt = $pdo->prepare("UPDATE robot_settings SET {$setting} = ?, updated_at = NOW() WHERE user_id = ?");
        $stmt->execute([$value, $userId]);
    } else {
        // Create new settings row with this setting
        $stmt = $pdo->prepare("INSERT INTO robot_settings (user_id, {$setting}, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
        $stmt->execute([$userId, $value]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Setting saved'
    ]);

} catch (PDOException $e) {
    error_log("Save settings error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
}
