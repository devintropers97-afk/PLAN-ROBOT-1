<?php
/**
 * ZYN Trade System - User Stats API
 * Returns user trading statistics for Robot Engine
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Validate Robot API Key
requireRobotApiKey();

// Get user_id from request
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if (!$userId) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID required'
    ]);
    exit;
}

try {
    global $pdo;

    // Get user info
    $stmt = $pdo->prepare("SELECT id, fullname, package, total_trades, wins, losses, total_profit FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }

    // Get today's stats
    $todayStats = getDailyStats($userId, date('Y-m-d'));

    // Get this week's stats
    $weekStart = date('Y-m-d', strtotime('monday this week'));
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total_trades,
            SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
            SUM(CASE WHEN result = 'loss' THEN 1 ELSE 0 END) as losses,
            COALESCE(SUM(profit_loss), 0) as total_pnl
        FROM trades
        WHERE user_id = ? AND DATE(created_at) >= ?
    ");
    $stmt->execute([$userId, $weekStart]);
    $weekStats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calculate win rates
    $allTimeWinRate = $user['total_trades'] > 0
        ? round(($user['wins'] / $user['total_trades']) * 100, 1)
        : 0;

    $todayWinRate = $todayStats['total_trades'] > 0
        ? round(($todayStats['wins'] / $todayStats['total_trades']) * 100, 1)
        : 0;

    $weekWinRate = $weekStats['total_trades'] > 0
        ? round(($weekStats['wins'] / $weekStats['total_trades']) * 100, 1)
        : 0;

    echo json_encode([
        'success' => true,
        'user_id' => $userId,
        'all_time' => [
            'total_trades' => (int)$user['total_trades'],
            'wins' => (int)$user['wins'],
            'losses' => (int)$user['losses'],
            'total_pnl' => (float)$user['total_profit'],
            'win_rate' => $allTimeWinRate
        ],
        'today' => [
            'total_trades' => (int)$todayStats['total_trades'],
            'wins' => (int)$todayStats['wins'],
            'losses' => (int)$todayStats['losses'],
            'total_pnl' => (float)($todayStats['total_pnl'] ?? 0),
            'win_rate' => $todayWinRate
        ],
        'this_week' => [
            'total_trades' => (int)$weekStats['total_trades'],
            'wins' => (int)$weekStats['wins'],
            'losses' => (int)$weekStats['losses'],
            'total_pnl' => (float)$weekStats['total_pnl'],
            'win_rate' => $weekWinRate
        ]
    ]);

} catch (PDOException $e) {
    error_log("User stats API error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
}
