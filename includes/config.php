<?php
/**
 * =========================================================
 * ZYN TRADE SYSTEM - Configuration File
 * =========================================================
 * Version: 3.0 Premium Edition
 * "Precision Over Emotion"
 *
 * IMPORTANT: Update database credentials before deployment
 * =========================================================
 */

// =========================================================
// ENVIRONMENT DETECTION
// =========================================================
define('ENVIRONMENT', getenv('APP_ENV') ?: 'production');
define('APP_DEBUG', ENVIRONMENT === 'development');

// Error reporting based on environment
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// =========================================================
// SESSION CONFIGURATION
// =========================================================
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// =========================================================
// DATABASE CONFIGURATION
// =========================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'nrrskfvk_ZYNtradesystem');
define('DB_USER', 'nrrskfvk_userZYNtradesystem');
define('DB_PASS', 'Devin1922$');
define('DB_CHARSET', 'utf8mb4');

// =========================================================
// SITE CONFIGURATION
// =========================================================
define('SITE_NAME', 'ZYN Trade System');
define('SITE_TAGLINE', 'Precision Over Emotion');
define('SITE_URL', 'https://tester.situneo.my.id');
define('SITE_EMAIL', 'support@tester.situneo.my.id');
define('SITE_VERSION', '3.0.0');

// =========================================================
// OLYMPTRADE AFFILIATE CONFIGURATION
// =========================================================
define('AFFILIATE_ID', '660784');
define('AFFILIATE_SUBID', 'ZYNtradeSystem');

// Multi-language affiliate links
define('AFFILIATE_LINKS', [
    'id' => 'https://olymptrade-vid.com/id-id/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'en' => 'https://olymptrade.com/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'pt' => 'https://olymptrade-vid.com/pt-pt/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'es' => 'https://olymptrade-vid.com/es-es/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'ar' => 'https://olymptrade-vid.com/ar-ar/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'hi' => 'https://olymptrade-vid.com/hi-hi/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'ms' => 'https://olymptrade-vid.com/ms-ms/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'th' => 'https://olymptrade-vid.com/th-th/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'vi' => 'https://olymptrade-vid.com/vi-vi/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'bn' => 'https://olymptrade-vid.com/bn-bn/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'tr' => 'https://olymptrade-vid.com/tr-tr/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'zh' => 'https://olymptrade-vid.com/zh-zh/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'ru' => 'https://olymptrade-vid.com/ru-ru/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'ko' => 'https://olymptrade-vid.com/ko-ko/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'ja' => 'https://olymptrade-vid.com/ja-ja/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'fr' => 'https://olymptrade-vid.com/fr-fr/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'de' => 'https://olymptrade-vid.com/de-de/?affiliate_id=660784&subid1=ZYNtradeSystem',
]);

define('OLYMPTRADE_AFFILIATE_LINK', AFFILIATE_LINKS['en']);

// =========================================================
// TELEGRAM CONFIGURATION
// =========================================================
define('TELEGRAM_CHANNEL', 'https://t.me/OlymptradeCopytrade');
define('TELEGRAM_CHANNEL_ID', '@OlymptradeCopytrade');
define('TELEGRAM_USERNAME', '@aheenkgans');
define('TELEGRAM_SUPPORT', '@aheenkgans');
define('TELEGRAM_BOT_TOKEN', '');
define('WHATSAPP_SUPPORT', '6281234567890'); // WhatsApp support number (without + prefix)

// =========================================================
// CURRENCY & PRICING (USD - Global Market)
// =========================================================
define('DEFAULT_CURRENCY', 'USD');
define('MIN_DEPOSIT', 10);
define('TRIAL_DURATION', 0);

// Package Pricing
define('PRICE_FREE', 0);
define('PRICE_STARTER', 19);
define('PRICE_PRO', 29);
define('PRICE_ELITE', 79);
define('PRICE_VIP', 149);

// =========================================================
// STRATEGY ACCESS PER PACKAGE
// =========================================================
define('STRATEGIES_FREE', ['8', '9']);
define('STRATEGIES_STARTER', ['6', '7', '8', '9']);
define('STRATEGIES_PRO', ['6', '7', '8', '9']);
define('STRATEGIES_ELITE', ['3', '4', '5', '6', '7', '8', '9']);
define('STRATEGIES_VIP', ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10']);

// =========================================================
// TRADING CONFIGURATION
// =========================================================
define('ALLOWED_MARKETS', ['EUR/USD', 'GBP/USD']);
define('ALLOWED_TIMEFRAMES', ['5M', '15M', '30M', '1H']);
define('REAL_ACCOUNT_ONLY', true);

// Money Management Types
define('MONEY_MANAGEMENT_TYPES', [
    'flat' => [
        'name' => 'Flat Amount',
        'description' => 'Jumlah tetap setiap trade',
        'icon' => 'fa-equals'
    ],
    'martingale' => [
        'name' => 'Martingale',
        'description' => 'Gandakan setelah loss (max 3 step)',
        'icon' => 'fa-chart-line',
        'max_steps' => 3,
        'multiplier' => 2
    ]
]);

// Timeframe Amount Settings
define('TIMEFRAME_AMOUNTS', [
    '5M' => ['min' => 10000, 'max' => 100000, 'default' => 10000],
    '15M' => ['min' => 15000, 'max' => 150000, 'default' => 15000],
    '30M' => ['min' => 20000, 'max' => 200000, 'default' => 20000],
    '1H' => ['min' => 25000, 'max' => 250000, 'default' => 25000]
]);

// =========================================================
// SCHEDULE CONFIGURATION
// =========================================================
define('WEEKEND_AUTO_OFF', true);
define('WEEKEND_DAYS', [6, 0]); // Saturday, Sunday

define('SCHEDULE_MODES', [
    'auto_24h' => [
        'name' => 'Auto 24 Jam',
        'description' => 'Robot aktif 24 jam non-stop',
        'icon' => 'fa-clock'
    ],
    'best_hours' => [
        'name' => 'Best Hours',
        'description' => 'Jam terbaik: 14:00-22:00 WIB',
        'icon' => 'fa-star',
        'start' => '14:00',
        'end' => '22:00'
    ],
    'custom_single' => [
        'name' => 'Custom Single',
        'description' => 'Tentukan 1 range waktu custom',
        'icon' => 'fa-sliders-h'
    ],
    'multi_session' => [
        'name' => 'Multi Session',
        'description' => 'Beberapa sesi berbeda dalam 1 hari',
        'icon' => 'fa-layer-group'
    ],
    'per_day' => [
        'name' => 'Per Day Different',
        'description' => 'Jadwal berbeda tiap hari',
        'icon' => 'fa-calendar-alt'
    ]
]);

// Signal Frequency per Strategy
define('SIGNAL_FREQUENCY', [
    '1' => ['min' => 3, 'max' => 8, 'avg' => 5],
    '2' => ['min' => 2, 'max' => 6, 'avg' => 4],
    '3' => ['min' => 4, 'max' => 10, 'avg' => 7],
    '4' => ['min' => 3, 'max' => 7, 'avg' => 5],
    '5' => ['min' => 2, 'max' => 5, 'avg' => 3],
    '6' => ['min' => 5, 'max' => 12, 'avg' => 8],
    '7' => ['min' => 4, 'max' => 9, 'avg' => 6],
    '8' => ['min' => 6, 'max' => 15, 'avg' => 10],
    '9' => ['min' => 5, 'max' => 13, 'avg' => 9],
    '10' => ['min' => 2, 'max' => 4, 'avg' => 3]
]);

// =========================================================
// SECURITY CONFIGURATION
// =========================================================
define('HASH_COST', 12);
define('SESSION_LIFETIME', 3600 * 24 * 7);
define('CSRF_TOKEN_LIFETIME', 3600);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900);

// Robot Engine API Key (must match robot-engine/.env API_SECRET_KEY)
define('ROBOT_API_SECRET_KEY', getenv('ROBOT_API_SECRET_KEY') ?: 'ZYN_R0B0T_4P1_K3Y_2024!@#$%');

// File Upload
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Watermark & Branding
define('WATERMARK_ENABLED', true);

// Anti-fraud
define('MULTI_ACCOUNT_CHECK', false);
define('DEVICE_LIMIT_CHECK', false);

// =========================================================
// PAGINATION
// =========================================================
define('ITEMS_PER_PAGE', 20);

// =========================================================
// TIMEZONE
// =========================================================
date_default_timezone_set('Asia/Jakarta');

// =========================================================
// DATABASE CONNECTION
// =========================================================
function getDBConnection() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        } catch (PDOException $e) {
            error_log("[ZYN Database Error] " . $e->getMessage());

            if (APP_DEBUG) {
                throw $e;
            }

            return null;
        }
    }

    return $pdo;
}

// =========================================================
// AUTHENTICATION HELPERS
// =========================================================
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function requireAdmin(): void {
    if (!isAdmin()) {
        redirect('dashboard.php');
    }
}

// =========================================================
// UTILITY FUNCTIONS
// =========================================================
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function cleanInput(string $data): string {
    return htmlspecialchars(trim(stripslashes($data)), ENT_QUOTES, 'UTF-8');
}

function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token']) ||
        empty($_SESSION['csrf_token_time']) ||
        (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_LIFETIME) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function formatCurrency(float $amount, string $currency = 'USD'): string {
    if ($currency === 'IDR') {
        return 'Rp' . number_format($amount, 0, ',', '.');
    }
    return '$' . number_format($amount, 2, '.', ',');
}

function formatDate(string $date, string $format = 'M d, Y'): string {
    $timestamp = strtotime($date);
    return $timestamp ? date($format, $timestamp) : $date;
}

function daysRemaining(?string $expiry_date): int {
    if (empty($expiry_date)) {
        return 0;
    }

    $now = new DateTime();
    $expiry = new DateTime($expiry_date);
    $diff = $now->diff($expiry);

    return $diff->invert ? 0 : $diff->days;
}

function generateRandomString(int $length = 16): string {
    return bin2hex(random_bytes($length / 2));
}

function getClientIP(): string {
    $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];

    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return '0.0.0.0';
}

function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function isAjaxRequest(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// =========================================================
// FLASH MESSAGE SYSTEM
// =========================================================
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function hasFlash(): bool {
    return isset($_SESSION['flash']);
}
