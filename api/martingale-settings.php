<?php
/**
 * ZYN Trade System - Martingale Settings API
 * Returns user's Martingale money management settings
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Validate Robot API Key
requireRobotApiKey();

// Get user_id from request
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if (!$userId) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID required'
    ]);
    exit;
}

try {
    global $pdo;

    // Get robot settings
    $stmt = $pdo->prepare("
        SELECT
            money_management_type,
            martingale_step,
            martingale_max_steps,
            martingale_multiplier,
            martingale_base_amount,
            trade_amount
        FROM robot_settings
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        // Return default settings
        echo json_encode([
            'success' => true,
            'enabled' => false,
            'type' => 'flat',
            'multiplier' => 2.0,
            'max_steps' => 3,
            'current_step' => 0,
            'base_amount' => 10000,
            'current_amount' => 10000
        ]);
        exit;
    }

    $enabled = in_array($settings['money_management_type'], ['martingale', 'anti_martingale']);
    $currentStep = (int)$settings['martingale_step'];
    $multiplier = (float)$settings['martingale_multiplier'];
    $baseAmount = (float)$settings['martingale_base_amount'];

    // Calculate current amount based on step
    $currentAmount = $baseAmount;
    if ($enabled && $currentStep > 0) {
        $currentAmount = $baseAmount * pow($multiplier, $currentStep);
    }

    echo json_encode([
        'success' => true,
        'enabled' => $enabled,
        'type' => $settings['money_management_type'],
        'multiplier' => $multiplier,
        'max_steps' => (int)$settings['martingale_max_steps'],
        'current_step' => $currentStep,
        'base_amount' => $baseAmount,
        'current_amount' => round($currentAmount, 2),
        'trade_amount' => (float)$settings['trade_amount']
    ]);

} catch (PDOException $e) {
    error_log("Martingale settings error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
}
