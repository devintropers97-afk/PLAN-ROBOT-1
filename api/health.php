<?php
/**
 * API: Health Check
 * Endpoint untuk cek status API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../includes/config.php';
require_once '../includes/functions.php';

try {
    $pdo = getDBConnection();

    // Test database connection
    $stmt = $pdo->query("SELECT 1");

    echo json_encode([
        'status' => 'ok',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '1.0.0',
        'database' => 'connected'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'timestamp' => date('Y-m-d H:i:s'),
        'database' => 'disconnected'
    ]);
}
