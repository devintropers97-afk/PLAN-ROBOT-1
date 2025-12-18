<?php
/**
 * ZYN Trade API - Index
 * Returns API information
 */
header('Content-Type: application/json');
echo json_encode([
    'name' => 'ZYN Trade API',
    'version' => '1.0',
    'status' => 'online',
    'endpoints' => [
        '/api/health.php',
        '/api/validate-license.php',
        '/api/robot-status.php'
    ]
]);
