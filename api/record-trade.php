<?php
/**
 * API: Record Trade
 * Endpoint untuk merekam trade dari robot
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['user_id', 'asset', 'direction', 'amount'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
        exit;
    }
}

try {
    $pdo = getDBConnection();

    // Insert trade
    $stmt = $pdo->prepare("
        INSERT INTO trades (
            user_id, asset, direction, amount, strategy_id, strategy,
            confidence, indicators, result, profit, timeframe,
            entry_price, exit_price, expiry, created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
        )
    ");

    $stmt->execute([
        $input['user_id'],
        $input['asset'],
        strtolower($input['direction']),
        $input['amount'],
        $input['strategy_id'] ?? null,
        $input['strategy_name'] ?? 'Unknown',
        $input['confidence'] ?? 0,
        json_encode($input['indicators'] ?? []),
        $input['result'] ?? 'pending',
        $input['profit'] ?? 0,
        $input['timeframe'] ?? '15M',
        $input['entry_price'] ?? null,
        $input['exit_price'] ?? null,
        $input['expiry'] ?? null
    ]);

    $tradeId = $pdo->lastInsertId();

    // Update user stats
    $updateStats = $pdo->prepare("
        UPDATE users SET
            total_trades = total_trades + 1,
            updated_at = NOW()
        WHERE id = ?
    ");
    $updateStats->execute([$input['user_id']]);

    echo json_encode([
        'success' => true,
        'trade_id' => $tradeId
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
