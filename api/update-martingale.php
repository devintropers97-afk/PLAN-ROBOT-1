<?php
/**
 * ZYN Trade System - Update Martingale Step API
 * Updates the current Martingale step after trade result
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get request data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$userId = isset($data['user_id']) ? (int)$data['user_id'] : 0;
$step = isset($data['step']) ? (int)$data['step'] : 0;
$amount = isset($data['amount']) ? (float)$data['amount'] : 0;

if (!$userId) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID required'
    ]);
    exit;
}

try {
    global $pdo;

    // Get current settings
    $stmt = $pdo->prepare("SELECT martingale_max_steps FROM robot_settings WHERE user_id = ?");
    $stmt->execute([$userId]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        echo json_encode([
            'success' => false,
            'message' => 'User settings not found'
        ]);
        exit;
    }

    // Validate step
    $maxSteps = (int)$settings['martingale_max_steps'];
    $step = max(0, min($step, $maxSteps));

    // Update martingale step
    $stmt = $pdo->prepare("
        UPDATE robot_settings
        SET
            martingale_step = ?,
            trade_amount = ?,
            updated_at = NOW()
        WHERE user_id = ?
    ");
    $stmt->execute([$step, $amount, $userId]);

    // Log activity
    logActivity($userId, 'martingale_update', "Step updated to {$step}, amount: {$amount}");

    echo json_encode([
        'success' => true,
        'message' => 'Martingale step updated',
        'step' => $step,
        'amount' => $amount,
        'max_steps' => $maxSteps
    ]);

} catch (PDOException $e) {
    error_log("Update martingale error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
}
