<?php
/**
 * ZYN Trade System - Push Notification Subscribe API
 * Stores push notification subscriptions
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

$userId = $_SESSION['user_id'];

// Get JSON input
$input = file_get_contents('php://input');
$subscription = json_decode($input, true);

if (!$subscription || !isset($subscription['endpoint'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid subscription data'
    ]);
    exit;
}

try {
    global $pdo;

    // Check if table exists, create if not
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS push_subscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            endpoint VARCHAR(500) NOT NULL,
            p256dh VARCHAR(255),
            auth VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_subscription (user_id, endpoint(255)),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    // Extract keys
    $p256dh = $subscription['keys']['p256dh'] ?? '';
    $auth = $subscription['keys']['auth'] ?? '';

    // Insert or update subscription
    $stmt = $pdo->prepare("
        INSERT INTO push_subscriptions (user_id, endpoint, p256dh, auth)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE p256dh = VALUES(p256dh), auth = VALUES(auth)
    ");
    $stmt->execute([$userId, $subscription['endpoint'], $p256dh, $auth]);

    echo json_encode([
        'success' => true,
        'message' => 'Subscription saved'
    ]);

} catch (PDOException $e) {
    error_log("Push subscribe error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save subscription'
    ]);
}
