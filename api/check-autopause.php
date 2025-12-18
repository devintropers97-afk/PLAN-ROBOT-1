<?php
/**
 * ZYN Trade System - Check Auto-Pause Status API
 * Returns auto-pause status and details for the logged-in user
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'auto_paused' => false,
        'message' => 'Not authenticated'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];

// Get robot settings
$settings = getRobotSettings($userId);

if (!$settings) {
    echo json_encode([
        'success' => false,
        'auto_paused' => false,
        'message' => 'No settings found'
    ]);
    exit;
}

// Check if auto-pause is triggered
$autoPaused = $settings['auto_pause_triggered'] ?? false;

if (!$autoPaused) {
    echo json_encode([
        'success' => true,
        'auto_paused' => false
    ]);
    exit;
}

// Get today's P&L
$todayStats = getDailyStats($userId, date('Y-m-d'));
$currentPnL = $todayStats['total_pnl'] ?? 0;

// Get resume info
$resumeInfo = getResumeInfo($userId);

// Prepare response
$response = [
    'success' => true,
    'auto_paused' => true,
    'reason' => $settings['auto_pause_reason'] ?? 'unknown',
    'pause_time' => $settings['auto_pause_time'] ?? null,
    'current_profit' => $currentPnL > 0 ? $currentPnL : 0,
    'current_loss' => $currentPnL < 0 ? abs($currentPnL) : 0,
    'take_profit_target' => $settings['take_profit_target'] ?? 0,
    'max_loss_limit' => $settings['max_loss_limit'] ?? 0,
    'resume_behavior' => $settings['resume_behavior'] ?? 'next_session',
    'can_resume' => $resumeInfo['can_resume'] ?? true,
    'next_resume_time' => $resumeInfo['next_resume_time'] ?? null,
    'resume_message' => $resumeInfo['message'] ?? ''
];

echo json_encode($response);
