<?php
/**
 * API: Validate License Key
 * Endpoint untuk validasi license key dari robot
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$licenseKey = $input['license_key'] ?? '';

if (empty($licenseKey)) {
    http_response_code(400);
    echo json_encode(['valid' => false, 'error' => 'License key required']);
    exit;
}

try {
    $pdo = getDBConnection();

    // Find user by license key
    $stmt = $pdo->prepare("
        SELECT u.*, u.package as plan_name
        FROM users u
        WHERE u.license_key = ? AND u.status = 'active'
    ");
    $stmt->execute([$licenseKey]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['valid' => false, 'error' => 'Invalid or inactive license']);
        exit;
    }

    // Check package expiry
    if ($user['package_expiry'] && strtotime($user['package_expiry']) < time()) {
        echo json_encode(['valid' => false, 'error' => 'License expired']);
        exit;
    }

    // Get allowed strategies based on package
    $packageStrategies = [
        'free' => [8, 9],
        'pro' => [1, 2, 8, 9],
        'elite' => [1, 2, 3, 4, 5, 8, 9],
        'vip' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
    ];
    $strategies = $packageStrategies[$user['package']] ?? [8, 9];

    // Update last login
    $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $updateStmt->execute([$user['id']]);

    echo json_encode([
        'valid' => true,
        'plan' => strtoupper($user['plan_name']),
        'expiry' => $user['package_expiry'],
        'strategies' => $strategies,
        'user' => [
            'id' => $user['id'],
            'fullname' => $user['fullname'],
            'email' => $user['email'],
            'olymptrade_id' => $user['olymptrade_id']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['valid' => false, 'error' => 'Server error']);
}
