<?php
/**
 * API: Get User Balance
 * Returns placeholder balance (actual balance comes from OlympTrade)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // Get user's robot settings for last known balance
    $robotSettings = getRobotSettings($userId);

    // Note: Actual balance should come from OlympTrade API
    // This is a placeholder that returns the last recorded balance
    $balance = $robotSettings['last_balance'] ?? 0;

    // Get today's P&L
    $stats = getUserStats($userId, 1); // Last 1 day
    $todayPnl = $stats['total_pnl'] ?? 0;

    echo json_encode([
        'success' => true,
        'balance' => $balance,
        'today_pnl' => $todayPnl,
        'currency' => 'USD',
        'last_updated' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching balance'
    ]);
}
