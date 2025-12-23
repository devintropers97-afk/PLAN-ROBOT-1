<?php
/**
 * =========================================
 * ZYN TRADE API - Health Check
 * =========================================
 *
 * Endpoint: GET /api/health.php
 * Description: Check API and system health status
 *
 * Response:
 * {
 *   "status": "healthy|degraded|unhealthy",
 *   "version": "1.0.1",
 *   "timestamp": "2024-01-01T00:00:00+00:00",
 *   "checks": {
 *     "database": "connected|disconnected",
 *     "cache": "available|unavailable",
 *     ...
 *   }
 * }
 */

// Security headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Include dependencies
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// API Version
define('API_VERSION', '1.0.1');
define('API_NAME', 'ZYN Trade API');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'error' => 'Method not allowed. Use GET.',
        'timestamp' => date('c')
    ]);
    exit;
}

// Initialize health check results
$checks = [
    'database' => [
        'status' => 'unknown',
        'latency_ms' => null
    ],
    'php' => [
        'version' => PHP_VERSION,
        'status' => 'ok'
    ],
    'memory' => [
        'usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
        'limit' => ini_get('memory_limit'),
        'status' => 'ok'
    ],
    'disk' => [
        'free_gb' => null,
        'status' => 'unknown'
    ]
];

$overallStatus = 'healthy';
$errors = [];

// ==========================================
// Check 1: Database Connection
// ==========================================
try {
    $startTime = microtime(true);
    $pdo = getDBConnection();

    // Simple query to verify connection
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $endTime = microtime(true);
    $latency = round(($endTime - $startTime) * 1000, 2);

    if ($result && $result['test'] == 1) {
        $checks['database']['status'] = 'connected';
        $checks['database']['latency_ms'] = $latency;

        // Check for slow database
        if ($latency > 500) {
            $checks['database']['warning'] = 'High latency detected';
            $overallStatus = 'degraded';
        }

        // Get some basic stats
        $statsStmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
        $checks['database']['active_users'] = (int)$stats['total'];

    } else {
        throw new Exception('Query returned unexpected result');
    }

} catch (Exception $e) {
    $checks['database']['status'] = 'disconnected';
    $checks['database']['error'] = 'Connection failed';
    $overallStatus = 'unhealthy';
    $errors[] = 'Database connection failed';
    error_log('Health Check Error [database]: ' . $e->getMessage());
}

// ==========================================
// Check 2: Disk Space
// ==========================================
try {
    $freeSpace = disk_free_space(__DIR__);
    $totalSpace = disk_total_space(__DIR__);

    if ($freeSpace !== false && $totalSpace !== false) {
        $freeGb = round($freeSpace / 1024 / 1024 / 1024, 2);
        $usedPercent = round((1 - $freeSpace / $totalSpace) * 100, 1);

        $checks['disk']['free_gb'] = $freeGb;
        $checks['disk']['used_percent'] = $usedPercent;

        if ($freeGb < 1) {
            $checks['disk']['status'] = 'critical';
            $checks['disk']['warning'] = 'Low disk space';
            $overallStatus = 'unhealthy';
            $errors[] = 'Critical: Low disk space';
        } elseif ($freeGb < 5) {
            $checks['disk']['status'] = 'warning';
            $checks['disk']['warning'] = 'Disk space running low';
            if ($overallStatus !== 'unhealthy') {
                $overallStatus = 'degraded';
            }
        } else {
            $checks['disk']['status'] = 'ok';
        }
    }
} catch (Exception $e) {
    $checks['disk']['status'] = 'unknown';
    error_log('Health Check Error [disk]: ' . $e->getMessage());
}

// ==========================================
// Check 3: Memory Usage
// ==========================================
$memoryLimit = ini_get('memory_limit');
$memoryBytes = $checks['memory']['usage_mb'] * 1024 * 1024;

// Parse memory limit
$limitValue = intval($memoryLimit);
$limitUnit = strtoupper(substr($memoryLimit, -1));

switch ($limitUnit) {
    case 'G':
        $limitBytes = $limitValue * 1024 * 1024 * 1024;
        break;
    case 'M':
        $limitBytes = $limitValue * 1024 * 1024;
        break;
    case 'K':
        $limitBytes = $limitValue * 1024;
        break;
    default:
        $limitBytes = $limitValue;
}

if ($limitBytes > 0) {
    $memoryPercent = round(($memoryBytes / $limitBytes) * 100, 1);
    $checks['memory']['used_percent'] = $memoryPercent;

    if ($memoryPercent > 90) {
        $checks['memory']['status'] = 'critical';
        if ($overallStatus !== 'unhealthy') {
            $overallStatus = 'degraded';
        }
    } elseif ($memoryPercent > 75) {
        $checks['memory']['status'] = 'warning';
    }
}

// ==========================================
// Check 4: Required Extensions
// ==========================================
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

$checks['extensions'] = [
    'status' => empty($missingExtensions) ? 'ok' : 'error',
    'required' => $requiredExtensions,
    'missing' => $missingExtensions
];

if (!empty($missingExtensions)) {
    $overallStatus = 'unhealthy';
    $errors[] = 'Missing PHP extensions: ' . implode(', ', $missingExtensions);
}

// ==========================================
// Build Response
// ==========================================
$uptime = null;
if (function_exists('sys_getloadavg')) {
    $load = sys_getloadavg();
    $checks['system_load'] = [
        '1min' => round($load[0], 2),
        '5min' => round($load[1], 2),
        '15min' => round($load[2], 2)
    ];
}

$response = [
    'status' => $overallStatus,
    'name' => API_NAME,
    'version' => API_VERSION,
    'timestamp' => date('c'),
    'server_time' => date('Y-m-d H:i:s T'),
    'environment' => defined('ENVIRONMENT') ? ENVIRONMENT : 'production',
    'checks' => $checks
];

// Include errors only if there are any
if (!empty($errors)) {
    $response['errors'] = $errors;
}

// Add response time
$response['response_time_ms'] = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2);

// Set appropriate HTTP status code
switch ($overallStatus) {
    case 'healthy':
        $statusCode = 200;
        break;
    case 'degraded':
        $statusCode = 200; // Still operational
        break;
    case 'unhealthy':
        $statusCode = 503; // Service Unavailable
        break;
    default:
        $statusCode = 200;
}

http_response_code($statusCode);
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
