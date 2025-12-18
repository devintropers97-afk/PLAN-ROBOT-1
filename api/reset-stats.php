<?php
/**
 * ZYN Trade System - Reset Daily Stats API
 * Resets daily statistics for the user
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
    global $pdo;

    // Reset today's stats in daily_stats table if exists
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("
        UPDATE daily_stats
        SET total_trades = 0, trades = 0, wins = 0, losses = 0, profit_loss = 0, profit = 0, win_rate = 0, updated_at = NOW()
        WHERE user_id = ? AND date = ?
    ");
    $stmt->execute([$userId, $today]);

    // Log the action
    logActivity($userId, 'stats_reset', 'User reset daily statistics');

    echo json_encode([
        'success' => true,
        'message' => 'Daily stats reset successfully'
    ]);

} catch (PDOException $e) {
    error_log("Reset stats error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to reset stats'
    ]);
}
