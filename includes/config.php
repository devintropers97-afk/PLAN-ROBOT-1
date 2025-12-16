<?php
/**
 * ZYN Trade System - Configuration File
 * Version: 2.1
 *
 * IMPORTANT: Update these settings before uploading to cPanel
 */

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration - CPANEL SETTINGS
define('DB_HOST', 'localhost');
define('DB_NAME', 'nrrskfvk_ZYNtradesystem');
define('DB_USER', 'nrrskfvk_userZYNtradesystem');
define('DB_PASS', 'Devin1922$');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', 'ZYN Trade System');
define('SITE_TAGLINE', 'Precision Over Emotion');
define('SITE_URL', 'https://tester.situneo.my.id');
define('SITE_EMAIL', 'support@tester.situneo.my.id');

// OlympTrade Affiliate Links - Multi-Language
define('AFFILIATE_ID', '660784');
define('AFFILIATE_SUBID', 'ZYNtradeSystem');

// Language-specific affiliate links
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

// Default affiliate link (English/Universal)
define('OLYMPTRADE_AFFILIATE_LINK', AFFILIATE_LINKS['en']);

// Telegram Support
define('TELEGRAM_CHANNEL', 'https://t.me/OlymptradeCopytrade');
define('TELEGRAM_CHANNEL_ID', '@OlymptradeCopytrade');
define('TELEGRAM_USERNAME', '@aheenkgans');
define('TELEGRAM_SUPPORT', '@aheenkgans');
define('TELEGRAM_BOT_TOKEN', ''); // For future Telegram bot integration

// Currency (USD for global market)
define('DEFAULT_CURRENCY', 'USD');

// Minimum Deposit (USD for OlympTrade)
define('MIN_DEPOSIT', 10);

// Trial Duration - FREE tier is unlimited but with limited strategies
define('TRIAL_DURATION', 0); // FREE tier doesn't expire

// Package Prices (USD - Global Market)
define('PRICE_FREE', 0);
define('PRICE_PRO', 29);       // $29/month
define('PRICE_ELITE', 79);     // $79/month
define('PRICE_VIP', 149);      // $149/month

// Strategy Access per Package
define('STRATEGIES_FREE', ['8', '9']);           // 2 strategies
define('STRATEGIES_PRO', ['6', '7', '8', '9']);  // 4 strategies
define('STRATEGIES_ELITE', ['3', '4', '5', '6', '7', '8', '9']); // 7 strategies
define('STRATEGIES_VIP', ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10']); // All 10

// Markets (Real Account Only)
define('ALLOWED_MARKETS', ['EUR/USD', 'GBP/USD']);
define('REAL_ACCOUNT_ONLY', true); // Demo accounts not allowed

// Timeframes
define('ALLOWED_TIMEFRAMES', ['5M', '15M', '30M', '1H']);

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

// Multi-Timeframe Amount Settings (per timeframe)
define('TIMEFRAME_AMOUNTS', [
    '5M' => ['min' => 10000, 'max' => 100000, 'default' => 10000],
    '15M' => ['min' => 15000, 'max' => 150000, 'default' => 15000],
    '30M' => ['min' => 20000, 'max' => 200000, 'default' => 20000],
    '1H' => ['min' => 25000, 'max' => 250000, 'default' => 25000]
]);

// Weekend Auto-Off (Saturday & Sunday)
define('WEEKEND_AUTO_OFF', true);
define('WEEKEND_DAYS', [6, 0]); // 0 = Sunday, 6 = Saturday

// Signal Frequency Estimates (per hour per strategy)
define('SIGNAL_FREQUENCY', [
    '1' => ['min' => 3, 'max' => 8, 'avg' => 5],    // RSI Reversal
    '2' => ['min' => 2, 'max' => 6, 'avg' => 4],    // MACD Divergence
    '3' => ['min' => 4, 'max' => 10, 'avg' => 7],   // Bollinger Breakout
    '4' => ['min' => 3, 'max' => 7, 'avg' => 5],    // EMA Crossover
    '5' => ['min' => 2, 'max' => 5, 'avg' => 3],    // Support/Resistance
    '6' => ['min' => 5, 'max' => 12, 'avg' => 8],   // Stochastic Momentum
    '7' => ['min' => 4, 'max' => 9, 'avg' => 6],    // ADX Trend
    '8' => ['min' => 6, 'max' => 15, 'avg' => 10],  // Moving Average (FREE)
    '9' => ['min' => 5, 'max' => 13, 'avg' => 9],   // Candlestick Pattern (FREE)
    '10' => ['min' => 2, 'max' => 4, 'avg' => 3]    // Multi-Indicator Combo
]);

// Schedule Modes
define('SCHEDULE_MODES', [
    'auto_24h' => [
        'name' => 'Auto 24 Jam',
        'description' => 'Robot aktif 24 jam non-stop (kecuali weekend)',
        'icon' => 'fa-clock'
    ],
    'best_hours' => [
        'name' => 'Best Hours',
        'description' => 'Jam terbaik: 14:00-22:00 WIB (London & NY session)',
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

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Security
define('HASH_COST', 12); // bcrypt cost
define('SESSION_LIFETIME', 3600 * 24 * 7); // 7 days

// File upload limits
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// Watermark settings
define('WATERMARK_ENABLED', true);

// Anti-fraud settings (Phase 1 - Relaxed)
define('MULTI_ACCOUNT_CHECK', false);
define('DEVICE_LIMIT_CHECK', false);

// Pagination
define('ITEMS_PER_PAGE', 20);

// Database connection function
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // Log error in production, show message in development
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Redirect function
function redirect($url) {
    header("Location: $url");
    exit;
}

// Clean input
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Format currency
function formatCurrency($amount, $currency = 'IDR') {
    if ($currency === 'IDR') {
        return 'Rp' . number_format($amount, 0, ',', '.');
    }
    return '$' . number_format($amount, 2);
}

// Format date
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

// Calculate days remaining
function daysRemaining($expiry_date) {
    $now = new DateTime();
    $expiry = new DateTime($expiry_date);
    $diff = $now->diff($expiry);
    return $diff->invert ? 0 : $diff->days;
}
?>
