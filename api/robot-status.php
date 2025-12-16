<?php
/**
 * API: Robot Status
 * Endpoint untuk melaporkan status robot
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $pdo = getDBConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Update robot status
        $input = json_decode(file_get_contents('php://input'), true);

        $userId = $input['user_id'] ?? '';
        $status = $input['status'] ?? 'unknown';
        $timestamp = $input['timestamp'] ?? date('Y-m-d H:i:s');
        $version = $input['version'] ?? '1.0.0';

        if (empty($userId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'user_id required']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO robot_status (user_id, status, last_active, version, created_at)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                status = VALUES(status),
                last_active = VALUES(last_active),
                version = VALUES(version)
        ");
        $stmt->execute([$userId, $status, $timestamp, $version]);

        echo json_encode(['success' => true]);

    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get robot status
        $userId = $_GET['user_id'] ?? '';

        if (empty($userId)) {
            http_response_code(400);
            echo json_encode(['error' => 'user_id required']);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT * FROM robot_status WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $status = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($status) {
            echo json_encode($status);
        } else {
            echo json_encode([
                'user_id' => $userId,
                'status' => 'unknown',
                'last_active' => null
            ]);
        }
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
