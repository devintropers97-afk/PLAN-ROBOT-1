<?php
/**
 * ZYN Trade System - Trigger Auto-Pause API
 * Called by Robot Engine when TP/SL is reached
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Validate Robot API Key
requireRobotApiKey();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get request data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$userId = isset($data['user_id']) ? (int)$data['user_id'] : 0;
$reason = $data['reason'] ?? 'unknown';

if (!$userId) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID required'
    ]);
    exit;
}

// Valid reasons
$validReasons = ['tp_reached', 'ml_reached', 'take_profit', 'max_loss', 'manual', 'error', 'maintenance'];
if (!in_array($reason, $validReasons)) {
    $reason = 'manual';
}

try {
    $pdo = getDBConnection();

    // Update robot settings
    $stmt = $pdo->prepare("
        UPDATE robot_settings
        SET
            robot_enabled = 0,
            auto_pause_triggered = 1,
            auto_pause_reason = ?,
            auto_pause_time = NOW(),
            updated_at = NOW()
        WHERE user_id = ?
    ");
    $stmt->execute([$reason, $userId]);

    // Update robot status
    $stmt = $pdo->prepare("
        UPDATE robot_status
        SET
            status = 'paused',
            updated_at = NOW()
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);

    // Create notification
    $title = $reason === 'tp_reached' || $reason === 'take_profit'
        ? 'Take Profit Reached!'
        : ($reason === 'ml_reached' || $reason === 'max_loss'
            ? 'Max Loss Reached!'
            : 'Robot Paused');

    $message = $reason === 'tp_reached' || $reason === 'take_profit'
        ? 'Congratulations! Your daily take profit target has been reached. Robot has been paused.'
        : ($reason === 'ml_reached' || $reason === 'max_loss'
            ? 'Your daily maximum loss limit has been reached. Robot has been paused to protect your capital.'
            : 'Robot has been automatically paused.');

    createNotification($userId, 'auto_pause', $message, $title);

    // Log activity
    logActivity($userId, 'auto_pause_triggered', "Robot auto-paused: {$reason}");

    echo json_encode([
        'success' => true,
        'message' => 'Auto-pause triggered successfully',
        'reason' => $reason,
        'timestamp' => date('c')
    ]);

} catch (PDOException $e) {
    error_log("Trigger auto-pause error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
}
