<?php
/**
 * =========================================
 * ZYN TRADE API - Record Trade
 * =========================================
 *
 * Endpoint: POST /api/record-trade.php
 * Description: Records trades executed by the robot
 *
 * Request Body:
 * {
 *   "user_id": "int (required)",
 *   "asset": "string (required) - e.g., EUR/USD",
 *   "direction": "string (required) - call|put",
 *   "amount": "float (required)",
 *   "strategy_id": "int (optional)",
 *   "strategy_name": "string (optional)",
 *   "confidence": "float (optional) - 0-100",
 *   "indicators": "array (optional)",
 *   "result": "string (optional) - win|loss|pending|tie",
 *   "profit": "float (optional)",
 *   "timeframe": "string (optional) - e.g., 15M, 1H",
 *   "entry_price": "float (optional)",
 *   "exit_price": "float (optional)",
 *   "expiry": "datetime (optional)"
 * }
 */

// Security headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key, Authorization');
header('X-Content-Type-Options: nosniff');

// Include dependencies
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// API Version
define('API_VERSION', '1.0.1');

/**
 * Send JSON response
 */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Send error response
 */
function sendError($message, $code, $statusCode = 400) {
    sendResponse([
        'success' => false,
        'error' => $message,
        'code' => $code,
        'timestamp' => date('c')
    ], $statusCode);
}

/**
 * Validate trade direction
 */
function isValidDirection($direction) {
    $valid = ['call', 'put', 'buy', 'sell', 'up', 'down'];
    return in_array(strtolower($direction), $valid);
}

/**
 * Normalize trade direction
 */
function normalizeDirection($direction) {
    $direction = strtolower($direction);
    $mapping = [
        'call' => 'call',
        'put' => 'put',
        'buy' => 'call',
        'sell' => 'put',
        'up' => 'call',
        'down' => 'put'
    ];
    return $mapping[$direction] ?? $direction;
}

/**
 * Validate trade result
 */
function isValidResult($result) {
    $valid = ['win', 'loss', 'pending', 'tie', 'cancelled'];
    return in_array(strtolower($result), $valid);
}

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed. Use POST.', 'METHOD_NOT_ALLOWED', 405);
}

// Parse JSON input
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sendError('Invalid JSON payload', 'INVALID_JSON', 400);
}

// Validate required fields
$requiredFields = ['user_id', 'asset', 'direction', 'amount'];
$missingFields = [];

foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || (is_string($input[$field]) && trim($input[$field]) === '')) {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    sendError('Missing required fields: ' . implode(', ', $missingFields), 'MISSING_FIELDS', 400);
}

// Validate and sanitize inputs
$userId = filter_var($input['user_id'], FILTER_VALIDATE_INT);
if (!$userId || $userId <= 0) {
    sendError('Invalid user_id', 'INVALID_USER_ID', 400);
}

$asset = strtoupper(trim($input['asset']));
if (strlen($asset) < 3 || strlen($asset) > 20) {
    sendError('Invalid asset format', 'INVALID_ASSET', 400);
}

$direction = trim($input['direction']);
if (!isValidDirection($direction)) {
    sendError('Invalid direction. Use: call, put, buy, sell', 'INVALID_DIRECTION', 400);
}
$direction = normalizeDirection($direction);

$amount = floatval($input['amount']);
if ($amount <= 0) {
    sendError('Amount must be greater than 0', 'INVALID_AMOUNT', 400);
}

// Optional fields with validation
$strategyId = isset($input['strategy_id']) ? filter_var($input['strategy_id'], FILTER_VALIDATE_INT) : null;
$strategyName = trim($input['strategy_name'] ?? 'Unknown');
$confidence = isset($input['confidence']) ? min(100, max(0, floatval($input['confidence']))) : null;
$indicators = $input['indicators'] ?? [];
$result = isset($input['result']) && isValidResult($input['result']) ? strtolower($input['result']) : 'pending';
$profit = isset($input['profit']) ? floatval($input['profit']) : 0;
$timeframe = trim($input['timeframe'] ?? '15M');
$entryPrice = isset($input['entry_price']) ? floatval($input['entry_price']) : null;
$exitPrice = isset($input['exit_price']) ? floatval($input['exit_price']) : null;
$expiry = $input['expiry'] ?? null;

// Validate expiry date format if provided
if ($expiry) {
    $expiryTime = strtotime($expiry);
    if (!$expiryTime) {
        $expiry = null;
    } else {
        $expiry = date('Y-m-d H:i:s', $expiryTime);
    }
}

try {
    $pdo = getDBConnection();

    // Verify user exists and is active
    $userCheck = $pdo->prepare("
        SELECT id, status, package, total_trades
        FROM users
        WHERE id = :id
        LIMIT 1
    ");
    $userCheck->execute(['id' => $userId]);
    $user = $userCheck->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        sendError('User not found', 'USER_NOT_FOUND', 404);
    }

    if ($user['status'] !== 'active') {
        sendError('User account is not active', 'USER_INACTIVE', 403);
    }

    // Begin transaction
    $pdo->beginTransaction();

    try {
        // Insert trade record
        $stmt = $pdo->prepare("
            INSERT INTO trades (
                user_id,
                asset,
                direction,
                amount,
                strategy_id,
                strategy,
                confidence,
                indicators,
                result,
                profit,
                timeframe,
                entry_price,
                exit_price,
                expiry,
                ip_address,
                created_at
            ) VALUES (
                :user_id,
                :asset,
                :direction,
                :amount,
                :strategy_id,
                :strategy,
                :confidence,
                :indicators,
                :result,
                :profit,
                :timeframe,
                :entry_price,
                :exit_price,
                :expiry,
                :ip_address,
                NOW()
            )
        ");

        $stmt->execute([
            'user_id' => $userId,
            'asset' => $asset,
            'direction' => $direction,
            'amount' => $amount,
            'strategy_id' => $strategyId,
            'strategy' => $strategyName,
            'confidence' => $confidence,
            'indicators' => json_encode($indicators),
            'result' => $result,
            'profit' => $profit,
            'timeframe' => $timeframe,
            'entry_price' => $entryPrice,
            'exit_price' => $exitPrice,
            'expiry' => $expiry,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);

        $tradeId = $pdo->lastInsertId();

        // Update user statistics
        $updateStats = $pdo->prepare("
            UPDATE users SET
                total_trades = total_trades + 1,
                total_profit = total_profit + :profit,
                wins = wins + :is_win,
                losses = losses + :is_loss,
                last_trade_at = NOW(),
                updated_at = NOW()
            WHERE id = :user_id
        ");

        $isWin = ($result === 'win') ? 1 : 0;
        $isLoss = ($result === 'loss') ? 1 : 0;

        $updateStats->execute([
            'profit' => $profit,
            'is_win' => $isWin,
            'is_loss' => $isLoss,
            'user_id' => $userId
        ]);

        // Update daily statistics (optional - if table exists)
        try {
            $today = date('Y-m-d');
            $dailyStats = $pdo->prepare("
                INSERT INTO daily_stats (user_id, date, trades, wins, losses, profit)
                VALUES (:user_id, :date, 1, :wins, :losses, :profit)
                ON DUPLICATE KEY UPDATE
                    trades = trades + 1,
                    wins = wins + :wins,
                    losses = losses + :losses,
                    profit = profit + :profit
            ");
            $dailyStats->execute([
                'user_id' => $userId,
                'date' => $today,
                'wins' => $isWin,
                'losses' => $isLoss,
                'profit' => $profit
            ]);
        } catch (Exception $e) {
            // Daily stats table might not exist - ignore
        }

        // Commit transaction
        $pdo->commit();

        // Return success response
        sendResponse([
            'success' => true,
            'message' => 'Trade recorded successfully',
            'timestamp' => date('c'),
            'data' => [
                'trade_id' => (int)$tradeId,
                'user_id' => $userId,
                'asset' => $asset,
                'direction' => $direction,
                'amount' => $amount,
                'result' => $result,
                'profit' => $profit,
                'recorded_at' => date('Y-m-d H:i:s'),
                'user_total_trades' => $user['total_trades'] + 1
            ]
        ], 201);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    error_log('API Error [record-trade]: ' . $e->getMessage());
    sendError('Database error occurred', 'DATABASE_ERROR', 500);

} catch (Exception $e) {
    error_log('API Error [record-trade]: ' . $e->getMessage());
    sendError('Internal server error', 'SERVER_ERROR', 500);
}
