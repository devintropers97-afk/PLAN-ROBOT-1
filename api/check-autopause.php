<?php
/**
 * API: Check Auto-Pause
 * Endpoint untuk cek apakah robot harus auto-pause
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$userId = $_GET['user_id'] ?? '';

if (empty($userId)) {
    http_response_code(400);
    echo json_encode(['error' => 'user_id required']);
    exit;
}

try {
    $pdo = getDBConnection();

    // Get user settings
    $stmt = $pdo->prepare("
        SELECT take_profit, max_loss, is_active, auto_pause_triggered
        FROM robot_settings
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        echo json_encode(['should_pause' => false, 'reason' => null]);
        exit;
    }

    // Already triggered
    if ($settings['auto_pause_triggered']) {
        echo json_encode(['should_pause' => true, 'reason' => 'already_triggered']);
        exit;
    }

    // Robot not active
    if (!$settings['is_active']) {
        echo json_encode(['should_pause' => true, 'reason' => 'robot_inactive']);
        exit;
    }

    // Get today's profit/loss
    $todayStmt = $pdo->prepare("
        SELECT
            COALESCE(SUM(profit), 0) as today_profit,
            COUNT(*) as today_trades,
            SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as today_wins,
            SUM(CASE WHEN result = 'loss' THEN 1 ELSE 0 END) as today_losses
        FROM trades
        WHERE user_id = ? AND DATE(created_at) = CURDATE()
    ");
    $todayStmt->execute([$userId]);
    $today = $todayStmt->fetch(PDO::FETCH_ASSOC);

    $todayProfit = floatval($today['today_profit'] ?? 0);
    $shouldPause = false;
    $reason = null;

    // Check Take Profit
    if ($settings['take_profit'] > 0 && $todayProfit >= $settings['take_profit']) {
        $shouldPause = true;
        $reason = 'take_profit';
    }

    // Check Max Loss
    if ($settings['max_loss'] > 0 && $todayProfit <= -$settings['max_loss']) {
        $shouldPause = true;
        $reason = 'max_loss';
    }

    // Check weekend
    $dayOfWeek = date('N'); // 6 = Saturday, 7 = Sunday
    if ($dayOfWeek >= 6) {
        $shouldPause = true;
        $reason = 'weekend';
    }

    echo json_encode([
        'should_pause' => $shouldPause,
        'reason' => $reason,
        'today_profit' => $todayProfit,
        'today_trades' => intval($today['today_trades']),
        'today_wins' => intval($today['today_wins']),
        'today_losses' => intval($today['today_losses'])
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
