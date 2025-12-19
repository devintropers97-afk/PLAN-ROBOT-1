<?php
/**
 * ZYN Trade System - Day Stats API
 * Returns detailed statistics for a specific day
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

$userId = $_SESSION['user_id'];
$date = isset($_GET['date']) ? cleanInput($_GET['date']) : date('Y-m-d');

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid date format'
    ]);
    exit;
}

try {
    $pdo = getDBConnection();

    // Get daily stats
    $stats = getDailyStats($userId, $date);

    // Get trades for this day
    $stmt = $pdo->prepare("
        SELECT strategy, asset, amount, result, profit_loss, created_at
        FROM trades
        WHERE user_id = ? AND DATE(created_at) = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$userId, $date]);
    $trades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'date' => date('d M Y', strtotime($date)),
        'total_trades' => $stats['total_trades'] ?? 0,
        'wins' => $stats['wins'] ?? 0,
        'losses' => $stats['losses'] ?? 0,
        'pnl' => (float)($stats['total_pnl'] ?? 0),
        'winrate' => $stats['total_trades'] > 0 ? round(($stats['wins'] / $stats['total_trades']) * 100, 1) : 0,
        'trades' => $trades
    ]);

} catch (PDOException $e) {
    error_log("Day stats error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch stats'
    ]);
}
