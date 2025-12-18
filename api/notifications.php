<?php
/**
 * API: Notifications
 * Get and manage user notifications
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$userId = $_GET['user_id'] ?? $_POST['user_id'] ?? '';

if (empty($userId)) {
    http_response_code(400);
    echo json_encode(['error' => 'user_id required']);
    exit;
}

try {
    $pdo = getDBConnection();

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Get notifications
            $limit = min(50, max(5, intval($_GET['limit'] ?? 20)));
            $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === '1';

            $sql = "
                SELECT
                    id,
                    type,
                    title,
                    message,
                    action_url,
                    icon,
                    priority,
                    is_read,
                    created_at
                FROM notifications
                WHERE user_id = ?
            ";

            if ($unreadOnly) {
                $sql .= " AND is_read = 0";
            }

            $sql .= " ORDER BY created_at DESC LIMIT ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId, $limit]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get unread count
            $countStmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
            $countStmt->execute([$userId]);
            $unreadCount = $countStmt->fetch()['count'];

            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => intval($unreadCount),
                'total' => count($notifications)
            ]);
            break;

        case 'POST':
            // Create notification (internal API)
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }

            $type = $input['type'] ?? 'info';
            $title = $input['title'] ?? null;
            $message = $input['message'] ?? '';
            $actionUrl = $input['action_url'] ?? null;
            $icon = $input['icon'] ?? 'fa-bell';
            $priority = $input['priority'] ?? 'normal';

            if (empty($message)) {
                http_response_code(400);
                echo json_encode(['error' => 'message required']);
                exit;
            }

            $stmt = $pdo->prepare("
                INSERT INTO notifications (user_id, type, title, message, action_url, icon, priority, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $result = $stmt->execute([$userId, $type, $title, $message, $actionUrl, $icon, $priority]);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'notification_id' => $pdo->lastInsertId()
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create notification']);
            }
            break;

        case 'PUT':
            // Mark notification as read
            $input = json_decode(file_get_contents('php://input'), true);
            $notificationId = $input['notification_id'] ?? $_GET['notification_id'] ?? null;
            $markAllRead = $input['mark_all_read'] ?? false;

            if ($markAllRead) {
                // Mark all notifications as read
                $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0");
                $stmt->execute([$userId]);

                echo json_encode([
                    'success' => true,
                    'marked_count' => $stmt->rowCount()
                ]);
            } elseif ($notificationId) {
                // Mark specific notification as read
                $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?");
                $stmt->execute([$notificationId, $userId]);

                echo json_encode([
                    'success' => $stmt->rowCount() > 0
                ]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'notification_id or mark_all_read required']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error', 'message' => $e->getMessage()]);
}
