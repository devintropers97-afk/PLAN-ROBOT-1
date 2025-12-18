<?php
/**
 * ZYN Trade System - Trading Schedule API
 * Returns user's trading schedule settings
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

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
            schedule_mode,
            schedule_start_time,
            schedule_end_time,
            schedule_sessions,
            schedule_per_day,
            weekend_auto_off
        FROM robot_settings
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        // Return default schedule
        echo json_encode([
            'success' => true,
            'schedule_mode' => 'auto_24h',
            'is_trading_time' => true,
            'weekend_auto_off' => true,
            'next_session' => null
        ]);
        exit;
    }

    // Determine if current time is within trading schedule
    $isTradingTime = true;
    $nextSession = null;

    $currentTime = date('H:i');
    $dayOfWeek = (int)date('w'); // 0 = Sunday, 6 = Saturday

    // Check weekend auto-off
    if ($settings['weekend_auto_off'] && ($dayOfWeek == 0 || $dayOfWeek == 6)) {
        $isTradingTime = false;
        $nextSession = date('Y-m-d 00:00:00', strtotime('next monday'));
    }

    // Check schedule mode
    switch ($settings['schedule_mode']) {
        case 'best_hours':
            // Best hours: 14:00-22:00 (high volatility)
            $isTradingTime = $currentTime >= '14:00' && $currentTime <= '22:00';
            if (!$isTradingTime) {
                $nextSession = $currentTime < '14:00'
                    ? date('Y-m-d 14:00:00')
                    : date('Y-m-d 14:00:00', strtotime('+1 day'));
            }
            break;

        case 'custom_single':
            $start = $settings['schedule_start_time'];
            $end = $settings['schedule_end_time'];
            if ($start && $end) {
                $isTradingTime = $currentTime >= $start && $currentTime <= $end;
                if (!$isTradingTime) {
                    $nextSession = $currentTime < $start
                        ? date('Y-m-d ') . $start
                        : date('Y-m-d ', strtotime('+1 day')) . $start;
                }
            }
            break;

        case 'multi_session':
            $sessions = json_decode($settings['schedule_sessions'], true) ?: [];
            $isTradingTime = false;
            foreach ($sessions as $session) {
                if ($currentTime >= $session['start'] && $currentTime <= $session['end']) {
                    $isTradingTime = true;
                    break;
                }
            }
            break;

        case 'per_day':
            $perDay = json_decode($settings['schedule_per_day'], true) ?: [];
            $todaySessions = $perDay[$dayOfWeek] ?? [];
            $isTradingTime = false;
            foreach ($todaySessions as $session) {
                if ($currentTime >= $session['start'] && $currentTime <= $session['end']) {
                    $isTradingTime = true;
                    break;
                }
            }
            break;

        case 'auto_24h':
        default:
            $isTradingTime = true;
            break;
    }

    echo json_encode([
        'success' => true,
        'schedule_mode' => $settings['schedule_mode'],
        'is_trading_time' => $isTradingTime,
        'weekend_auto_off' => (bool)$settings['weekend_auto_off'],
        'next_session' => $nextSession,
        'current_time' => date('Y-m-d H:i:s'),
        'day_of_week' => $dayOfWeek
    ]);

} catch (PDOException $e) {
    error_log("Schedule API error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
}
