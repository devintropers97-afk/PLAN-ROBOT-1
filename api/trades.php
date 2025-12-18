<?php
/**
 * API: Get Trade History
 * Returns user's trade history with optional filtering
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
$period = intval($_GET['period'] ?? 7);

// Validate period
if ($period < 1 || $period > 365) {
    $period = 7;
}

try {
    $trades = getRecentTrades($userId, 100); // Get more trades for filtering

    // Filter by date
    $cutoffDate = date('Y-m-d H:i:s', strtotime("-$period days"));
    $filteredTrades = array_filter($trades, function($trade) use ($cutoffDate) {
        return $trade['created_at'] >= $cutoffDate;
    });

    // Format response
    $formattedTrades = array_map(function($trade) {
        return [
            'id' => $trade['id'],
            'strategy' => $trade['strategy'] ?? 'Unknown',
            'strategy_id' => $trade['strategy_id'] ?? '',
            'asset' => $trade['asset'] ?? 'EUR/USD',
            'timeframe' => $trade['timeframe'] ?? '15M',
            'direction' => $trade['direction'] ?? 'call',
            'amount' => floatval($trade['amount'] ?? 0),
            'result' => $trade['result'] ?? 'pending',
            'profit_loss' => floatval($trade['profit_loss'] ?? 0),
            'created_at' => $trade['created_at']
        ];
    }, array_values($filteredTrades));

    echo json_encode([
        'success' => true,
        'period' => $period,
        'count' => count($formattedTrades),
        'trades' => $formattedTrades
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching trades'
    ]);
}
