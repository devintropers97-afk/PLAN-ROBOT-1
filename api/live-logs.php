<?php
/**
 * API: Live Logs
 * Returns activity logs for user dashboard
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$userId = $_GET['user_id'] ?? '';
$limit = min(50, max(5, intval($_GET['limit'] ?? 15)));

if (empty($userId)) {
    http_response_code(400);
    echo json_encode(['error' => 'user_id required']);
    exit;
}

try {
    $pdo = getDBConnection();

    // Get activity logs
    $stmt = $pdo->prepare("
        SELECT
            id,
            action,
            description as message,
            created_at
        FROM activity_log
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$userId, $limit]);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format logs with type classification
    $formattedLogs = array_map(function($log) {
        $type = 'info';
        $action = strtolower($log['action'] ?? '');

        if (strpos($action, 'win') !== false || strpos($action, 'profit') !== false) {
            $type = 'win';
        } elseif (strpos($action, 'loss') !== false) {
            $type = 'loss';
        } elseif (strpos($action, 'signal') !== false) {
            $type = 'signal';
        } elseif (strpos($action, 'error') !== false || strpos($action, 'fail') !== false) {
            $type = 'error';
        } elseif (strpos($action, 'system') !== false || strpos($action, 'pause') !== false) {
            $type = 'system';
        }

        return [
            'id' => $log['id'],
            'type' => $type,
            'action' => $log['action'],
            'message' => $log['message'],
            'created_at' => $log['created_at'],
            'time_ago' => timeAgo($log['created_at'])
        ];
    }, $logs);

    echo json_encode([
        'success' => true,
        'logs' => $formattedLogs,
        'count' => count($formattedLogs)
    ]);

} catch (Exception $e) {
    error_log("Live logs API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

/**
 * Convert timestamp to human-readable time ago
 */
function timeAgo($datetime) {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) return $diff->y . 'y ago';
    if ($diff->m > 0) return $diff->m . 'mo ago';
    if ($diff->d > 0) return $diff->d . 'd ago';
    if ($diff->h > 0) return $diff->h . 'h ago';
    if ($diff->i > 0) return $diff->i . 'm ago';
    return 'Just now';
}
