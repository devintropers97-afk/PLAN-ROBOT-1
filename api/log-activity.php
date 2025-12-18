<?php
/**
 * ZYN Trade System - Log Activity API
 * Records robot activities in activity_log table
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

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
$activity = $data['activity'] ?? '';
$details = $data['details'] ?? '';

if (!$userId || !$activity) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID and activity required'
    ]);
    exit;
}

try {
    global $pdo;

    // Parse details if string
    $detailsJson = is_string($details) ? $details : json_encode($details);

    // Insert activity log
    $stmt = $pdo->prepare("
        INSERT INTO activity_log (user_id, action, description, ip_address, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $userId,
        $activity,
        $detailsJson,
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Activity logged',
        'log_id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    // Silent fail for logging - don't break robot operation
    echo json_encode([
        'success' => false,
        'message' => 'Log failed'
    ]);
}
