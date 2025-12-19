<?php
/**
 * ZYN Trade System - Stop All Trades API
 * Emergency stop for robot trading
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

$userId = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();

    // Disable robot
    $stmt = $pdo->prepare("UPDATE robot_settings SET robot_enabled = 0, updated_at = NOW() WHERE user_id = ?");
    $stmt->execute([$userId]);

    // Log the action
    logActivity($userId, 'robot_stopped', 'User initiated emergency stop');

    echo json_encode([
        'success' => true,
        'message' => 'Robot stopped successfully'
    ]);

} catch (PDOException $e) {
    error_log("Stop all error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to stop robot'
    ]);
}
