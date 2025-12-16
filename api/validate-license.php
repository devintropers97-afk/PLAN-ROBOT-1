<?php
/**
 * =========================================
 * ZYN TRADE API - Validate License
 * =========================================
 *
 * Endpoint: POST /api/validate-license.php
 * Description: Validates robot license keys and returns user/plan info
 *
 * Request Body:
 * {
 *   "license_key": "string (required)"
 * }
 *
 * Response:
 * Success: { "valid": true, "plan": "...", "user": {...}, "strategies": [...] }
 * Error: { "valid": false, "error": "...", "code": "..." }
 */

// Security headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key, Authorization');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Include dependencies
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// API Version
define('API_VERSION', '1.0.1');

/**
 * Send JSON response with proper structure
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
        'valid' => false,
        'error' => $message,
        'code' => $code,
        'timestamp' => date('c')
    ], $statusCode);
}

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed. Use POST.', 'METHOD_NOT_ALLOWED', 405);
}

// Rate limiting check (simple implementation)
$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateLimitKey = 'rate_limit_validate_' . md5($clientIP);

// Parse JSON input
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sendError('Invalid JSON payload', 'INVALID_JSON', 400);
}

// Validate required fields
$licenseKey = trim($input['license_key'] ?? '');

if (empty($licenseKey)) {
    sendError('License key is required', 'LICENSE_REQUIRED', 400);
}

// Validate license key format (should be alphanumeric with dashes)
if (!preg_match('/^[A-Za-z0-9\-]{16,64}$/', $licenseKey)) {
    sendError('Invalid license key format', 'INVALID_FORMAT', 400);
}

try {
    $pdo = getDBConnection();

    // Find user by license key
    $stmt = $pdo->prepare("
        SELECT
            u.id,
            u.fullname,
            u.email,
            u.username,
            u.olymptrade_id,
            u.package,
            u.package_expiry,
            u.status,
            u.license_key,
            u.created_at,
            u.last_login
        FROM users u
        WHERE u.license_key = :license_key
        LIMIT 1
    ");
    $stmt->execute(['license_key' => $licenseKey]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user exists
    if (!$user) {
        sendError('License key not found', 'LICENSE_NOT_FOUND', 401);
    }

    // Check user status
    if ($user['status'] !== 'active') {
        $statusMessages = [
            'pending' => 'Account pending verification',
            'suspended' => 'Account has been suspended',
            'banned' => 'Account has been banned',
            'inactive' => 'Account is inactive'
        ];
        $message = $statusMessages[$user['status']] ?? 'Account not active';
        sendError($message, 'ACCOUNT_' . strtoupper($user['status']), 403);
    }

    // Check package expiry
    if ($user['package_expiry']) {
        $expiryTimestamp = strtotime($user['package_expiry']);
        if ($expiryTimestamp < time()) {
            // Package expired - downgrade to free
            $updateStmt = $pdo->prepare("UPDATE users SET package = 'free' WHERE id = :id");
            $updateStmt->execute(['id' => $user['id']]);
            $user['package'] = 'free';
            $user['package_expiry'] = null;
        }
    }

    // Define strategies per package
    $packageStrategies = [
        'free' => [8, 9],                           // Basic strategies only
        'starter' => [1, 8, 9],                     // + Trend Following
        'pro' => [1, 2, 3, 8, 9],                   // + More strategies
        'elite' => [1, 2, 3, 4, 5, 6, 8, 9],       // + Premium strategies
        'vip' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]   // All strategies
    ];

    // Define package features
    $packageFeatures = [
        'free' => [
            'max_trades_day' => 10,
            'max_trade_amount' => 5,
            'history_days' => 7,
            'priority_support' => false
        ],
        'starter' => [
            'max_trades_day' => 30,
            'max_trade_amount' => 25,
            'history_days' => 30,
            'priority_support' => false
        ],
        'pro' => [
            'max_trades_day' => 100,
            'max_trade_amount' => 100,
            'history_days' => 90,
            'priority_support' => true
        ],
        'elite' => [
            'max_trades_day' => 500,
            'max_trade_amount' => 500,
            'history_days' => 180,
            'priority_support' => true
        ],
        'vip' => [
            'max_trades_day' => -1, // Unlimited
            'max_trade_amount' => -1, // Unlimited
            'history_days' => 365,
            'priority_support' => true
        ]
    ];

    $package = strtolower($user['package']) ?: 'free';
    $strategies = $packageStrategies[$package] ?? $packageStrategies['free'];
    $features = $packageFeatures[$package] ?? $packageFeatures['free'];

    // Update last login timestamp
    $updateLogin = $pdo->prepare("
        UPDATE users
        SET last_login = NOW(),
            login_count = COALESCE(login_count, 0) + 1
        WHERE id = :id
    ");
    $updateLogin->execute(['id' => $user['id']]);

    // Calculate days until expiry
    $daysRemaining = null;
    if ($user['package_expiry']) {
        $daysRemaining = max(0, ceil((strtotime($user['package_expiry']) - time()) / 86400));
    }

    // Send success response
    sendResponse([
        'valid' => true,
        'api_version' => API_VERSION,
        'timestamp' => date('c'),
        'plan' => strtoupper($package),
        'expiry' => $user['package_expiry'],
        'days_remaining' => $daysRemaining,
        'strategies' => $strategies,
        'features' => $features,
        'user' => [
            'id' => (int)$user['id'],
            'fullname' => $user['fullname'],
            'email' => $user['email'],
            'username' => $user['username'],
            'olymptrade_id' => $user['olymptrade_id'],
            'member_since' => $user['created_at']
        ]
    ]);

} catch (PDOException $e) {
    // Log error (don't expose details to client)
    error_log('API Error [validate-license]: ' . $e->getMessage());
    sendError('Database connection failed', 'DATABASE_ERROR', 500);

} catch (Exception $e) {
    error_log('API Error [validate-license]: ' . $e->getMessage());
    sendError('Internal server error', 'SERVER_ERROR', 500);
}
