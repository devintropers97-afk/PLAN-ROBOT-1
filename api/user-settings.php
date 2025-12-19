<?php
/**
 * API: User Settings
 * Endpoint untuk mendapatkan dan mengupdate pengaturan robot user
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Handle GET request - Get settings
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Allow Robot API key OR session login
    $userId = null;

    if (validateRobotApiKey()) {
        // Robot Engine calling with API key - get user_id from params
        $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'user_id required']);
            exit;
        }
    } elseif (isLoggedIn()) {
        // User logged in via session
        $userId = $_SESSION['user_id'];
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    try {
        $settings = getRobotSettings($userId);
        $user = getUserById($userId);

        echo json_encode([
            'success' => true,
            'user_id' => $userId,
            'package' => $user['package'] ?? 'free',
            'is_active' => (bool)($settings['robot_enabled'] ?? false),
            'market' => $settings['market'] ?? 'EUR/USD',
            'timeframe' => $settings['timeframe'] ?? '15M',
            'strategies' => json_decode($settings['strategies'] ?? '[]', true),
            'schedule_mode' => $settings['schedule_mode'] ?? 'auto_24h',
            'money_management_type' => $settings['money_management_type'] ?? 'flat',
            'trade_amount' => $settings['trade_amount'] ?? 10000,
            'take_profit_target' => $settings['take_profit_target'] ?? 50,
            'max_loss_limit' => $settings['max_loss_limit'] ?? 25,
            'daily_limit' => $settings['daily_limit'] ?? 10
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
    exit;
}

// Handle POST request - Update settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    $userId = $_SESSION['user_id'];

    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $setting = $input['setting'] ?? '';
    $value = $input['value'] ?? '';

    if (empty($setting)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Setting name required']);
        exit;
    }

    // Allowed settings to update
    $allowedSettings = [
        'robot_enabled', 'market', 'timeframe', 'trade_amount',
        'strategies', 'schedule_mode', 'schedule_start_time', 'schedule_end_time',
        'money_management_type', 'martingale_base_amount',
        'take_profit_target', 'max_loss_limit', 'daily_limit',
        'timeframe_amounts', 'schedule_sessions', 'schedule_per_day'
    ];

    if (!in_array($setting, $allowedSettings)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid setting']);
        exit;
    }

    try {
        $result = updateRobotSettings($userId, [$setting => $value]);

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Setting updated' : 'Failed to update setting'
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);
