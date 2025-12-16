<?php
/**
 * =========================================
 * ZYN TRADE API - Robot Status
 * =========================================
 *
 * Endpoint: POST/GET /api/robot-status.php
 * Description: Report and retrieve robot status
 *
 * POST - Update robot status:
 * {
 *   "user_id": "int (required)",
 *   "license_key": "string (optional, for auth)",
 *   "status": "string (running|stopped|error|idle)",
 *   "version": "string",
 *   "balance": "float",
 *   "session_trades": "int",
 *   "session_profit": "float"
 * }
 *
 * GET - Get robot status:
 * ?user_id=123
 */

// Security headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
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
 * Validate status value
 */
function isValidStatus($status) {
    $validStatuses = ['running', 'stopped', 'error', 'idle', 'paused', 'maintenance'];
    return in_array(strtolower($status), $validStatuses);
}

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    $pdo = getDBConnection();

    // ==========================================
    // POST: Update robot status
    // ==========================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Parse JSON input
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            sendError('Invalid JSON payload', 'INVALID_JSON', 400);
        }

        // Validate required fields
        $userId = filter_var($input['user_id'] ?? '', FILTER_VALIDATE_INT);

        if (!$userId || $userId <= 0) {
            sendError('Valid user_id is required', 'USER_ID_REQUIRED', 400);
        }

        // Extract and validate data
        $status = strtolower(trim($input['status'] ?? 'unknown'));
        $version = trim($input['version'] ?? '1.0.0');
        $balance = isset($input['balance']) ? floatval($input['balance']) : null;
        $sessionTrades = isset($input['session_trades']) ? intval($input['session_trades']) : 0;
        $sessionProfit = isset($input['session_profit']) ? floatval($input['session_profit']) : 0;
        $errorMessage = $input['error_message'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

        // Validate status
        if (!isValidStatus($status) && $status !== 'unknown') {
            $status = 'unknown';
        }

        // Version validation (semver format)
        if (!preg_match('/^\d+\.\d+\.\d+/', $version)) {
            $version = '1.0.0';
        }

        // Verify user exists
        $userCheck = $pdo->prepare("SELECT id, status FROM users WHERE id = :id LIMIT 1");
        $userCheck->execute(['id' => $userId]);
        $user = $userCheck->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            sendError('User not found', 'USER_NOT_FOUND', 404);
        }

        // Insert or update robot status
        $stmt = $pdo->prepare("
            INSERT INTO robot_status (
                user_id,
                status,
                version,
                balance,
                session_trades,
                session_profit,
                error_message,
                ip_address,
                last_active,
                created_at,
                updated_at
            ) VALUES (
                :user_id,
                :status,
                :version,
                :balance,
                :session_trades,
                :session_profit,
                :error_message,
                :ip_address,
                NOW(),
                NOW(),
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                status = VALUES(status),
                version = VALUES(version),
                balance = COALESCE(VALUES(balance), balance),
                session_trades = VALUES(session_trades),
                session_profit = VALUES(session_profit),
                error_message = VALUES(error_message),
                ip_address = VALUES(ip_address),
                last_active = NOW(),
                updated_at = NOW(),
                heartbeat_count = heartbeat_count + 1
        ");

        $stmt->execute([
            'user_id' => $userId,
            'status' => $status,
            'version' => $version,
            'balance' => $balance,
            'session_trades' => $sessionTrades,
            'session_profit' => $sessionProfit,
            'error_message' => $errorMessage,
            'ip_address' => $ipAddress
        ]);

        // Update users table with last robot activity
        $updateUser = $pdo->prepare("
            UPDATE users
            SET robot_last_active = NOW()
            WHERE id = :id
        ");
        $updateUser->execute(['id' => $userId]);

        sendResponse([
            'success' => true,
            'message' => 'Status updated successfully',
            'timestamp' => date('c'),
            'data' => [
                'user_id' => $userId,
                'status' => $status,
                'recorded_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }

    // ==========================================
    // GET: Retrieve robot status
    // ==========================================
    else if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $userId = filter_var($_GET['user_id'] ?? '', FILTER_VALIDATE_INT);

        if (!$userId || $userId <= 0) {
            sendError('Valid user_id parameter required', 'USER_ID_REQUIRED', 400);
        }

        // Fetch robot status
        $stmt = $pdo->prepare("
            SELECT
                rs.user_id,
                rs.status,
                rs.version,
                rs.balance,
                rs.session_trades,
                rs.session_profit,
                rs.error_message,
                rs.last_active,
                rs.heartbeat_count,
                rs.created_at,
                rs.updated_at,
                u.fullname as user_name,
                u.package as user_package
            FROM robot_status rs
            JOIN users u ON u.id = rs.user_id
            WHERE rs.user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);
        $status = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($status) {
            // Calculate uptime/activity metrics
            $lastActive = strtotime($status['last_active']);
            $isOnline = (time() - $lastActive) < 120; // Consider online if active within 2 minutes

            sendResponse([
                'success' => true,
                'timestamp' => date('c'),
                'data' => [
                    'user_id' => (int)$status['user_id'],
                    'user_name' => $status['user_name'],
                    'package' => $status['user_package'],
                    'robot' => [
                        'status' => $status['status'],
                        'version' => $status['version'],
                        'is_online' => $isOnline,
                        'balance' => $status['balance'] ? floatval($status['balance']) : null,
                        'session_trades' => (int)$status['session_trades'],
                        'session_profit' => floatval($status['session_profit']),
                        'error_message' => $status['error_message'],
                        'last_active' => $status['last_active'],
                        'heartbeat_count' => (int)$status['heartbeat_count']
                    ]
                ]
            ]);
        } else {
            // No status record found - return default
            sendResponse([
                'success' => true,
                'timestamp' => date('c'),
                'data' => [
                    'user_id' => $userId,
                    'robot' => [
                        'status' => 'unknown',
                        'version' => null,
                        'is_online' => false,
                        'balance' => null,
                        'session_trades' => 0,
                        'session_profit' => 0,
                        'last_active' => null,
                        'heartbeat_count' => 0
                    ]
                ]
            ]);
        }
    }

    // ==========================================
    // Invalid method
    // ==========================================
    else {
        sendError('Method not allowed. Use GET or POST.', 'METHOD_NOT_ALLOWED', 405);
    }

} catch (PDOException $e) {
    error_log('API Error [robot-status]: ' . $e->getMessage());
    sendError('Database error occurred', 'DATABASE_ERROR', 500);

} catch (Exception $e) {
    error_log('API Error [robot-status]: ' . $e->getMessage());
    sendError('Internal server error', 'SERVER_ERROR', 500);
}
