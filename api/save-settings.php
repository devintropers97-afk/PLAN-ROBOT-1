<?php
/**
 * ZYN Trade System - Save Settings API
 * Saves individual robot settings via AJAX
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

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid CSRF token'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];
$setting = $_POST['setting'] ?? '';
$value = $_POST['value'] ?? '';

// Whitelist of allowed settings (must match robot_settings columns)
$allowedSettings = [
    'robot_enabled',
    'market',
    'markets',
    'timeframe',
    'timeframes',
    'risk_level',
    'trade_amount',
    'daily_limit',
    'max_trades_per_day',
    'strategy_id',
    'active_strategies',
    'take_profit_target',
    'max_loss_limit',
    'auto_pause_on_tp',
    'auto_pause_on_ml',
    'schedule_mode',
    'schedule_start_time',
    'schedule_end_time',
    'schedule_sessions',
    'schedule_per_day',
    'weekend_auto_off',
    'resume_behavior',
    'notification_enabled',
    'sound_enabled',
    'money_management_type',
    'martingale_step',
    'martingale_max_steps',
    'martingale_multiplier',
    'martingale_base_amount',
    'notify_on_trade',
    'notify_on_error',
    'notify_on_pause',
    'daily_target_amount',
    'daily_target_auto_stop'
];

if (!in_array($setting, $allowedSettings)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid setting'
    ]);
    exit;
}

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }

    // Check if settings exist for user
    $stmt = $pdo->prepare("SELECT id FROM robot_settings WHERE user_id = ?");
    $stmt->execute([$userId]);

    if ($stmt->fetch()) {
        // Update existing setting
        $stmt = $pdo->prepare("UPDATE robot_settings SET {$setting} = ?, updated_at = NOW() WHERE user_id = ?");
        $stmt->execute([$value, $userId]);
    } else {
        // Create new settings row with this setting
        $stmt = $pdo->prepare("INSERT INTO robot_settings (user_id, {$setting}, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
        $stmt->execute([$userId, $value]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Setting saved'
    ]);

} catch (PDOException $e) {
    error_log("Save settings error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
}
