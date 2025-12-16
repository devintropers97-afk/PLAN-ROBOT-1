<?php
/**
 * API: Update Trade Result
 * Endpoint untuk update hasil trade (win/loss/draw)
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

if (empty($input['trade_id']) || empty($input['result'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'trade_id and result required']);
    exit;
}

$validResults = ['win', 'loss', 'draw', 'tie'];
if (!in_array($input['result'], $validResults)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid result value']);
    exit;
}

try {
    $pdo = getDBConnection();

    // Update trade
    $stmt = $pdo->prepare("
        UPDATE trades SET
            result = ?,
            profit = ?,
            exit_price = ?,
            updated_at = NOW()
        WHERE id = ?
    ");

    $stmt->execute([
        $input['result'],
        $input['profit'] ?? 0,
        $input['exit_price'] ?? null,
        $input['trade_id']
    ]);

    // Get trade to update user stats
    $getTrade = $pdo->prepare("SELECT user_id, profit FROM trades WHERE id = ?");
    $getTrade->execute([$input['trade_id']]);
    $trade = $getTrade->fetch(PDO::FETCH_ASSOC);

    if ($trade) {
        // Update user statistics
        $winAdd = $input['result'] === 'win' ? 1 : 0;
        $lossAdd = $input['result'] === 'loss' ? 1 : 0;
        $profitAdd = $input['profit'] ?? 0;

        $updateUser = $pdo->prepare("
            UPDATE users SET
                win_trades = win_trades + ?,
                loss_trades = loss_trades + ?,
                total_profit = total_profit + ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $updateUser->execute([$winAdd, $lossAdd, $profitAdd, $trade['user_id']]);

        // Check for auto-pause conditions
        checkAutoPauseCondition($pdo, $trade['user_id']);
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

function checkAutoPauseCondition($pdo, $userId) {
    // Get user settings
    $stmt = $pdo->prepare("
        SELECT rs.*, u.total_profit
        FROM robot_settings rs
        JOIN users u ON rs.user_id = u.id
        WHERE rs.user_id = ?
    ");
    $stmt->execute([$userId]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$settings) return;

    // Get today's profit/loss
    $todayStmt = $pdo->prepare("
        SELECT SUM(profit) as today_profit
        FROM trades
        WHERE user_id = ? AND DATE(created_at) = CURDATE()
    ");
    $todayStmt->execute([$userId]);
    $todayResult = $todayStmt->fetch(PDO::FETCH_ASSOC);
    $todayProfit = $todayResult['today_profit'] ?? 0;

    $shouldPause = false;
    $reason = '';

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

    if ($shouldPause) {
        $pauseStmt = $pdo->prepare("
            UPDATE robot_settings SET
                is_active = 0,
                auto_pause_triggered = 1,
                auto_pause_reason = ?,
                updated_at = NOW()
            WHERE user_id = ?
        ");
        $pauseStmt->execute([$reason, $userId]);
    }
}
