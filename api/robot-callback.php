<?php
/**
 * =========================================================
 * ZYN TRADE SYSTEM - Robot Callback Handler
 * =========================================================
 *
 * Webhook endpoint untuk menerima callback dari Robot API
 * Robot akan memanggil endpoint ini setelah trade selesai
 *
 * Endpoint: POST /api/robot-callback.php
 *
 * Headers:
 *   X-API-Key: robot-callback-secret-key
 *   Content-Type: application/json
 *
 * Body:
 *   {
 *     "event": "trade_completed",
 *     "jobId": "uuid",
 *     "status": "completed|failed",
 *     "result": {...},
 *     "timestamp": 1234567890
 *   }
 */

require_once __DIR__ . '/../includes/config.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

// Verify API Key
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
$expectedKey = getenv('ROBOT_CALLBACK_KEY') ?: 'robot-callback-secret-key';

if (!hash_equals($expectedKey, $apiKey)) {
    error_log("[Robot Callback] Invalid API key from " . getClientIP());
    jsonResponse(['error' => 'Unauthorized'], 401);
}

// Get request body
$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody, true);

if (!$data) {
    jsonResponse(['error' => 'Invalid JSON body'], 400);
}

// Log incoming callback
error_log("[Robot Callback] Received: " . json_encode([
    'event' => $data['event'] ?? 'unknown',
    'jobId' => $data['jobId'] ?? null,
    'status' => $data['status'] ?? null
]));

// Handle different event types
$event = $data['event'] ?? '';

switch ($event) {
    case 'trade_completed':
        handleTradeCompleted($data);
        break;

    case 'trade_failed':
        handleTradeFailed($data);
        break;

    case 'login_success':
        handleLoginSuccess($data);
        break;

    case 'login_failed':
        handleLoginFailed($data);
        break;

    case 'session_expired':
        handleSessionExpired($data);
        break;

    case 'captcha_detected':
        handleCaptchaDetected($data);
        break;

    case 'balance_updated':
        handleBalanceUpdated($data);
        break;

    default:
        jsonResponse(['error' => 'Unknown event type'], 400);
}

// =========================================================
// EVENT HANDLERS
// =========================================================

/**
 * Handle successful trade completion
 */
function handleTradeCompleted(array $data): void
{
    $pdo = getDBConnection();
    if (!$pdo) {
        jsonResponse(['error' => 'Database connection failed'], 500);
    }

    $jobId = $data['jobId'] ?? null;
    $result = $data['result'] ?? [];

    if (!$jobId) {
        jsonResponse(['error' => 'Missing jobId'], 400);
    }

    try {
        // Update trade history
        $stmt = $pdo->prepare("
            UPDATE trade_history SET
                status = 'completed',
                executed_at = NOW(),
                olymptrade_trade_id = :ot_trade_id,
                result_amount = :result_amount,
                result_status = :result_status,
                updated_at = NOW()
            WHERE job_id = :job_id
        ");

        $stmt->execute([
            'job_id' => $jobId,
            'ot_trade_id' => $result['tradeId'] ?? null,
            'result_amount' => $result['amount'] ?? 0,
            'result_status' => $result['outcome'] ?? 'unknown'
        ]);

        // Get trader_id from trade_history
        $stmt = $pdo->prepare("SELECT trader_id, amount, direction FROM trade_history WHERE job_id = :job_id");
        $stmt->execute(['job_id' => $jobId]);
        $trade = $stmt->fetch();

        if ($trade) {
            // Update daily stats
            updateDailyStats($pdo, $trade['trader_id'], $result);

            // Send notification if enabled
            sendTradeNotification($trade['trader_id'], 'completed', $data);
        }

        jsonResponse([
            'success' => true,
            'message' => 'Trade completion recorded'
        ]);

    } catch (PDOException $e) {
        error_log("[Robot Callback] DB Error: " . $e->getMessage());
        jsonResponse(['error' => 'Database error'], 500);
    }
}

/**
 * Handle failed trade
 */
function handleTradeFailed(array $data): void
{
    $pdo = getDBConnection();
    if (!$pdo) {
        jsonResponse(['error' => 'Database connection failed'], 500);
    }

    $jobId = $data['jobId'] ?? null;
    $error = $data['error'] ?? 'Unknown error';

    if (!$jobId) {
        jsonResponse(['error' => 'Missing jobId'], 400);
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE trade_history SET
                status = 'failed',
                error_message = :error,
                updated_at = NOW()
            WHERE job_id = :job_id
        ");

        $stmt->execute([
            'job_id' => $jobId,
            'error' => substr($error, 0, 500)
        ]);

        // Get trader_id for notification
        $stmt = $pdo->prepare("SELECT trader_id FROM trade_history WHERE job_id = :job_id");
        $stmt->execute(['job_id' => $jobId]);
        $trade = $stmt->fetch();

        if ($trade) {
            sendTradeNotification($trade['trader_id'], 'failed', $data);
        }

        jsonResponse([
            'success' => true,
            'message' => 'Trade failure recorded'
        ]);

    } catch (PDOException $e) {
        error_log("[Robot Callback] DB Error: " . $e->getMessage());
        jsonResponse(['error' => 'Database error'], 500);
    }
}

/**
 * Handle successful login
 */
function handleLoginSuccess(array $data): void
{
    $pdo = getDBConnection();
    if (!$pdo) {
        jsonResponse(['error' => 'Database connection failed'], 500);
    }

    $email = $data['email'] ?? null;

    if (!$email) {
        jsonResponse(['error' => 'Missing email'], 400);
    }

    try {
        // Update trader session info
        $stmt = $pdo->prepare("
            UPDATE traders SET
                last_login_at = NOW(),
                login_status = 'success',
                consecutive_login_failures = 0,
                updated_at = NOW()
            WHERE olymptrade_email = :email
        ");
        $stmt->execute(['email' => $email]);

        // Insert session record
        $stmt = $pdo->prepare("
            INSERT INTO trader_sessions (trader_id, session_id, ip_address, status, created_at)
            SELECT id, :session_id, :ip, 'active', NOW()
            FROM traders WHERE olymptrade_email = :email
            ON DUPLICATE KEY UPDATE status = 'active', updated_at = NOW()
        ");
        $stmt->execute([
            'email' => $email,
            'session_id' => $data['sessionId'] ?? 'unknown',
            'ip' => $data['ip'] ?? '0.0.0.0'
        ]);

        jsonResponse(['success' => true, 'message' => 'Login success recorded']);

    } catch (PDOException $e) {
        error_log("[Robot Callback] DB Error: " . $e->getMessage());
        jsonResponse(['error' => 'Database error'], 500);
    }
}

/**
 * Handle failed login
 */
function handleLoginFailed(array $data): void
{
    $pdo = getDBConnection();
    if (!$pdo) {
        jsonResponse(['error' => 'Database connection failed'], 500);
    }

    $email = $data['email'] ?? null;
    $reason = $data['reason'] ?? 'Unknown';

    if (!$email) {
        jsonResponse(['error' => 'Missing email'], 400);
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE traders SET
                login_status = 'failed',
                last_login_error = :reason,
                consecutive_login_failures = consecutive_login_failures + 1,
                updated_at = NOW()
            WHERE olymptrade_email = :email
        ");
        $stmt->execute([
            'email' => $email,
            'reason' => substr($reason, 0, 255)
        ]);

        // Check if too many failures
        $stmt = $pdo->prepare("
            SELECT id, consecutive_login_failures FROM traders WHERE olymptrade_email = :email
        ");
        $stmt->execute(['email' => $email]);
        $trader = $stmt->fetch();

        if ($trader && $trader['consecutive_login_failures'] >= 5) {
            // Notify admin about account issue
            notifyAdminLoginIssue($trader['id'], $email, $reason);
        }

        jsonResponse(['success' => true, 'message' => 'Login failure recorded']);

    } catch (PDOException $e) {
        error_log("[Robot Callback] DB Error: " . $e->getMessage());
        jsonResponse(['error' => 'Database error'], 500);
    }
}

/**
 * Handle session expired
 */
function handleSessionExpired(array $data): void
{
    $pdo = getDBConnection();
    if (!$pdo) {
        jsonResponse(['error' => 'Database connection failed'], 500);
    }

    $email = $data['email'] ?? null;

    if (!$email) {
        jsonResponse(['error' => 'Missing email'], 400);
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE traders SET
                login_status = 'expired',
                updated_at = NOW()
            WHERE olymptrade_email = :email
        ");
        $stmt->execute(['email' => $email]);

        // Mark all active sessions as expired
        $stmt = $pdo->prepare("
            UPDATE trader_sessions SET status = 'expired', updated_at = NOW()
            WHERE trader_id = (SELECT id FROM traders WHERE olymptrade_email = :email)
            AND status = 'active'
        ");
        $stmt->execute(['email' => $email]);

        jsonResponse(['success' => true, 'message' => 'Session expiry recorded']);

    } catch (PDOException $e) {
        error_log("[Robot Callback] DB Error: " . $e->getMessage());
        jsonResponse(['error' => 'Database error'], 500);
    }
}

/**
 * Handle captcha detected
 */
function handleCaptchaDetected(array $data): void
{
    $pdo = getDBConnection();
    if (!$pdo) {
        jsonResponse(['error' => 'Database connection failed'], 500);
    }

    $email = $data['email'] ?? null;

    // Log captcha detection
    error_log("[Robot Callback] Captcha detected for: " . ($email ?: 'unknown'));

    if ($email) {
        try {
            // Record captcha encounter
            $stmt = $pdo->prepare("
                UPDATE traders SET
                    captcha_count = captcha_count + 1,
                    last_captcha_at = NOW(),
                    updated_at = NOW()
                WHERE olymptrade_email = :email
            ");
            $stmt->execute(['email' => $email]);
        } catch (PDOException $e) {
            error_log("[Robot Callback] DB Error: " . $e->getMessage());
        }
    }

    jsonResponse(['success' => true, 'message' => 'Captcha detection recorded']);
}

/**
 * Handle balance update
 */
function handleBalanceUpdated(array $data): void
{
    $pdo = getDBConnection();
    if (!$pdo) {
        jsonResponse(['error' => 'Database connection failed'], 500);
    }

    $email = $data['email'] ?? null;
    $balance = $data['balance'] ?? null;
    $isDemo = $data['isDemo'] ?? true;

    if (!$email || $balance === null) {
        jsonResponse(['error' => 'Missing required fields'], 400);
    }

    try {
        $column = $isDemo ? 'balance_demo' : 'balance_real';

        $stmt = $pdo->prepare("
            UPDATE traders SET
                {$column} = :balance,
                balance_updated_at = NOW(),
                updated_at = NOW()
            WHERE olymptrade_email = :email
        ");
        $stmt->execute([
            'email' => $email,
            'balance' => $balance
        ]);

        jsonResponse(['success' => true, 'message' => 'Balance updated']);

    } catch (PDOException $e) {
        error_log("[Robot Callback] DB Error: " . $e->getMessage());
        jsonResponse(['error' => 'Database error'], 500);
    }
}

// =========================================================
// HELPER FUNCTIONS
// =========================================================

/**
 * Update daily statistics
 */
function updateDailyStats(PDO $pdo, int $traderId, array $result): void
{
    $today = date('Y-m-d');
    $outcome = $result['outcome'] ?? 'unknown';
    $profit = $result['profit'] ?? 0;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO daily_stats (trader_id, date, total_trades, wins, losses, profit, updated_at)
            VALUES (:trader_id, :date, 1, :wins, :losses, :profit, NOW())
            ON DUPLICATE KEY UPDATE
                total_trades = total_trades + 1,
                wins = wins + :wins,
                losses = losses + :losses,
                profit = profit + :profit,
                updated_at = NOW()
        ");

        $stmt->execute([
            'trader_id' => $traderId,
            'date' => $today,
            'wins' => $outcome === 'win' ? 1 : 0,
            'losses' => $outcome === 'loss' ? 1 : 0,
            'profit' => $profit
        ]);
    } catch (PDOException $e) {
        error_log("[Robot Callback] Stats update error: " . $e->getMessage());
    }
}

/**
 * Send trade notification to trader
 */
function sendTradeNotification(int $traderId, string $type, array $data): void
{
    // TODO: Implement notification system (email, telegram, push)
    // For now, just log
    error_log("[Notification] Trade {$type} for trader #{$traderId}");
}

/**
 * Notify admin about login issues
 */
function notifyAdminLoginIssue(int $traderId, string $email, string $reason): void
{
    // TODO: Implement admin notification
    error_log("[Admin Alert] Login issues for trader #{$traderId} ({$email}): {$reason}");
}
